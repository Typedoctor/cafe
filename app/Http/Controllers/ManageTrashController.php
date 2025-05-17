<?php

namespace App\Http\Controllers;

use App\Models\Spoilage;
use App\Models\ShelfItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManageTrashController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $trashes = Spoilage::query()
            ->when($month && $year, function ($query) use ($month, $year) {
                return $query->whereMonth('created_at', $month)
                             ->whereYear('created_at', $year);
            })
            ->latest()
            ->get();

        $shelfItems = ShelfItem::with('product')
            ->whereHas('product')
            ->orderBy('product_id')
            ->get();

        $products = Product::all();

        return view('cashier.manage_trash', compact('trashes', 'shelfItems', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_names' => 'required|array|min:1',
            'product_names.*' => 'required|string',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'reason' => 'required|string|max:255',
            'source' => 'required|string|in:inventory,shelf,all',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $productNames = $validated['product_names'];
                $quantities = $validated['quantities'];
                $source = $request->input('source', 'inventory');

                if (count($productNames) !== count($quantities)) {
                    throw new \Exception('Mismatch between product names and quantities.');
                }

                $errors = [];

                foreach ($productNames as $index => $productName) {
                    $quantity = $quantities[$index];
                    $category = $validated['category'];
                    $reason = $validated['reason'];

                    $itemSource = $source;
                    // If "all", determine source by checking shelf first, then inventory
                    $shelfItem = ShelfItem::with('product')
                        ->whereHas('product', function ($query) use ($productName) {
                            $query->where('product_name', $productName);
                        })
                        ->first();

                    $product = Product::where('product_name', $productName)->first();

                    if ($itemSource === 'all') {
                        if ($shelfItem && $shelfItem->quantity_added > 0) {
                            $itemSource = 'shelf';
                        } elseif ($product && $product->quantity > 0) {
                            $itemSource = 'inventory';
                        } else {
                            $itemSource = null;
                        }
                    }

                    if ($itemSource === 'shelf' && $shelfItem) {
                        if ($shelfItem->quantity_added < $quantity) {
                            $errors[] = "Not enough quantity available for '$productName' in shelf. Available: {$shelfItem->quantity_added}, Requested: $quantity.";
                            continue;
                        }
                        $price = $shelfItem->price - $shelfItem->product->purchase_cost;
                        $totalLoss = $price * $quantity;

                        Spoilage::create([
                            'product_name' => $productName,
                            'category' => $category,
                            'quantity' => $quantity,
                            'reason' => $reason,
                            'total_loss' => $totalLoss,
                        ]);
                        $shelfItem->decrement('quantity_added', $quantity);
                    } elseif ($itemSource === 'inventory' && $product) {
                        if ($product->quantity < $quantity) {
                            $errors[] = "Not enough quantity available for '$productName' in inventory. Available: {$product->quantity}, Requested: $quantity.";
                            continue;
                        }
                        $price = $product->purchase_cost;
                        $totalLoss = $price * $quantity;

                        Spoilage::create([
                            'product_name' => $productName,
                            'category' => $category,
                            'quantity' => $quantity,
                            'reason' => $reason,
                            'total_loss' => $totalLoss,
                        ]);
                        $product->decrement('quantity', $quantity);
                    } else {
                        $errors[] = "Product '$productName' not found in shelf or inventory.";
                    }
                }

                if (!empty($errors)) {
                    throw new \Exception(implode(' ', $errors));
                }
            });

            return redirect()->route('trash.index', $request->only(['month', 'year']))
                ->with('success', 'Trash entries added successfully!');
        } catch (\Exception $e) {
            \Log::error('Error in store transaction', ['error' => $e->getMessage()]);
            return redirect()->route('trash.index', $request->only(['month', 'year']))
                ->withErrors(['quantity' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $trash = Spoilage::find($id);
        if (!$trash) {
            return redirect()->route('trash.index')->with('error', 'Trash entry not found');
        }

        try {
            DB::transaction(function () use ($trash) {
                $shelfItem = ShelfItem::with('product')
                    ->whereHas('product', function ($query) use ($trash) {
                        $query->where('product_name', $trash->product_name);
                    })
                    ->first();

                if ($shelfItem) {
                    $shelfItem->increment('quantity_added', $trash->quantity);
                }

                $trash->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('trash.index')->with('error', 'Failed to delete trash entry');
        }

        return redirect()->route('trash.index')->with('success', 'Trash entry deleted successfully and quantity restored');
    }
}