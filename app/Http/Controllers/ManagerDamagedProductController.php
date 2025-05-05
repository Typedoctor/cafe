<?php

namespace App\Http\Controllers;

use App\Models\DamagedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagerDamagedProductController extends Controller
{
    public function index()
    {
        $damagedProducts = DamagedProduct::all();
        return view('manager.damaged_products', compact('damagedProducts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255|unique:damaged_products,product_name',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'supplier' => 'required|string|max:255',
            'reported_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->route('damaged-products.index')
                             ->withErrors($validator)
                             ->withInput();
        }

        $data = $request->all();
        if (empty($data['reported_at'])) {
            $data['reported_at'] = now();
        }

        DamagedProduct::create($data);

        return redirect()->route('damaged-products.index') ->with('success', 'Damaged product reported successfully');
    }

    public function update(Request $request, DamagedProduct $damagedProduct)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255|unique:damaged_products,product_name,' . $damagedProduct->id,
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'supplier' => 'required|string|max:255',
            'reported_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->route('damaged-products.index')
                             ->withErrors($validator)
                             ->withInput();
        }

        $data = $request->all();
        if (empty($data['reported_at'])) {
            $data['reported_at'] = $damagedProduct->reported_at;
        }

        $damagedProduct->update($data);

        return redirect()->route('damaged-products.index') ->with('success', 'Damaged product updated successfully');
    }

    public function destroy($id)
    {
        \Log::info('Destroy method called', ['id' => $id]);
        $product = DamagedProduct::find($id);
        if (!$product) {
            return redirect()->route('damaged-products.index') ->with('error', 'Damaged product not found');
        }
        $product->delete();
        return redirect()->route('damaged-products.index')->with('success', 'Damaged product deleted successfully');
    }
}