<?php

namespace App\Http\Controllers;
use App\Models\Trash;
use Illuminate\Http\Request;

class ManageTrashController extends Controller
{
    public function index()
    {
        $trash = Trash::all();
        dd($trash);
        return view('cashier.manage_trash');
    }

    public function create()
    {
        return view('cashier.create_trash');
    }

    public function store()
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer',
            'reason' => 'required|string|max:255',
            'price' => 'required|numeric',
            
        ]);

        Trash::create($validated);
    }
}
