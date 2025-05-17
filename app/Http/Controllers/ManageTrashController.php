<?php

namespace App\Http\Controllers;

use App\Models\Spoilage;
use App\Models\ShelfItem;
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

        // Modified to include zero-stock items
        $shelfItems = ShelfItem::with('product')
            ->whereHas('product')
            ->orderBy('product_id')
            ->get();

        return view('cashier.manage_trash', compact('trashes', 'shelfItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_names' => 'required|array|min:1',
            'product_names.*' => 'required|string|exists:products,product_name',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $productNames = $validated['product_names'];
                $quantities = $validated['quantities'];

                // Ensure arrays have the same length
                if (count($productNames) !== count($quantities)) {
                    throw new \Exception('Mismatch between product names and quantities.');
                }

                $errors = [];

                foreach ($productNames as $index => $productName) {
                    $quantity = $quantities[$index];

                    $shelfItem = ShelfItem::with('product')
                        ->whereHas('product', function ($query) use ($productName) {
                            $query->where('product_name', $productName);
                        })
                        ->first();

                    if (!$shelfItem) {
                        $errors[] = "Product '$productName' not found in shelf items.";
                        continue;
                    }

                    if ($shelfItem->quantity_added < $quantity) {
                        $errors[] = "Not enough quantity available for '$productName'. Available: {$shelfItem->quantity_added}, Requested: $quantity.";
                        continue;
                    }

                    // Create spoilage entry
                    $spoilageData = [
                        'product_name' => $productName,
                        'category' => $validated['category'],
                        'quantity' => $quantity,
                        'reason' => $validated['reason'],
                        'total_loss' => $shelfItem->price * $quantity,
                    ];

                    $trash = Spoilage::create($spoilageData);
                    \Log::info('New trash entry created', ['trash_id' => $trash->id]);

                    // Update stock
                    $shelfItem->decrement('quantity_added', $quantity);
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