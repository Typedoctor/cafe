<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class Transaction extends Model
{
    use Auditable;

    protected $fillable = [
        'customer_name',
        'user_id',
        'product_name',
        'quantity',
        'total_price',
        'special_instructions',
        'order_type',
        'status',
        'transaction_id', 
        'money_received',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'N/A']);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Generate a transaction_id in the format T-YYYYMMDD-HHMMSS-XXXX
            $timestamp = now()->format('YmdHis');
            $random = strtoupper(Str::random(4));
            $transaction->transaction_id = "T-{$timestamp}-{$random}";
        });
    }
}