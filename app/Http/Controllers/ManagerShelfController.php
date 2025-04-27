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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_added' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $itemQuantities = [];

            foreach ($validated['items'] as $index => $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantity = $itemData['quantity_added'];

                if (!isset($itemQuantities[$product->id])) {
                    $itemQuantities[$product->id] = [
                        'name' => $product->product_name,
                        'available' => $product->quantity,
                        'requested' => 0,
                    ];
                }
                $itemQuantities[$product->id]['requested'] += $quantity;

                if ($product->quantity < $quantity) {
                    return back()->withErrors([
                        'items.' . $index . '.quantity_added' => "Insufficient stock for {$product->product_name}. Available: {$product->quantity}",
                    ])->withInput();
                }
            }

            foreach ($itemQuantities as $productId => $data) {
                if ($data['requested'] > $data['available']) {
                    return back()->withErrors([
                        'items' => "Insufficient stock for {$data['name']}. Total requested: {$data['requested']}, Available: {$data['available']}",
                    ])->withInput();
                }
            }

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $existingItem = ShelfItem::where('product_id', $itemData['product_id'])->first();

                if ($existingItem) {
                    $existingItem->quantity_added += $itemData['quantity_added'];
                    $existingItem->price = $itemData['price'] ?? $existingItem->price;
                    
                    $existingItem->save();
                } else {
                    ShelfItem::create([
                        'product_id' => $itemData['product_id'],
                        'quantity_added' => $itemData['quantity_added'],
                        'price' => $itemData['price'] ?? null,
                        
                    ]);
                }

                $product->quantity -= $itemData['quantity_added'];
                $product->save();
            }

            DB::commit();
            return redirect()->route('add-to-shelf.index')->with('success', 'Items added to shelf successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add to Shelf Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to add items to shelf: ' . $e->getMessage()])->withInput();
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
            return redirect()->route('add-to-shelf.index')->with('success', 'Shelf item removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Remove Shelf Item Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to remove shelf item: ' . $e->getMessage()])->withInput();
        }
    }
}