<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Spoilage extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = ['product_name', 'category', 'quantity', 'reason', 'total_loss'];
}
