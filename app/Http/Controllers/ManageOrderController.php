<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageOrderController extends Controller
{


    public function index()
    {
        $orders = Order::with('orderItems.product')->get();
        $products = Product::all();
        return view('cashier.dashboard', compact('products', 'orders'));
    }

    public function create()
    {
        $products = Product::all();
        return view('cashier.dashboard', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'order_type' => ['required', 'string', 'regex:/^(Dine-in|Takeout)$/i'],
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $total_price = 0;
            $orderItems = [];

            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                if ($product->quantity < $productData['quantity']) {
                    return back()->withErrors([
                        'products' => "Insufficient stock for {$product->product_name}. Available: {$product->quantity}"
                    ])->withInput();
                }

                $item_price = $product->price * $productData['quantity'];
                $total_price += $item_price;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $item_price,
                ];
            }

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'user_id' => Auth::id(),
                'total_price' => $total_price,
                'order_type' => ucwords(strtolower($validated['order_type'])),
                'special_instructions' => $validated['special_instructions'],
            ]);

            foreach ($orderItems as $item) {
                $product = Product::findOrFail($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $product->quantity -= $item['quantity'];
                $product->save();
            }

            DB::commit();
            return redirect()->route('cashier.dashboard')->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()])->withInput();
        }
    }
}