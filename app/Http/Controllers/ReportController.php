<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use Illuminate\Http\Request;
use App\Models\DamagedProduct;
use Carbon\Carbon;
use App\Models\Transaction;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'daily');
        $tab = $request->input('tab', 'profit');
        $subtab = $request->input('subtab', 'thrown');
        $month = $request->input('month', 'all');
        $year = $request->input('year', 'all');
        $queryTrash = Trash::query();
        $queryTransaction = Transaction::query();
        $queryDamaged = DamagedProduct::query()->where('status', 'Marked as Loss');
        $Transactquery = Transaction::select(
            'transaction_id',
            'user_id',
            'customer_name',
            'order_type',
            'status',
            \DB::raw('GROUP_CONCAT(product_name) as product_names'),
            \DB::raw('SUM(quantity) as total_quantity'),
            \DB::raw('SUM(total_price) as total_price'),
            'money_received',
            'change',
            'special_instructions',
            'created_at',
            'updated_at'
        );

        if ($period === 'daily') {
            $today = Carbon::today();
            if (($month !== 'all' && $month != $today->month) || ($year !== 'all' && $year != $today->year)) {
                $trashes = collect([]);
                $transactions = collect([]);
                $damagedProducts = collect([]);
            } else {
                $queryTrash->whereDate('created_at', $today);
                $queryTransaction->whereDate('created_at', $today);
                $queryDamaged->whereDate('reported_at', $today);
                $trashes = $queryTrash->get();
                $transactions = $queryTransaction->get();
                $damagedProducts = $queryDamaged->get();
            }
        } elseif ($period === 'monthly') {
            if ($month !== 'all') {
                $queryTrash->whereMonth('created_at', $month);
                $queryTransaction->whereMonth('created_at', $month);
                $queryDamaged->whereMonth('reported_at', $month);
            } else {
                $queryTrash->whereMonth('created_at', now()->month);
                $queryTransaction->whereMonth('created_at', now()->month);
                $queryDamaged->whereMonth('reported_at', now()->month);
            }
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryTransaction->whereYear('created_at', $year);
                $queryDamaged->whereYear('reported_at', $year);
            } else {
                $queryTrash->whereYear('created_at', now()->year);
                $queryTransaction->whereYear('created_at', now()->year);
                $queryDamaged->whereYear('reported_at', now()->year);
            }
            $trashes = $queryTrash->get();
            $transactions = $queryTransaction->get();
            $damagedProducts = $queryDamaged->get();
        } elseif ($period === 'yearly') {
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryTransaction->whereYear('created_at', $year);
                $queryDamaged->whereYear('reported_at', $year);
            } else {
                $queryTrash->whereYear('created_at', now()->year);
                $queryTransaction->whereYear('created_at', now()->year);
                $queryDamaged->whereYear('reported_at', now()->year);
            }
            $trashes = $queryTrash->get();
            $transactions = $queryTransaction->get();
            $damagedProducts = $queryDamaged->get();
        }

        $trashLoss = $trashes->sum('total_loss');
        $damagedLoss = $damagedProducts->sum('total_cost');
        $totalLoss = $trashLoss + $damagedLoss;
        $trashCount = $trashes->sum('quantity');
        $damagedCount = $damagedProducts->sum('quantity');
        $revenue = $transactions->sum('total_price');

        $summarizedTransactions = $Transactquery->groupBy(
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

        return view('manager.reports', compact('revenue', 'trashes', 'totalLoss', 'trashCount', 'damagedCount', 'period', 'tab', 'subtab', 'month', 'year', 'transactions', 'damagedProducts', 'summarizedTransactions'));
    }
}