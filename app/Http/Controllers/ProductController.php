<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        return view('manager.inventory', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|unique:products,product_name|regex:/^[a-zA-Z\s]+$/', // Letters and spaces only
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1', // Positive integers only
            'supplier' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/', // Letters and spaces only
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'product_name' => $validated['product_name'],
                'category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'supplier' => $validated['supplier'],
            ]);

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store Product Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to add product: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|unique:products,product_name,' . $product->id . '|regex:/^[a-zA-Z\s]+$/', // Letters and spaces only
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1', // Positive integers only
            'supplier' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/', // Letters and spaces only
        ]);

        try {
            DB::beginTransaction();

            $product->update([
                'product_name' => $validated['product_name'],
                'category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'supplier' => $validated['supplier'],
            ]);

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Product Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Delete Product Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }
}