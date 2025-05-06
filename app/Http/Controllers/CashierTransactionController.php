<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
//use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;

class CashierTransactionController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', 'all');
        $year = $request->input('year', 'all');
        $search = $request->input('search');

        $query = Transaction::select(
            'transaction_id', // Select the database-stored transaction_id
            'user_id',
            'customer_name',
            'order_type',
            'status',
            \DB::raw('GROUP_CONCAT(product_name) as product_names'),
            \DB::raw('SUM(quantity) as total_quantity'),
            \DB::raw('SUM(total_price) as total_price'),
            'special_instructions',
            'created_at',
            'updated_at'
        );

        // Apply month filter
        if ($month !== 'all') {
            $query->whereMonth('created_at', $month);
        }

        // Apply year filter
        if ($year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        if (!empty($search)) {
            $query->where('customer_name', 'like', '%' . $search . '%');
        }

        $summarizedTransactions = $query->groupBy(
            'transaction_id', // Group by the database-stored transaction_id
            'user_id',
            'customer_name',
            'order_type',
            'status',
            'special_instructions',
            'created_at',
            'updated_at'
        )->get();

        return view('cashier.cashier-transactions', compact('summarizedTransactions'));
    }

    public function export(Request $request)
    {
        return Excel::download(new TransactionsExport($request->all()), 'transactions_' . Carbon::now()->format('Ymd_His') . '.xlsx');
    }
}