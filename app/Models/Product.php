<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Product extends Model
{
    use HasFactory;
    use Auditable;

     protected $fillable = [
        'product_name',
        'category',
        'quantity',
        'supplier',
        'unit_of_measurement',
        'purchase_cost', // Add purchase cost
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
