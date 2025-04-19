<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'daily');
        $tab = $request->input('tab', 'profit');
        $month = $request->input('month', 'all');
        $year = $request->input('year', 'all');
        $query = Trash::query();

        // Apply period-based filtering
        if ($period === 'daily') {
            // For daily, always filter by today's date
            $today = Carbon::today();
            // Only include data if the selected month/year matches today
            if (($month !== 'all' && $month != $today->month) || ($year !== 'all' && $year != $today->year)) {
                $trashes = collect([]); // Return empty collection if month/year doesn't match today
            } else {
                $query->whereDate('created_at', $today);
                $trashes = $query->get();
            }
        } elseif ($period === 'monthly') {
            // Apply month and year filters, default to current month/year if not specified
            if ($month !== 'all') {
                $query->whereMonth('created_at', $month);
            } else {
                $query->whereMonth('created_at', now()->month);
            }
            if ($year !== 'all') {
                $query->whereYear('created_at', $year);
            } else {
                $query->whereYear('created_at', now()->year);
            }
            $trashes = $query->get();
        } elseif ($period === 'yearly') {
            // Apply year filter, default to current year if not specified
            if ($year !== 'all') {
                $query->whereYear('created_at', $year);
            } else {
                $query->whereYear('created_at', now()->year);
            }
            $trashes = $query->get();
        }

        $totalLoss = $trashes->sum('total_loss');
        $trashCount = $trashes->sum('quantity');

        return view('manager.reports', compact('trashes', 'totalLoss', 'trashCount', 'period', 'tab', 'month', 'year'));
    }
}