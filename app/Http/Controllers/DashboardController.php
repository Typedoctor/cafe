<?php

namespace App\Http\Controllers;






use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Trash;

class DashboardController extends Controller 
{
    public function index() {
        //for loss widget
        $trashes = Trash::all();
        $totalLoss = $trashes->sum('total_loss');

        //for best product widget
        $topSellingProducts = Product::orderBy('sales','desc')->take(5)
        ->get();

        //for low on stock widget
        $lowStockProducts = Product::where('quantity', '<', 5)
        ->orderBy('quantity', 'asc')
        ->get();

        return view('manager.dashboard', compact('trashes', 'totalLoss','topSellingProducts','lowStockProducts'));
    }

    public function dashboard(){

    }
}
