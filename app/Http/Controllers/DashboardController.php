<?php

namespace App\Http\Controllers;






use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Trash;
use App\Models\Transaction;

class DashboardController extends Controller 
{
    public function index() {
        //for loss widget
        $trashes = Trash::all();
        $totalLoss = $trashes->sum('total_loss');

        //for best product widget
        $topSellingProducts = Transaction::select('product_id', \DB::raw('SUM(quantity) as total_quantity'))
        ->groupBy('product_id')
        ->orderByDesc('total_quantity')
        ->take(5)
        ->with('product') // Eager-load the Product relationship
        ->get();    

        //for low on stock widget
        $lowStockProducts = Product::where('quantity', '<', 5)
        ->orderBy('quantity', 'asc')
        ->get();

        return view('manager.dashboard', compact('trashes', 'topSellingProducts','totalLoss','lowStockProducts'));
    }

    public function dashboard(){

    }
}
