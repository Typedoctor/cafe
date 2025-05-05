<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShelfItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierManageOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('orderItems.product')->get();
        $shelfItems = ShelfItem::with('product')
            ->whereHas('product') // Ensure product exists
            ->where('quantity_added', '>', 0) // Only items with stock
            ->get();
        return view('cashier.manage_order', compact('shelfItems', 'orders'));
    }

    public function create()
    {
        $shelfItems = ShelfItem::with('product')
            ->whereHas('product') // Ensure product exists
            ->where('quantity_added', '>', 0) // Only items with stock
            ->get();
        return view('cashier.manage_order', compact('shelfItems'));
    }

    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:shelf_items,product_id',
            'products.*.quantity' => 'required|integer|min:1',
            'order_type' => ['required', 'string', 'regex:/^(Dine-in|Takeout)$/i'],
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $total_price = 0;
            $orderItems = [];
            $productQuantities = []; // Track total quantities per product

            // Aggregate quantities by product
            foreach ($validated['products'] as $index => $productData) {
                $shelfItem = ShelfItem::with('product')->where('product_id', $productData['product_id'])->firstOrFail();
                $quantity = $productData['quantity'];

                // Sum quantities for the same product
                if (!isset($productQuantities[$shelfItem->product_id])) {
                    $productQuantities[$shelfItem->product_id] = [
                        'name' => $shelfItem->product->product_name,
                        'available' => $shelfItem->quantity_added,
                        'requested' => 0,
                    ];
                }
                $productQuantities[$shelfItem->product_id]['requested'] += $quantity;

                // Check stock for this item
                if ($shelfItem->quantity_added < $quantity) {
                    return back()->withErrors([
                        'products.' . $index . '.quantity' => "Insufficient stock for {$shelfItem->product->product_name}. Available: {$shelfItem->quantity_added}"
                    ])->withInput();
                }

                $item_price = ($shelfItem->price ?? 0) * $quantity;
                $total_price += $item_price;

                $orderItems[] = [
                    'product_id' => $shelfItem->product_id,
                    'quantity' => $quantity,
                    'price' => $item_price,
                ];
            }

            // Check cumulative stock for each product
            foreach ($productQuantities as $productId => $data) {
                if ($data['requested'] > $data['available']) {
                    return back()->withErrors([
                        'products' => "Insufficient stock for {$data['name']}. Total requested: {$data['requested']}, Available: {$data['available']}"
                    ])->withInput();
                }
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
                $shelfItem = ShelfItem::where('product_id', $item['product_id'])->firstOrFail();
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $shelfItem->quantity_added -= $item['quantity'];
                if ($shelfItem->quantity_added < 0) {
                    throw new \Exception("Stock for {$shelfItem->product->product_name} cannot go below zero.");
                }
                $shelfItem->save();
            }

            DB::commit();
            return redirect()->route('cashier.manage_order')->with('success', 'Order created successfully.');
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
                $shelfItems = ShelfItem::with('product')
                    ->whereHas('product')
                    ->where('quantity_added', '>', 0)
                    ->get();
                return view('cashier.manage_order', compact('shelfItems', 'orders'))
                    ->withErrors(['error' => 'Cannot cancel a completed order.']);
            }

            // Restore stock
            foreach ($order->orderItems as $item) {
                $shelfItem = ShelfItem::where('product_id', $item->product_id)->firstOrFail();
                $shelfItem->quantity_added += $item->quantity;
                $shelfItem->save();
            }

            // Delete the order and its items
            $order->orderItems()->delete();
            $order->delete();

            DB::commit();

            $orders = Order::with('orderItems.product')->get();
            $shelfItems = ShelfItem::with('product')
                ->whereHas('product')
                ->where('quantity_added', '>', 0)
                ->get();
            return view('cashier.manage_order', compact('shelfItems', 'orders'))
                ->with('success', 'Order canceled successfully and stock restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Cancel Order Error: ' . $e->getMessage());
            $orders = Order::with('orderItems.product')->get();
            $shelfItems = ShelfItem::with('product')
                ->whereHas('product')
                ->where('quantity_added', '>', 0)
                ->get();
            return view('cashier.dashboarmanage_orderd', compact('shelfItems', 'orders'))
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
                $shelfItems = ShelfItem::with('product')
                    ->whereHas('product')
                    ->where('quantity_added', '>', 0)
                    ->get();
                return view('cashier.manage_order', compact('shelfItems', 'orders'))
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
                    'product_name' => $item->product->product_name,
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
            $shelfItems = ShelfItem::with('product')
                ->whereHas('product')
                ->where('quantity_added', '>', 0)
                ->get();
            return view('cashier.manage_order', compact('shelfItems', 'orders'))
                ->with('success', 'Order marked as completed and transaction recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Complete Order Error: ' . $e->getMessage());
            $orders = Order::with('orderItems.product')->get();
            $shelfItems = ShelfItem::with('product')
                ->whereHas('product')
                ->where('quantity_added', '>', 0)
                ->get();
            return view('cashier.manage_order', compact('shelfItems', 'orders'))
                ->withErrors(['error' => 'Failed to mark order as completed: ' . $e->getMessage()]);
        }
    }
}