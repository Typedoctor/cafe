<?php

namespace App\Http\Controllers;

use App\Models\Spoilage;
use App\Models\Sale;
use App\Models\DamagedProduct;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'period' => 'nullable|in:daily,monthly,yearly',
            'tab' => 'nullable|in:profit,loss',
            'subtab' => 'nullable|in:all-transactions,sales-log,summary,thrown,damaged',
            'month' => 'nullable|in:all,1,2,3,4,5,6,7,8,9,10,11,12',
            'year' => 'nullable|in:all,' . implode(',', range(2000, now()->year)),
        ]);

        $period = $request->input('period', 'daily');
        $tab = $request->input('tab', 'profit');
        $subtab = $request->input('subtab', 'all-transactions');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Query for trashes (Spoilage)
        $queryTrash = Spoilage::query();
        // Query for damaged products
        $queryDamaged = DamagedProduct::query()->where('status', 'Marked as Loss');
        // Query for summarized transactions
        $queryTransactions = Transaction::select(
            'transaction_id',
            'user_id',
            'customer_name',
            'order_type',
            'status',
            DB::raw('GROUP_CONCAT(product_name) as product_names'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total_price) as total_price'),
            'money_received',
            'change',
            'special_instructions',
            'created_at',
            'updated_at'
        );
        // Query for sales logs
        $querySales = Sale::select(
            'order_id',
            'product_name',
            'quantity',
            // Use selling price (purchase cost + profit) for unit_price and total_price
            'unit_price',
            'total_price',
            'customer_name',
            'order_type',
            'status',
            'special_instructions',
            'created_at'
        );
        // Query for sales summary
        $summaryQuery = Sale::select(
            'product_name',
            DB::raw('SUM(quantity) as total_quantity_sold'),
            // Use SUM(total_price) for total revenue (purchase cost + profit)
            DB::raw('SUM(total_price) as total_revenue')
        );

        if ($period === 'daily') {
            $today = Carbon::today();
            if (($month != $today->month && $month !== 'all') || ($year != $today->year && $year !== 'all')) {
                $trashes = collect([]);
                $damagedProducts = collect([]);
                $summarizedTransactions = collect([]);
                $saleLogs = collect([]);
                $salesSummary = collect([]);
            } else {
                $queryTrash->whereDate('created_at', $today);
                $queryDamaged->whereDate('reported_at', $today);
                $queryTransactions->whereDate('created_at', $today);
                $querySales->whereDate('created_at', $today);
                $summaryQuery->whereDate('created_at', $today);
                $trashes = $queryTrash->get();
                $damagedProducts = $queryDamaged->get();
                $summarizedTransactions = $queryTransactions->groupBy(
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
                $saleLogs = $querySales->orderBy('created_at', 'desc')->get();
                $salesSummary = $summaryQuery->groupBy('product_name')
                                             ->orderBy('total_quantity_sold', 'desc')
                                             ->get();
            }
        } elseif ($period === 'monthly') {
            if ($month !== 'all') {
                $queryTrash->whereMonth('created_at', $month);
                $queryDamaged->whereMonth('reported_at', $month);
                $queryTransactions->whereMonth('created_at', $month);
                $querySales->whereMonth('created_at', $month);
                $summaryQuery->whereMonth('created_at', $month);
            }
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryDamaged->whereYear('reported_at', $year);
                $queryTransactions->whereYear('created_at', $year);
                $querySales->whereYear('created_at', $year);
                $summaryQuery->whereYear('created_at', $year);
            }
            $trashes = $queryTrash->get();
            $damagedProducts = $queryDamaged->get();
            $summarizedTransactions = $queryTransactions->groupBy(
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
            $saleLogs = $querySales->orderBy('created_at', 'desc')->get();
            $salesSummary = $summaryQuery->groupBy('product_name')
                                         ->orderBy('total_quantity_sold', 'desc')
                                         ->get();
        } elseif ($period === 'yearly') {
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryDamaged->whereYear('reported_at', $year);
                $queryTransactions->whereYear('created_at', $year);
                $querySales->whereYear('created_at', $year);
                $summaryQuery->whereYear('created_at', $year);
            }
            $trashes = $queryTrash->get();
            $damagedProducts = $queryDamaged->get();
            $summarizedTransactions = $queryTransactions->groupBy(
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
            $saleLogs = $querySales->orderBy('created_at', 'desc')->get();
            $salesSummary = $summaryQuery->groupBy('product_name')
                                         ->orderBy('total_quantity_sold', 'desc')
                                         ->get();
        }

        $trashLoss = $trashes->sum('total_loss');
        $damagedLoss = $damagedProducts->sum('total_cost');
        $totalLoss = $trashLoss + $damagedLoss;
        $trashCount = $trashes->sum('quantity');
        $damagedCount = $damagedProducts->sum('quantity');
        $totalRevenue = $saleLogs->sum('total_price'); // Sum of selling prices (purchase cost + profit)
        $totalQuantity = $saleLogs->sum('quantity');

        return view('manager.reports', compact(
            'totalRevenue',
            'totalQuantity',
            'trashes',
            'totalLoss',
            'trashCount',
            'damagedCount',
            'period',
            'tab',
            'subtab',
            'month',
            'year',
            'summarizedTransactions',
            'saleLogs',
            'salesSummary',
            'damagedProducts',
            'trashLoss',
            'damagedLoss'
        ));
    }
}