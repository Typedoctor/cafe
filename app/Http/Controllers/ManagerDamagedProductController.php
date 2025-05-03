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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        if (empty($data['reported_at'])) {
            $data['reported_at'] = now();
        }

        $damagedProduct = DamagedProduct::create($data);

        return response()->json(['damagedProduct' => $damagedProduct], 201);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        if (empty($data['reported_at'])) {
            $data['reported_at'] = $damagedProduct->reported_at;
        }

        $damagedProduct->update($data);

        return response()->json(['damagedProduct' => $damagedProduct], 200);
    }

    public function destroy($id)
    {
        \Log::info('Destroy method called', ['id' => $id]);
        $product = DamagedProduct::find($id);
        if (!$product) {
            return redirect()->route('damaged-products.index')->with('error', 'product not found');
        }
        $product->delete();
        return redirect()->route('damaged-products.index')->with('success', 'product deleted successfully');
    }
}