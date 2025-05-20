<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Order extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'customer_name',
        'user_id',
        'product_id',
        'status',
        'total_price',
        'order_type',
        'money_received',
        'special_instructions',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'N/A']);
    }
}