<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use App\Models\ShelfItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManageTrashController extends Controller
{
    public function index()
    {
        $trashes = Trash::latest()->get();
        $shelfItems = ShelfItem::with('product')
            ->whereHas('product')
            ->where('quantity_added', '>', 0)
            ->orderBy('product_id')
            ->get();

        return view('cashier.manage_trash', compact('trashes', 'shelfItems'));
    }

    public function store(Request $request)
    {
        \Log::info('Store request data: ' . json_encode($request->all()));
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|exists:products,product_name',
            'category' => 'required|string|in:snack,drink,meal,dessert',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $shelfItem = ShelfItem::with('product')
                    ->whereHas('product', function ($query) use ($validated) {
                        $query->where('product_name', $validated['product_name']);
                    })
                    ->firstOrFail();

                if ($shelfItem->quantity_added < $validated['quantity']) {
                    throw new \Exception('Not enough product quantity available.');
                }

                // Calculate total_loss
                $validated['total_loss'] = $shelfItem->price * $validated['quantity'];

                Trash::create($validated);
                $shelfItem->decrement('quantity_added', $validated['quantity']);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('trash.index')
            ->with('success', 'Trash entry added successfully!');
    }

    public function destroy($id)
    {
        \Log::info('Destroy method called', ['id' => $id]);
        $trash = Trash::find($id);
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
            \Log::error('Error deleting trash entry: ' . $e->getMessage());
            return redirect()->route('trash.index')->with('error', 'Failed to delete trash entry');
        }

        return redirect()->route('trash.index')->with('success', 'Trash entry deleted successfully and quantity restored');
    }
}