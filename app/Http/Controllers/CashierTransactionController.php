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
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $month = $request->input('month', $currentMonth);
        $year = $request->input('year', $currentYear);

        $query = Transaction::select(
            'transaction_id',
            'user_id',
            'customer_name',
            'order_type',
            'status',
            \DB::raw('GROUP_CONCAT(product_name) as product_names'),
            \DB::raw('GROUP_CONCAT(quantity) as product_quantities'), // Add this line
            \DB::raw('SUM(quantity) as total_quantity'),
            \DB::raw('SUM(total_price) as total_price'),
            'money_received',
            'change',
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

        $summarizedTransactions = $query->groupBy(
            'transaction_id',
            'user_id',
            'customer_name',
            'order_type',
            'status',
            'special_instructions',
            'money_received',
            'change',
            'created_at',
            'updated_at'
        )->get();

        // Decode product_name JSON for each transaction
        foreach ($summarizedTransactions as $transaction) {
            $productSummary = [];
            if (!empty($transaction->product_names)) {
                // If product_names is JSON, decode it
                $decoded = json_decode($transaction->product_names, true);
                if (is_array($decoded)) {
                    $productSummary = $decoded;
                } else {
                    // fallback: old format (comma-separated)
                    $names = explode(',', $transaction->product_names);
                    $quantities = explode(',', $transaction->product_quantities ?? '');
                    foreach ($names as $idx => $name) {
                        $productSummary[trim($name)] = isset($quantities[$idx]) ? (int) $quantities[$idx] : 1;
                    }
                }
            }
            $transaction->product_summary = $productSummary;
        }

        return view('cashier.cashier-transactions', compact('summarizedTransactions'));
    }

    public function export(Request $request)
    {
        return Excel::download(new TransactionsExport($request->all()), 'transactions_' . Carbon::now()->format('Ymd_His') . '.xlsx');
    }
}