<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Spoilage extends Model
{
    use Auditable;

    protected $fillable = ['product_name', 'category', 'quantity', 'reason', 'total_loss'];
}
