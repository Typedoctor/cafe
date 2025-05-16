<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShelfItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManagerShelfController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $shelfItems = ShelfItem::with('product')->get();
        return view('manager.add_to_shelf', compact('products', 'shelfItems'));
    }

    public function metrics()
    {
        $totalItems = ShelfItem::count();
        $lowStockItems = ShelfItem::whereBetween('quantity_added', [3, 5])->count();
        $criticalStockItems = ShelfItem::where('quantity_added', '<=', 2)->count();

        return response()->json([
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'criticalStockItems' => $criticalStockItems,
        ]);
    }

    public function check(Request $request)
    {
        $productId = $request->input('product_id');
        $shelfItem = ShelfItem::where('product_id', $productId)->with('product')->first();

        if ($shelfItem) {
            return response()->json([
                'exists' => true,
                'quantity_added' => $shelfItem->quantity_added,
                'price' => $shelfItem->price ?? null,
                'product_name' => $shelfItem->product->product_name,
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_added' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0|max:1200',
        ]);

        try {
            DB::beginTransaction();

            $itemQuantities = [];

            // Validate stock availability
            foreach ($validated['items'] as $index => $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $existingItem = ShelfItem::where('product_id', $itemData['product_id'])->first();

                if (!isset($itemQuantities[$product->id])) {
                    $itemQuantities[$product->id] = [
                        'name' => $product->product_name,
                        'available' => $product->quantity,
                        'requested' => 0,
                        'existing_quantity' => $existingItem ? $existingItem->quantity_added : 0,
                    ];
                }
                $itemQuantities[$product->id]['requested'] += $itemData['quantity_added'];

                // Check if the total requested quantity (including existing) exceeds available stock
                $totalRequested = $itemQuantities[$product->id]['requested'] + $itemQuantities[$product->id]['existing_quantity'];
                if ($totalRequested > $product->quantity) {
                    return response()->json([
                        'errors' => [
                            'items.' . $index . '.quantity_added' => "Insufficient stock for {$product->product_name}. Available: {$product->quantity}, Total requested: {$totalRequested}",
                        ]
                    ], 422);
                }
            }

            // Process each item
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $existingItem = ShelfItem::where('product_id', $itemData['product_id'])->first();

                if ($existingItem) {
                    // Update existing shelf item by adding to the quantity and keeping the price if provided
                    $existingItem->quantity_added += $itemData['quantity_added'];
                    $existingItem->price = $itemData['price'] ?? $existingItem->price; // Use new price if provided, otherwise keep existing
                    $existingItem->save();
                } else {
                    // Create new shelf item
                    ShelfItem::create([
                        'product_id' => $itemData['product_id'],
                        'quantity_added' => $itemData['quantity_added'],
                        'price' => $itemData['price'],
                    ]);
                }

                // Deduct from product stock
                $product->quantity -= $itemData['quantity_added'];
                $product->save();
            }

            DB::commit();
            return response()->json(['message' => 'Items added to shelf successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add to Shelf Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add items to shelf: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $shelfItem = ShelfItem::with('product')->findOrFail($id);
        return response()->json([
            'id' => $shelfItem->id,
            'product_name' => $shelfItem->product->product_name,
            'quantity_added' => $shelfItem->quantity_added,
            'price' => $shelfItem->price,
            'product_id' => $shelfItem->product_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $shelfItem = ShelfItem::findOrFail($id);
        $product = Product::findOrFail($shelfItem->product_id);

        $validated = $request->validate([
            'quantity_added' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0|max:1200',
        ]);

        try {
            DB::beginTransaction();

            $newQuantity = $validated['quantity_added'];
            $oldQuantity = $shelfItem->quantity_added;
            $quantityDifference = $newQuantity - $oldQuantity;

            if ($quantityDifference > 0 && $product->quantity < $quantityDifference) {
                return response()->json([
                    'errors' => [
                        'quantity_added' => "Insufficient stock for {$product->product_name}. Available: {$product->quantity}, Requested additional: {$quantityDifference}",
                    ]
                ], 422);
            }

            $shelfItem->quantity_added = $newQuantity;
            $shelfItem->price = $validated['price'];
            $shelfItem->save();

            if ($quantityDifference > 0) {
                $product->quantity -= $quantityDifference;
            } elseif ($quantityDifference < 0) {
                $product->quantity += abs($quantityDifference);
            }
            $product->save();

            DB::commit();
            return response()->json(['message' => 'Shelf item updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Shelf Item Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update shelf item: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $shelfItem = ShelfItem::findOrFail($id);
        $product = Product::findOrFail($shelfItem->product_id);

        try {
            DB::beginTransaction();

            $product->quantity += $shelfItem->quantity_added;
            $product->save();

            $shelfItem->delete();

            DB::commit();
            return response()->json(['message' => 'Item deleted from shelf successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Remove Shelf Item Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to remove shelf item: ' . $e->getMessage()], 500);
        }
    }
}