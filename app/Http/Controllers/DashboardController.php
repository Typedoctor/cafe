<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Trash;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller 
{
    public function index() {
        // For loss widget
        $trashes = Trash::all();
        $totalLoss = $trashes->sum('total_loss');

        // For best product widget
        $topSellingProducts = Transaction::select('product_id', \DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->with('product') // Eager-load the Product relationship
            ->get();    

        // For low on stock widget
        $lowStockProducts = Product::where('quantity', '<', 5)
            ->orderBy('quantity', 'asc')
            ->get();

        // Fetch sales data for the chart (sales volume over time)
        $startDate = Carbon::now()->startOfMonth(); // Start of current month
        $endDate = Carbon::now()->endOfMonth(); // End of current month

        // Define weeks (assuming 4 weeks in a month for simplicity)
        $weeks = [];
        $weekStart = $startDate->copy();
        for ($i = 1; $i <= 4; $i++) {
            $weekEnd = $weekStart->copy()->addDays(6); // Each week is 7 days
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

        // Fetch top 3 products by total sales to display in the chart
        $topProducts = Transaction::select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(3) // Limit to top 3 products for the chart
            ->with('product')
            ->get();

        // Prepare chart data
        $salesData = [
            'labels' => array_column($weeks, 'label'), // ['Week 1', 'Week 2', 'Week 3', 'Week 4']
            'datasets' => []
        ];

        // Colors for each product (consistent with your current chart)
        $colors = [
            ['border' => '#10394f', 'background' => 'rgba(16, 57, 79, 0.2)'],
            ['border' => '#0d2c3a', 'background' => 'rgba(13, 44, 58, 0.2)'],
            ['border' => '#007bff', 'background' => 'rgba(0, 123, 255, 0.2)']
        ];

        // Fetch sales data for each product per week
        $maxY = 0;
        foreach ($topProducts as $index => $product) {
            $productSales = [];
            foreach ($weeks as $week) {
                $quantity = Transaction::where('product_id', $product->product_id)
                    ->whereBetween('created_at', [$week['start'], $week['end']])
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

        return view('manager.dashboard', compact('trashes', 'topSellingProducts', 'totalLoss', 'lowStockProducts', 'salesData', 'maxY'));
    }

    public function dashboard() {
        // Empty for now, or you can redirect to index if not needed
    }
}