<?php

namespace App\Http\Controllers;






use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Trash;

class DashboardController extends Controller 
{
    public function index() {
        $trashes = Trash::all();
        $totalLoss = $trashes->sum('total_loss');
        return view('manager.dashboard', compact('trashes', 'totalLoss'));
    }
}
