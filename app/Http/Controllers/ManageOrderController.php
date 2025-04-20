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
                'status' => 'pending',
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
            return back()->withErrors(['error' => 'Failed to create order. Please try again.'])->withInput();
        }
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::findOrFail($validated['order_id']);
            
            if ($order->status === 'completed') {
                return redirect()->route('cashier.dashboard')->withErrors(['error' => 'Cannot cancel a completed order.']);
            }

            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity;
                    $product->save();
                }
            }

            $order->orderItems()->delete();
            $order->delete();

            DB::commit();
            return redirect()->route('cashier.dashboard')->with('success', 'Order canceled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cashier.dashboard')->withErrors(['error' => 'Failed to cancel order. Please try again.']);
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
                return redirect()->route('cashier.dashboard')->withErrors(['error' => 'Order is already completed.']);
            }

            // Create a Transaction record for each OrderItem
            foreach ($order->orderItems as $item) {
                // Skip if the product no longer exists (due to onDelete('cascade'))
                if (!$item->product) {
                    continue;
                }

                Transaction::create([
                    'customer_name' => $order->customer_name,
                    'user_id' => $order->user_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'total_price' => $item->price, // Price for this specific product (quantity * product price)
                    'special_instructions' => $order->special_instructions,
                    'order_type' => $order->order_type,
                ]);
            }

            // Update the order status
            $order->status = 'completed';
            $order->save();

            // Delete the order and its items to remove it from the "Placed Orders" list
            $order->orderItems()->delete();
            $order->delete();

            DB::commit();
            return redirect()->route('cashier.dashboard')->with('success', 'Order marked as completed and transaction recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cashier.dashboard')->withErrors(['error' => 'Failed to mark order as completed. Please try again.']);
        }
    }
}