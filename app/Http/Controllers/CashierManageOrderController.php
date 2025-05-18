<?php

  namespace App\Http\Controllers;

  use Illuminate\Http\Request;
  use App\Models\ShelfItem;
  use App\Models\Order;
  use App\Models\OrderItem;
  use App\Models\Transaction;
  use App\Models\Sale;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Str;

  class CashierManageOrderController extends Controller
  {
      public function index()
      {
          $orders = Order::with('orderItems.product')->paginate(10);
          $shelfItems = ShelfItem::with('product')
              ->whereHas('product')
              ->where('quantity_added', '>=', 0)
              ->paginate(50);
          return view('cashier.manage_order', compact('shelfItems', 'orders'));
      }

      public function create()
      {
          return redirect()->route('cashier.manage_order');
      }

     
            public function store(Request $request)
            {
                $validated = $request->validate([
                    'customer_name' => 'required|string|max:24|regex:/^[a-zA-Z\s\',&À-ÿ-]+$/',
                    'products' => 'required|array|min:1',
                    'products.*.product_id' => 'required|exists:shelf_items,product_id',
                    'products.*.quantity' => 'required|integer|min:1',
                    'order_type' => ['required', 'string', 'regex:/^(Dine-in|Takeout)$/i'],
                    'special_instructions' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\',&À-ÿ%.-]+$/',
                    'money_received' => 'required|numeric|min:0',
                ]);

                try {
                    DB::beginTransaction();

                    $total_price = 0;
                    $orderItems = [];
                    $productQuantities = [];

                    foreach ($validated['products'] as $index => $productData) {
                        $shelfItem = ShelfItem::with('product')->where('product_id', $productData['product_id'])->firstOrFail();
                        $quantity = $productData['quantity'];

                        if (!isset($productQuantities[$shelfItem->product_id])) {
                            $productQuantities[$shelfItem->product_id] = [
                                'name' => $shelfItem->product->product_name,
                                'available' => $shelfItem->quantity_added,
                                'requested' => 0,
                                'shelfItem' => $shelfItem,
                            ];
                        }
                        $productQuantities[$shelfItem->product_id]['requested'] += $quantity;
                    }

                    foreach ($productQuantities as $productId => $data) {
                        if ($data['requested'] > $data['available']) {
                            return back()->withErrors([
                                'products' => "Insufficient stock for {$data['name']}. Total requested: {$data['requested']}, Available: {$data['available']}"
                            ])->withInput();
                        }
                    }

                    foreach ($validated['products'] as $productData) {
                        $shelfItem = $productQuantities[$productData['product_id']]['shelfItem'];
                        $quantity = $productData['quantity'];
                        // Use selling price (purchase cost + profit)
                        $unit_price = $shelfItem->price ?? 0;
                        $item_price = $unit_price * $quantity;
                        $total_price += $item_price;

                        $orderItems[] = [
                            'product_id' => $shelfItem->product_id,
                            'quantity' => $quantity,
                            'price' => $item_price,
                        ];
                    }

                    $change = max(0, $validated['money_received'] - $total_price);

                    $order = Order::create([
                        'customer_name' => $validated['customer_name'],
                        'user_id' => Auth::id(),
                        'total_price' => $total_price,
                        'order_type' => ucwords(strtolower($validated['order_type'])),
                        'special_instructions' => $validated['special_instructions'] ? Str::of($validated['special_instructions'])->stripTags() : '',
                        'money_received' => $validated['money_received'],
                        'status' => 'pending',
                    ]);

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

                        // Use selling price as unit_price
                        $unit_price = $shelfItem->price ?? 0;

                        Sale::create([
                            'order_id' => $order->id,
                            'product_id' => $shelfItem->product_id,
                            'product_name' => $shelfItem->product->product_name,
                            'quantity' => $item['quantity'],
                            'unit_price' => $unit_price,
                            'total_price' => $item['price'],
                            'money_received' => $validated['money_received'],
                            'change' => $change,
                            'user_id' => Auth::id(),
                            'customer_name' => $validated['customer_name'],
                            'order_type' => ucwords(strtolower($validated['order_type'])),
                            'status' => 'pending',
                            'special_instructions' => $validated['special_instructions'] ? Str::of($validated['special_instructions'])->stripTags() : '',
                        ]);
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
              
              if ($order->status === 'Completed') {
                  return redirect()->route('cashier.manage_order')
                      ->withErrors(['error' => 'Cannot cancel a completed order.']);
              }

              foreach ($order->orderItems as $item) {
                  $shelfItem = ShelfItem::where('product_id', $item->product_id)->firstOrFail();
                  $shelfItem->quantity_added += $item->quantity;
                  $shelfItem->save();

                  Sale::where('order_id', $order->id)
                      ->where('product_id', $item->product_id)
                      ->where('quantity', $item->quantity)
                      ->delete();
              }

              $order->orderItems()->delete();
              $order->delete();

              DB::commit();
              return redirect()->route('cashier.manage_order')
                  ->with('success', 'Order canceled successfully and stock restored.');
          } catch (\Exception $e) {
              DB::rollBack();
              \Log::error('Cancel Order Error: ' . $e->getMessage());
              return redirect()->route('cashier.manage_order')
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
              
              if ($order->status === 'Completed') {
                  return redirect()->route('cashier.manage_order')
                      ->withErrors(['error' => 'Order is already completed.']);
              }

              $total_quantity = 0;
              $total_price = 0;
              $product_names = [];
              $change = max(0, $order->money_received - $order->total_price);

              foreach ($order->orderItems as $item) {
                  if (!$item->product) {
                      \Log::warning("OrderItem {$item->id} has no associated product. Skipping.");
                      continue;
                  }
                  $total_quantity += $item->quantity;
                  $total_price += $item->price;
                  $product_names[] = $item->product->product_name;

                  Sale::where('order_id', $order->id)
                      ->where('product_id', $item->product_id)
                      ->where('quantity', $item->quantity)
                      ->update(['status' => 'Completed', 'change' => $change]);
              }

              if (empty($product_names)) {
                  throw new \Exception('No valid products found in the order.');
              }

              Transaction::create([
                  'customer_name' => $order->customer_name,
                  'user_id' => $order->user_id ?? Auth::id(),
                  'product_name' => implode(', ', $product_names),
                  'quantity' => $total_quantity,
                  'total_price' => $total_price,
                  'change' => $change,
                  'special_instructions' => $order->special_instructions ?? '',
                  'order_type' => $order->order_type,
                  'status' => 'Completed',
                  'money_received' => $order->money_received,
              ]);

              $order->status = 'Completed';
              $order->save();

              $order->orderItems()->delete();
              $order->delete();

              DB::commit();
              return redirect()->route('cashier.manage_order')
                  ->with('success', 'Order marked as completed and transaction recorded.');
          } catch (\Exception $e) {
              DB::rollBack();
              \Log::error('Complete Order Error: ' . $e->getMessage());
              return redirect()->route('cashier.manage_order')
                  ->withErrors(['error' => 'Failed to mark order as completed: ' . $e->getMessage()]);
          }
      }
  }