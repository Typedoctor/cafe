<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trash extends Model
{
    protected $fillable = ['product_name', 'category', 'quantity', 'reason', 'total_loss'];
}
