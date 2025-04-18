<?php

namespace App\Http\Controllers;
use App\Models\Trash;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'daily');
        $tab = $request->input('tab', 'profit'); // Default to 'profit' tab
        $query = Trash::query();

        if ($period === 'daily') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'monthly') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($period === 'yearly') {
            $query->whereYear('created_at', now()->year);
        }

        $trashes = $query->get();
        $totalLoss = $trashes->sum('total_loss');
        $trashCount = $trashes->sum('quantity');

        return view('manager.reports', compact('trashes', 'totalLoss', 'trashCount', 'period', 'tab'));
    }
}