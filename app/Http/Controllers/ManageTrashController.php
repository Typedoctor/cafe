<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use Illuminate\Http\Request;

class ManageTrashController extends Controller
{
    public function index(Request $request)
    {
        $query = Trash::latest();

        // Search by product name
        if ($request->has('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by month
        if ($request->has('month') && $request->month !== 'all') {
            $query->whereMonth('created_at', $request->month);
        }

        // Filter by year
        if ($request->has('year') && $request->year !== 'all') {
            $query->whereYear('created_at', $request->year);
        }

        $trashes = $query->get();
        
        return view('cashier.manage_trash', compact('trashes'));
    }

    public function create()
    {
        return view('cashier.manage_trash'); // Using same view as index with modal
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|unique:trashes,product_name',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'total_loss' => 'required|numeric|min:0',
        ]);

        Trash::create($validated);
        
        return redirect()->route('trash.index')
            ->with('success', 'Trash entry added successfully!');
    }

    public function edit(Trash $trash)
    {
        return view('cashier.manage_trash', compact('trash'));
    }

    public function update(Request $request, Trash $trash)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|unique:trashes,product_name,'.$trash->id,
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'total_loss' => 'required|numeric|min:0',
        ]);

        $trash->update($validated);

        return redirect()->route('trash.index')
            ->with('success', 'Trash entry updated successfully!');
    }

    public function destroy(Trash $trash)
    {
        $trash->delete();
        return redirect()->route('trash.index');
    }
}