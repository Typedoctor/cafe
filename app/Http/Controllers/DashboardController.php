<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Spoilage;
use App\Models\Sale;
use App\Models\DamagedProduct;
use Carbon\Carbon;

class DashboardController extends Controller 
{
    public function index(Request $request) {
        // Clean up session if 'all' is present
        if ($request->session()->get('filter_month') === 'all' || $request->session()->get('filter_year') === 'all') {
            $request->session()->forget(['filter_period', 'filter_month', 'filter_year']);
        }

        // Handle reset
        if ($request->has('reset') && $request->input('reset') === 'true') {
            $request->session()->forget(['filter_period', 'filter_month', 'filter_year']);
        }

        // Validate and set filter inputs
        $period = in_array($request->input('period'), ['monthly', 'yearly'])
            ? $request->input('period')
            : $request->session()->get('filter_period', 'monthly');

        $month = is_numeric($request->input('month')) && $request->input('month') >= 1 && $request->input('month') <= 12
            ? (int) $request->input('month')
            : $request->session()->get('filter_month', now()->month);

        $year = is_numeric($request->input('year')) && $request->input('year') >= now()->year - 5 && $request->input('year') <= now()->year
            ? (int) $request->input('year')
            : $request->session()->get('filter_year', now()->year);

        // Store filters in session
        $request->session()->put('filter_period', $period);
        $request->session()->put('filter_month', $month);
        $request->session()->put('filter_year', $year);

        // Initialize queries
        $queryTrash = Spoilage::query();
        $queryTransaction = Sale::query();
        $queryTopSelling = Sale::query();
        $querySalesChart = Sale::query();
        $queryDamaged = DamagedProduct::query();

        // Apply filters
        if ($period === 'monthly') {
            $queryTrash->whereMonth('created_at', $month)->whereYear('created_at', $year);
            $queryTransaction->whereMonth('created_at', $month)->whereYear('created_at', $year);
            $queryTopSelling->whereMonth('created_at', $month)->whereYear('created_at', $year);
            $querySalesChart->whereMonth('created_at', $month)->whereYear('created_at', $year);
            $queryDamaged->whereMonth('reported_at', $month)->whereYear('reported_at', $year);
        } else { // yearly
            $queryTrash->whereYear('created_at', $year);
            $queryTransaction->whereYear('created_at', $year);
            $queryTopSelling->whereYear('created_at', $year);
            $querySalesChart->whereYear('created_at', $year);
            $queryDamaged->whereYear('reported_at', $year);
        }

        // Fetch data
        $trashes = $queryTrash->get();
        $transactions = $queryTransaction->get();
        $damagedProducts = $queryDamaged->get();
        $totalLossFromSpoilage = $trashes->sum('total_loss');
        $totalLossFromDamaged = $damagedProducts->where('status', DamagedProduct::STATUS_LOSS)->sum('total_cost');
        $totalLoss = $totalLossFromSpoilage + $totalLossFromDamaged;
        $totalSaved = $damagedProducts->where('status', DamagedProduct::STATUS_RETURNED)->sum('total_cost');
        $revenue = $transactions->sum('total_price');
        $damagedCount = $damagedProducts->count();

        // Top selling products
        $topSellingProducts = $queryTopSelling
            ->select('product_name', \DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->with('product')
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('quantity', '<', 5)
            ->orderBy('quantity', 'asc')
            ->get();

        // Chart data
        try {
            if ($period === 'yearly') {
                $startDate = Carbon::create($year, 1, 1)->startOfDay();
                $endDate = Carbon::create($year, 12, 31)->endOfDay();
                $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
                $intervals = [
                    ['start' => $startDate->copy(), 'end' => $startDate->copy()->endOfQuarter(), 'label' => 'Q1'],
                    ['start' => $startDate->copy()->addMonths(3), 'end' => $startDate->copy()->addMonths(5)->endOfMonth(), 'label' => 'Q2'],
                    ['start' => $startDate->copy()->addMonths(6), 'end' => $startDate->copy()->addMonths(8)->endOfMonth(), 'label' => 'Q3'],
                    ['start' => $startDate->copy()->addMonths(9), 'end' => $endDate->copy(), 'label' => 'Q4'],
                ];
            } else { // monthly
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
                $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                $weeks = [];
                $weekStart = $startDate->copy();
                for ($i = 1; $i <= 4; $i++) {
                    $weekEnd = $weekStart->copy()->addDays(6);
                    if ($weekEnd->gt($endDate)) {
                        $weekEnd = $endDate->copy();
                    }
                    $weeks[] = [
                        'start' => $weekStart->copy(),
                        'end' => $weekEnd->copy(),
                        'label' => "Week $i"
                    ];
                    $weekStart = $weekEnd->copy()->addDay();
                }
                $intervals = $weeks;
            }
        } catch (\Exception $e) {
            // Fallback to current month/year if Carbon fails
            \Log::error('Carbon error', ['month' => $month, 'year' => $year, 'error' => $e->getMessage()]);
            $month = now()->month;
            $year = now()->year;
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            $weeks = [];
            $weekStart = $startDate->copy();
            for ($i = 1; $i <= 4; $i++) {
                $weekEnd = $weekStart->copy()->addDays(6);
                if ($weekEnd->gt($endDate)) {
                    $weekEnd = $endDate->copy();
                }
                $weeks[] = [
                    'start' => $weekStart->copy(),
                    'end' => $weekEnd->copy(),
                    'label' => "Week $i"
                ];
                $weekStart = $weekEnd->copy()->addDay();
            }
            $intervals = $weeks;
        }

        // Top 3 products for chart
        $topProducts = $querySalesChart
            ->select('product_id', \DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(3)
            ->with('product')
            ->get();

        // Prepare chart data
        $salesData = ['labels' => $labels, 'datasets' => []];
        $colors = [
            ['border' => '#10394f', 'background' => 'rgba(16, 57, 79, 0.2)'],
            ['border' => 'blue', 'background' => 'rgba(0, 174, 255, 0.2)'],
            ['border' => 'green', 'background' => 'rgba(65, 196, 21, 0.2)']
        ];
        $maxY = 0;

        foreach ($topProducts as $index => $product) {
            $productSales = [];
            foreach ($intervals as $interval) {
                $quantity = Sale::where('product_id', $product->product_id)
                    ->whereBetween('created_at', [$interval['start'], $interval['end']])
                    ->sum('quantity');
                $productSales[] = $quantity ?: 0;
            }
            $maxY = max($maxY, max($productSales));
            $salesData['datasets'][] = [
                'label' => $product->product->product_name ?? 'Unknown Product',
                'data' => $productSales,
                'borderColor' => $colors[$index % count($colors)]['border'],
                'backgroundColor' => $colors[$index % count($colors)]['background'],
                'fill' => true,
                'tension' => 0.3
            ];
        }

        $maxY = $maxY > 0 ? ceil($maxY / 100) * 100 : 100;

        return view('manager.dashboard', compact(
            'trashes',
            'revenue',
            'topSellingProducts',
            'totalLoss',
            'totalLossFromSpoilage',
            'totalLossFromDamaged',
            'totalSaved',
            'lowStockProducts',
            'salesData',
            'maxY',
            'period',
            'month',
            'year',
            'damagedProducts',
            'damagedCount'
        ));
    }
}