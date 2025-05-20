<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class DamagedProduct extends Model
{
    use HasFactory;
    use Auditable;


    const STATUS_RETURNED = 'Successfully Returned';
    const STATUS_LOSS = 'Marked as Loss';

    protected $fillable = [
        'product_name',
        'quantity',
        'price_per_item',
        'reason',
        'supplier',
        'reported_at',
        'status',
        'return_date',
        'total_loss',
        'total_saved',
        'return_notes'
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'return_date' => 'datetime',
        'price_per_item' => 'decimal:2',
        'total_loss' => 'decimal:2',
        'total_saved' => 'decimal:2',
    ];

    public function getTotalCostAttribute()
    {
        return $this->quantity * $this->price_per_item;
    }

    public static function updateTotals()
    {
        $totalLoss = self::where('status', self::STATUS_LOSS)
            ->sum(DB::raw('quantity * price_per_item'));

        $totalSaved = self::where('status', self::STATUS_RETURNED)
            ->sum(DB::raw('quantity * price_per_item'));

        // Update all rows with the calculated totals
        DB::transaction(function () use ($totalLoss, $totalSaved) {
            self::query()->update([
                'total_loss' => $totalLoss,
                'total_saved' => $totalSaved,
                'updated_at' => now(),
            ]);
        });
    }
}