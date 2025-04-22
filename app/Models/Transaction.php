<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['customer_name','user_id', 'product_id','product_name', 'quantity', 'total_price', 'special_instructions', 'order_type', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'N/A']);
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}