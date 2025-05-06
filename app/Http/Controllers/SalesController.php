<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', 'all');
        $year = $request->input('year', 'all');
        $search = $request->input('search');

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

        if ($month !== 'all') {
            $query->whereMonth('created_at', $month);
        }

        if ($year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        if (!empty($search)) {
            $query->where('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('product_name', 'like', '%' . $search . '%');
        }

        $saleLogs = $query->orderBy('created_at', 'desc')->get();

        // Calculate sales summary
        $salesSummary = Sale::select(
            'product_name',
            \DB::raw('SUM(quantity) as total_quantity_sold'),
            \DB::raw('SUM(total_price) as total_revenue')
        )
            ->groupBy('product_name')
            ->orderBy('total_quantity_sold', 'desc')
            ->get();

        return view('manager.sales', compact('saleLogs', 'salesSummary'));
    }
}