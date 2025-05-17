<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000|max:' . now()->year,
            'tab' => 'nullable|in:transactions,summary'
        ]);

        $month = $request->input('month');
        $year = $request->input('year');
        $tab = $request->input('tab', 'transactions');
        $query = Sale::select(
            'order_id',
            'product_name',
            'quantity',
            'unit_price',
            'total_price',
            'customer_name',
            'order_type',
            'status',
            'special_instructions',
            'created_at'
        );

        if ($month) {
            $query->whereMonth('created_at', $month);
        }
        if ($year) {
            $query->whereYear('created_at', $year);
        }

        $saleLogs = $query->orderBy('created_at', 'desc')->get();

        $summaryQuery = Sale::select(
            'product_name',
            DB::raw('SUM(quantity) as total_quantity_sold'),
            DB::raw('SUM(total_price) as total_revenue')
        );

        if ($month) {
            $summaryQuery->whereMonth('created_at', $month);
        }
        if ($year) {
            $summaryQuery->whereYear('created_at', $year);
        }

        $salesSummary = $summaryQuery->groupBy('product_name')
                                     ->orderBy('total_quantity_sold', 'desc')
                                     ->get();

        $totalRevenue = $saleLogs->sum('total_price');
        $totalQuantity = $saleLogs->sum('quantity');

        return view('manager.sales', compact('saleLogs', 'salesSummary', 'totalRevenue', 'totalQuantity', 'tab', 'month', 'year'));
    }
}