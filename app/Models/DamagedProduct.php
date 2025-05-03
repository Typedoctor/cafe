<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class DamagedProduct extends Model
{
    use Auditable;

    protected $fillable = ['product_name', 'quantity', 'reason', 'supplier', 'reported_at'];
    protected $casts = ['reported_at' => 'datetime',
    ];
}