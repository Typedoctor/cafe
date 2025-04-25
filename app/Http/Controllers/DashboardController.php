<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Trash;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller 
{
    public function index(Request $request) {
        // Check for reset parameter
        if ($request->has('reset') && $request->input('reset') === 'true') {
            // Clear session values
            $request->session()->forget(['filter_period', 'filter_month', 'filter_year']);
            // Set default values
            $period = 'daily';
            $month = 'all';
            $year = 'all';
        } else {
            // Get filter inputs from request, falling back to session values
            $period = $request->input('period', $request->session()->get('filter_period', 'daily'));
            $month = $request->input('month', $request->session()->get('filter_month', 'all'));
            $year = $request->input('year', $request->session()->get('filter_year', 'all'));
        }

        // Store the filter values in the session
        $request->session()->put('filter_period', $period);
        $request->session()->put('filter_month', $month);
        $request->session()->put('filter_year', $year);
        
        // Initialize queries for Trash and Transaction
        $queryTrash = Trash::query();
        $queryTransaction = Transaction::query();
        // Clone the query for topSellingProducts and salesData to apply filters
        $queryTopSelling = Transaction::query();
        $querySalesChart = Transaction::query();

        // Apply period-based filtering (same as ReportController)
        if ($period === 'daily') {
            // For daily, always filter by today's date
            $today = Carbon::today();
            // Only include data if the selected month/year matches today
            if (($month !== 'all' && $month != $today->month) || ($year !== 'all' && $year != $today->year)) {
                $trashes = collect([]); // Empty collection for trashes
                $transactions = collect([]); // Empty collection for transactions
                $topSellingProducts = collect([]); // Empty for top-selling
                $salesTransactions = collect([]); // Empty for sales chart
            } else {
                $queryTrash->whereDate('created_at', $today);
                $queryTransaction->whereDate('created_at', $today);
                $queryTopSelling->whereDate('created_at', $today);
                $querySalesChart->whereDate('created_at', $today);
                $trashes = $queryTrash->get();
                $transactions = $queryTransaction->get();
            }
        } elseif ($period === 'monthly') {
            // Apply month and year filters, default to current month/year if not specified
            if ($month !== 'all') {
                $queryTrash->whereMonth('created_at', $month);
                $queryTransaction->whereMonth('created_at', $month);
                $queryTopSelling->whereMonth('created_at', $month);
                $querySalesChart->whereMonth('created_at', $month);
            } else {
                $queryTrash->whereMonth('created_at', now()->month);
                $queryTransaction->whereMonth('created_at', now()->month);
                $queryTopSelling->whereMonth('created_at', now()->month);
                $querySalesChart->whereMonth('created_at', now()->month);
            }
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryTransaction->whereYear('created_at', $year);
                $queryTopSelling->whereYear('created_at', $year);
                $querySalesChart->whereYear('created_at', $year);
            } else {
                $queryTrash->whereYear('created_at', now()->year);
                $queryTransaction->whereYear('created_at', now()->year);
                $queryTopSelling->whereYear('created_at', now()->year);
                $querySalesChart->whereYear('created_at', now()->year);
            }
            $trashes = $queryTrash->get();
            $transactions = $queryTransaction->get();
        } elseif ($period === 'yearly') {
            // Apply year filter, default to current year if not specified
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryTransaction->whereYear('created_at', $year);
                $queryTopSelling->whereYear('created_at', $year);
                $querySalesChart->whereYear('created_at', $year);
            } else {
                $queryTrash->whereYear('created_at', now()->year);
                $queryTransaction->whereYear('created_at', now()->year);
                $queryTopSelling->whereYear('created_at', now()->year);
                $querySalesChart->whereYear('created_at', now()->year);
            }
            $trashes = $queryTrash->get();
            $transactions = $queryTransaction->get();
        }

        // Calculate total loss and revenue from filtered data
        $totalLoss = $trashes->sum('total_loss');
        $revenue = $transactions->sum('total_price');

        // For best product widget (apply filters)
        $topSellingProducts = $queryTopSelling->select('product_id', \DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->with('product') // Eager-load the Product relationship
            ->get();    

        // For low on stock widget (unchanged, as stock isn't time-based)
        $lowStockProducts = Product::where('quantity', '<', 5)
            ->orderBy('quantity', 'asc')
            ->get();

        // Fetch sales data for the chart (sales volume over time)
        // Adjust the date range based on the period
        if ($period === 'daily') {
            $startDate = Carbon::today();
            $endDate = Carbon::today();
            $labels = ['Today'];
            $intervals = [['start' => $startDate, 'end' => $endDate, 'label' => 'Today']];
        } elseif ($period === 'yearly') {
            $selectedYear = $year !== 'all' ? $year : now()->year;
            $startDate = Carbon::create($selectedYear, 1, 1)->startOfDay();
            $endDate = Carbon::create($selectedYear, 12, 31)->endOfDay();
            $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
            $intervals = [
                ['start' => $startDate->copy(), 'end' => $startDate->copy()->endOfQuarter(), 'label' => 'Q1'],
                ['start' => $startDate->copy()->addMonths(3), 'end' => $startDate->copy()->addMonths(5)->endOfMonth(), 'label' => 'Q2'],
                ['start' => $startDate->copy()->addMonths(6), 'end' => $startDate->copy()->addMonths(8)->endOfMonth(), 'label' => 'Q3'],
                ['start' => $startDate->copy()->addMonths(9), 'end' => $endDate->copy(), 'label' => 'Q4'],
            ];
        } else { // Monthly
            $selectedMonth = $month !== 'all' ? $month : now()->month;
            $selectedYear = $year !== 'all' ? $year : now()->year;
            $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
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

        // Fetch top 3 products by total sales for the chart (apply filters)
        $topProducts = $querySalesChart->select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(3)
            ->with('product')
            ->get();

        // Prepare chart data
        $salesData = [
            'labels' => $labels, // Dynamic labels based on period
            'datasets' => []
        ];

        // Colors for each product
        $colors = [
            ['border' => '#10394f', 'background' => 'rgba(16, 57, 79, 0.2)'],
            ['border' => 'blue', 'background' => 'rgba(0, 174, 255, 0.2)'],
            ['border' => 'green', 'background' => 'rgba(65, 196, 21, 0.2)']
        ];

        // Fetch sales data for each product per interval
        $maxY = 0;
        foreach ($topProducts as $index => $product) {
            $productSales = [];
            foreach ($intervals as $interval) {
                $quantity = Transaction::where('product_id', $product->product_id)
                    ->whereBetween('created_at', [$interval['start'], $interval['end']])
                    ->sum('quantity');
                $productSales[] = $quantity ?: 0; // Default to 0 if no sales
            }

            // Update maxY for dynamic Y-axis scaling
            $maxInDataset = max($productSales);
            $maxY = max($maxY, $maxInDataset);

            $salesData['datasets'][] = [
                'label' => $product->product->product_name ?? 'Unknown Product',
                'data' => $productSales,
                'borderColor' => $colors[$index % count($colors)]['border'],
                'backgroundColor' => $colors[$index % count($colors)]['background'],
                'fill' => true,
                'tension' => 0.3
            ];
        }

        // Round up maxY to the nearest 100 for better scaling
        $maxY = $maxY > 0 ? ceil($maxY / 100) * 100 : 100; // Default to 100 if no sales

        return view('manager.dashboard', compact('trashes', 'revenue', 'topSellingProducts', 'totalLoss', 'lowStockProducts', 'salesData', 'maxY', 'period', 'month', 'year'));
    }

    public function dashboard() {
        // Empty for now, or you can redirect to index if not needed
    }
}