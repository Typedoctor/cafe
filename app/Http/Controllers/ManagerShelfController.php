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
            'items.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:1200',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $purchaseCost = $request->input("items.$index.purchase_cost");
                    if ($purchaseCost !== null && $value <= $purchaseCost) {
                        $fail('The price must be greater than the purchase cost.');
                    }
                }
            ],
        ]);

        try {
            DB::beginTransaction();

            $errors = [];
            $productQuantities = [];

            // Aggregate quantities by product_id
            foreach ($validated['items'] as $index => $item) {
                $productId = $item['product_id'];
                if (!isset($productQuantities[$productId])) {
                    $productQuantities[$productId] = [
                        'quantity' => 0,
                        'price' => $item['price'],
                        'indices' => [],
                    ];
                }
                $productQuantities[$productId]['quantity'] += $item['quantity_added'];
                $productQuantities[$productId]['indices'][] = $index;
                $productQuantities[$productId]['price'] = $item['price']; // Use last price
            }

            // Validate new quantity against available stock
            foreach ($productQuantities as $productId => $data) {
                $product = Product::findOrFail($productId);
                $newQuantity = $data['quantity'];

                if ($newQuantity > $product->quantity) {
                    foreach ($data['indices'] as $index) {
                        $errors["items.{$index}.quantity_added"] = ["Insufficient stock for '{$product->product_name}'. Available: {$product->quantity}, Requested: {$newQuantity}."];
                    }
                }
            }

            if ($errors) {
                return response()->json(['errors' => $errors], 422);
            }

            // Process items
            foreach ($productQuantities as $productId => $data) {
                $product = Product::findOrFail($productId);
                $existingItem = ShelfItem::where('product_id', $productId)->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity_added' => $existingItem->quantity_added + $data['quantity'],
                        'price' => $data['price'],
                    ]);
                } else {
                    ShelfItem::create([
                        'product_id' => $productId,
                        'quantity_added' => $data['quantity'],
                        'price' => $data['price'],
                    ]);
                }

                $product->quantity -= $data['quantity'];
                $product->save();
            }

            DB::commit();
            return response()->json(['message' => 'Items added to shelf successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add to Shelf Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add items to shelf.'], 500);
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
            'quantity_added' => 'nullable|integer|min:1',
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:1200',
                function ($attribute, $value, $fail) use ($product) {
                    if ($value <= $product->purchase_cost) {
                        $fail('The price must be greater than the purchase cost.');
                    }
                }
            ],
        ]);

        try {
            DB::beginTransaction();

            $newQuantity = $validated['quantity_added'] ?? $shelfItem->quantity_added;
            $oldQuantity = $shelfItem->quantity_added;
            $quantityDifference = $newQuantity - $oldQuantity;

            if (
                isset($validated['quantity_added']) &&
                $quantityDifference > 0 &&
                $quantityDifference > $product->quantity
            ) {
                return response()->json([
                    'errors' => [
                        'quantity_added' => ["Insufficient stock for '{$product->product_name}'. Available: {$product->quantity}, Requested additional: {$quantityDifference}."]
                    ]
                ], 422);
            }

            $shelfItem->update([
                'quantity_added' => $newQuantity,
                'price' => $validated['price'],
            ]);

            if (isset($validated['quantity_added'])) {
                if ($quantityDifference > 0) {
                    $product->quantity -= $quantityDifference;
                } elseif ($quantityDifference < 0) {
                    $product->quantity += abs($quantityDifference);
                }
                $product->save();
            }

            DB::commit();
            return response()->json(['message' => 'Shelf item updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Shelf Item Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update shelf item.'], 500);
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
            return response()->json(['message' => 'Shelfed Item deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Remove Shelf Item Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to remove shelf item.'], 500);
        }
    }
}