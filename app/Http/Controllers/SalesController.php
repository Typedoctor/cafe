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

        // Only default to current month/year if not present in the request at all
        $hasMonth = $request->has('month');
        $hasYear = $request->has('year');
        $month = $hasMonth ? $request->input('month') : now()->month;
        $year = $hasYear ? $request->input('year') : now()->year;
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

        // Only filter if month/year is not empty string or null
        if ($month !== null && $month !== '') {
            $query->whereMonth('created_at', $month);
        }
        if ($year !== null && $year !== '') {
            $query->whereYear('created_at', $year);
        }

        $saleLogs = $query->orderBy('created_at', 'desc')->get();

        $summaryQuery = Sale::select(
            'product_name',
            DB::raw('SUM(quantity) as total_quantity_sold'),
            DB::raw('SUM(total_price) as total_revenue')
        );

        if ($month !== null && $month !== '') {
            $summaryQuery->whereMonth('created_at', $month);
        }
        if ($year !== null && $year !== '') {
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