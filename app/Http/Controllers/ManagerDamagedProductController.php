<?php

namespace App\Http\Controllers;

use App\Models\DamagedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagerDamagedProductController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $month = $request->input('month', $currentMonth);
        $year = $request->input('year', $currentYear);

        $query = DamagedProduct::query();

        if ($month !== 'all') {
            $query->whereMonth('reported_at', $month);
        }
        if ($year !== 'all') {
            $query->whereYear('reported_at', $year);
        }

        $damagedProducts = $query->get();
        $summary = $damagedProducts->first() ?? (object)['total_loss' => 0.00, 'total_saved' => 0.00];

        return view('manager.damaged_products', [
            'damagedProducts' => $damagedProducts,
            'totalLoss' => $summary->total_loss,
            'totalSaved' => $summary->total_saved,
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|regex:/^[a-zA-Z\s\',&À-ÿ-]+$/|max:50', // Match frontend regex
            'quantity' => 'required|integer|min:1|max:9999', // 5-digit limit
            'price_per_item' => 'required|numeric|min:1',
            'reason' => 'required|string|regex:/^[a-zA-Z0-9\s,.()\-.À-ÿ]+$/|max:100',
            'supplier' =>'required|string|regex:/^[a-zA-Z\s,.&\'\-.À-ÿ]+$/|max:50',
            'reported_at' => 'nullable|date',
            'status' => 'required|in:Successfully Returned and Replaced,Marked as Loss',
            'return_notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('damaged-products.index')
                             ->withErrors($validator)
                             ->withInput();
        }

        $data = $request->all();
        // Calculate total_cost
        $data['total_cost'] = $data['quantity'] * $data['price_per_item'];
        $data['reported_at'] = $data['reported_at'] ?? now();
        $data['return_date'] = $data['status'] === 'Successfully Returned and Replaced' ? now() : null;

        DamagedProduct::create($data);
        DamagedProduct::updateTotals();

        return redirect()->route('damaged-products.index')
                         ->with('success', 'Damaged product reported successfully');
    }

    public function update(Request $request, DamagedProduct $damagedProduct)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|regex:/^[a-zA-Z\s\',&À-ÿ-]+$/|max:50', // Match frontend regex
            'quantity' => 'required|integer|min:1|max:99999', // 5-digit limit
            'price_per_item' => 'required|numeric|min:1',
            'reason' => 'required|string|regex:/^[a-zA-Z0-9\s,.()\-.À-ÿ]+$/|max:100',
            'supplier' =>'required|string|regex:/^[a-zA-Z\s,.&\'\-.À-ÿ]+$/|max:50',
            'reported_at' => 'nullable|date',
            'status' => 'required|in:Successfully Returned and Replaced,Marked as Loss',
            'return_notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('damaged-products.index')
                             ->withErrors($validator)
                             ->withInput();
        }

        $data = $request->all();
        // Calculate total_cost
        $data['total_cost'] = $data['quantity'] * $data['price_per_item'];
        $data['reported_at'] = $data['reported_at'] ?? $damagedProduct->reported_at;
        $data['return_date'] = $data['status'] === 'Successfully Returned and Replaced' ? now() : null;

        $damagedProduct->update($data);
        DamagedProduct::updateTotals();

        return redirect()->route('damaged-products.index')
                         ->with('success', 'Damaged product updated successfully');
    }

    public function destroy($id)
    {
        $product = DamagedProduct::find($id);
        if (!$product) {
            return redirect()->route('damaged-products.index')
                             ->with('error', 'Damaged product not found');
        }
        $product->delete();
        DamagedProduct::updateTotals();

        return redirect()->route('damaged-products.index')
                         ->with('success', 'Damaged product deleted successfully');
    }
}