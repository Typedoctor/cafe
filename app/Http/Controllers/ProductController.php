<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller {
    public function index(Request $request) {
        $query = Product::query();

        // Search by product name
        if ($request->has('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $products = $query->get();
        
        return view('manager.product', compact('products'));
    }

    public function create() {
        return view('products.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'price' => 'required|numeric',
            'quantity' => 'required|integer'
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product added successfully!');
    }

    public function edit(Product $product) {
        return view('products.edit', compact('product'));
    }

    public function destroy(Product $product) {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted!');
    }

    /*pag update han existing product*/ 
    public function update(Request $request, Product $product) {
        $request->validate([
            'product_name' => 'required|string|unique:products,product_name,' . $product->id,
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'numeric|min:0',
            'price' => 'numeric|min:0',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }    
}