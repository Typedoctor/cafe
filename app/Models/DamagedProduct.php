<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamagedProduct extends Model
{
    protected $fillable = ['product_name', 'quantity', 'reason', 'supplier', 'reported_at'];
    protected $casts = ['reported_at' => 'datetime',
    ];
}