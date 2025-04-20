<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
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
        // Validate input
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

            // Process each product
            foreach ($validated['products'] as $index => $productData) {
                $product = Product::findOrFail($productData['product_id']);

                // Check stock
                if ($product->quantity < $productData['quantity']) {
                    return back()->withErrors([
                        'products.' . $index . '.quantity' => "Insufficient stock for {$product->product_name}. Available: {$product->quantity}"
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

            // Create order
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'user_id' => Auth::id() ?? throw new \Exception('No authenticated user found'),
                'total_price' => $total_price,
                'order_type' => ucwords(strtolower($validated['order_type'])),
                'special_instructions' => $validated['special_instructions'] ?? '',
                'status' => 'pending',
            ]);

            // Create order items and update stock
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
            \Log::error('Store Order Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()])->withInput();
        }
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::with('orderItems.product')->findOrFail($validated['order_id']);
            
            if ($order->status === 'completed') {
                $orders = Order::with('orderItems.product')->get();
                $products = Product::all();
                return view('cashier.dashboard', compact('products', 'orders'))
                    ->withErrors(['error' => 'Cannot cancel a completed order.']);
            }

            // Delete the order and its items
            $order->orderItems()->delete();
            $order->delete();

            DB::commit();

            // Fetch updated orders and products for the view
            $orders = Order::with('orderItems.product')->get();
            $products = Product::all();
            return view('cashier.dashboard', compact('products', 'orders'))
                ->with('success', 'Order canceled successfully and transaction recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Cancel Order Error: ' . $e->getMessage());
            $orders = Order::with('orderItems.product')->get();
            $products = Product::all();
            return view('cashier.dashboard', compact('products', 'orders'))
                ->withErrors(['error' => 'Failed to cancel order: ' . $e->getMessage()]);
        }
    }

    public function complete(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::with('orderItems.product')->findOrFail($validated['order_id']);
            
            if ($order->status === 'completed') {
                $orders = Order::with('orderItems.product')->get();
                $products = Product::all();
                return view('cashier.dashboard', compact('products', 'orders'))
                    ->withErrors(['error' => 'Order is already completed.']);
            }

            // Create transaction records
            foreach ($order->orderItems as $item) {
                if (!$item->product) {
                    \Log::warning("OrderItem {$item->id} has no associated product. Skipping.");
                    continue;
                }

                Transaction::create([
                    'customer_name' => $order->customer_name,
                    'user_id' => $order->user_id ?? Auth::id() ?? throw new \Exception('No user ID available'),
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'total_price' => $item->price,
                    'special_instructions' => $order->special_instructions ?? '',
                    'order_type' => $order->order_type,
                    'status' => 'completed',
                ]);
            }

            // Update order status
            $order->status = 'completed';
            $order->save();

            // Delete order items and order
            $order->orderItems()->delete();
            $order->delete();

            DB::commit();

            $orders = Order::with('orderItems.product')->get();
            $products = Product::all();
            return view('cashier.dashboard', compact('products', 'orders'))
                ->with('success', 'Order marked as completed and transaction recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Complete Order Error: ' . $e->getMessage());
            $orders = Order::with('orderItems.product')->get();
            $products = Product::all();
            return view('cashier.dashboard', compact('products', 'orders'))
                ->withErrors(['error' => 'Failed to mark order as completed: ' . $e->getMessage()]);
        }
    }
}