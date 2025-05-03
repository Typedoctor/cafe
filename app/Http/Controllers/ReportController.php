<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Transaction;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'daily');
        $tab = $request->input('tab', 'profit');
        $month = $request->input('month', 'all');
        $year = $request->input('year', 'all');
        $queryTrash = Trash::query();
        $queryTransaction = Transaction::query();

        // Apply period-based filtering
        if ($period === 'daily') {
            // For daily, always filter by today's date
            $today = Carbon::today();
            // Only include data if the selected month/year matches today
            if (($month !== 'all' && $month != $today->month) || ($year !== 'all' && $year != $today->year)) {
                $trashes = collect([]); // Return empty collection if month/year doesn't match today
                $transactions = collect([]); 
            } else {
                $queryTrash->whereDate('created_at', $today);
                $trashes = $queryTrash->get();
                $queryTransaction->whereDate('created_at', $today);
                $transactions = $queryTransaction->get();
            }
        } elseif ($period === 'monthly') {
            // Apply month and year filters, default to current month/year if not specified
            if ($month !== 'all') {
                $queryTrash->whereMonth('created_at', $month);
                $queryTransaction->whereMonth('created_at', $month);
            } else {
                $queryTrash->whereMonth('created_at', now()->month);
                $queryTransaction->whereMonth('created_at', now()->month);
            }
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryTransaction->whereYear('created_at', $year);
            } else {
                $queryTrash->whereYear('created_at', now()->year);
                $queryTransaction->whereYear('created_at', now()->year);
            }
            $trashes = $queryTrash->get();
            $transactions = $queryTransaction->get();
        } elseif ($period === 'yearly') {
            // Apply year filter, default to current year if not specified
            if ($year !== 'all') {
                $queryTrash->whereYear('created_at', $year);
                $queryTransaction->whereYear('created_at', $year);
            } else {
                $queryTrash->whereYear('created_at', now()->year);
                $queryTransaction->whereYear('created_at', now()->year);
            }
            $trashes = $queryTrash->get();
            $transactions = $queryTransaction->get(); 
        }

        $totalLoss = $trashes->sum('total_loss');
        $trashCount = $trashes->sum('quantity');
        $revenue = $transactions->sum('total_price');

        return view('manager.reports', compact('revenue', 'trashes', 'totalLoss', 'trashCount', 'period', 'tab', 'month', 'year', 'transactions'));
    }
}