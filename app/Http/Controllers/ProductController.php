<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'product_name' => 'required|string|max:50|unique:products,product_name',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1|max:1200',
            'supplier' => 'required|string|max:50',
            'unit_of_measurement' => 'required|in:pieces,liters,kilograms,grams',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'product_name' => $validated['product_name'],
                'category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'supplier' => $validated['supplier'],
                'unit_of_measurement' => $validated['unit_of_measurement'],
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['product' => $product], 201);
            }

            return redirect()->route('products.index')->with('success', 'Product added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store Product Error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to add product', 'errors' => ['general' => $e->getMessage()]], 500);
            }
            return back()->withErrors(['error' => 'Failed to add product: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'product_name')->ignore($product->id),
            ],
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1|max:1200',
            'supplier' => 'required|string|max:50',
            'unit_of_measurement' => 'required|in:pieces,liters,kilograms,grams',
        ]);

        try {
            DB::beginTransaction();

            $product->update([
                'product_name' => $validated['product_name'],
                'category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'supplier' => $validated['supplier'],
                'unit_of_measurement' => $validated['unit_of_measurement'],
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['product' => $product], 200);
            }

            return redirect()->route('products.index')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Product Error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to update product', 'errors' => ['general' => $e->getMessage()]], 500);
            }
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Request $request, Product $product)
    {
        try {
            $product->delete();
            if ($request->ajax()) {
                return response()->json(['message' => 'Product deleted successfully!'], 200);
            }
            return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Delete Product Error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to delete product', 'errors' => ['general' => $e->getMessage()]], 500);
            }
            return back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    public function getMetrics()
    {
        $totalItems = Product::count();
        $lowStockItems = Product::whereBetween('quantity', [3, 5])->count();
        $criticalStockItems = Product::where('quantity', '<=', 2)->count();

        return response()->json([
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'criticalStockItems' => $criticalStockItems,
        ]);
    }
}