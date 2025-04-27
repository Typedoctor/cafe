<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShelfItem extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity_added', 'price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}