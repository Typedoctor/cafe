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

        $shelfItems = ShelfItem::with('product')
            ->whereHas('product')
            ->where('quantity_added', '>', 0)
            ->orderBy('product_id')
            ->get();

        return view('cashier.manage_trash', compact('trashes', 'shelfItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|exists:products,product_name',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $shelfItem = ShelfItem::with('product')
                    ->whereHas('product', function ($query) use ($validated) {
                        $query->where('product_name', $validated['product_name']);
                    })
                    ->first();

                if (!$shelfItem) {
                    throw new \Exception('Product not found in shelf items.');
                }

                if ($shelfItem->quantity_added < $validated['quantity']) {
                    throw new \Exception('Not enough product quantity available.');
                }

                // Calculate total_loss
                $validated['total_loss'] = $shelfItem->price * $validated['quantity'];

                $trash = Spoilage::create($validated);
                \Log::info('New trash entry created', ['trash_id' => $trash->id]);

                $shelfItem->decrement('quantity_added', $validated['quantity']);
            });

            return redirect()->route('trash.index', $request->only(['month', 'year']))
                ->with('success', 'Trash entry added successfully!');
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