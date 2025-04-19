<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $products = Product::orderBy('product_name')->get();
        
        return view('cashier.manage_trash', compact('trashes', 'products'));
    }

    public function create()
    {
        $products = Product::orderBy('product_name')->get();
        return view('cashier.manage_trash', compact('products'));
    }

    public function store(Request $request)
    {
        \Log::info('Store request data: ' . json_encode($request->all()));
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|exists:products,product_name',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $product = Product::where('product_name', $validated['product_name'])->firstOrFail();
                if ($product->quantity < $validated['quantity']) {
                    throw new \Exception('Not enough product quantity available.');
                }

                // Calculate total_loss
                $validated['total_loss'] = $product->price * $validated['quantity'];

                Trash::create($validated);
                $product->decrement('quantity', $validated['quantity']);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }
        
        return redirect()->route('trash.index')
            ->with('success', 'Trash entry added successfully!');
    }

    public function edit(Trash $trash)
    {
        $products = Product::orderBy('product_name')->get();
        return view('cashier.manage_trash', compact('trash', 'products'));
    }

    public function update(Request $request, Trash $trash)
    {
        \Log::info('Update request data: ' . json_encode($request->all()));
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|exists:products,product_name',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $trash) {
                $product = Product::where('product_name', $validated['product_name'])->firstOrFail();
                $quantityDifference = $validated['quantity'] - $trash->quantity;

                if ($quantityDifference > 0 && $product->quantity < $quantityDifference) {
                    throw new \Exception('Not enough product quantity available.');
                }

                // Calculate total_loss
                $validated['total_loss'] = $product->price * $validated['quantity'];

                // If product_name changed, restore quantity to the old product
                if ($trash->product_name !== $validated['product_name']) {
                    $oldProduct = Product::where('product_name', $trash->product_name)->first();
                    if ($oldProduct) {
                        $oldProduct->increment('quantity', $trash->quantity);
                    }
                }

                $trash->update($validated);
                if ($quantityDifference != 0) {
                    $product->decrement('quantity', $quantityDifference);
                }
            });
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('trash.index')
            ->with('success', 'Trash entry updated successfully!');
    }

    public function destroy(Trash $trash)
    {
        try {
            DB::transaction(function () use ($trash) {
                $product = Product::where('product_name', $trash->product_name)->first();
                if ($product) {
                    $product->increment('quantity', $trash->quantity);
                }
                $trash->delete();
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete trash entry.']);
        }

        return redirect()->route('trash.index')
            ->with('success', 'Trash entry deleted successfully!');
    }
}