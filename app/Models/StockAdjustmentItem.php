<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'current_quantity',
        'adjusted_quantity',
        'quantity_change',
        'note',
    ];

    protected $casts = [
        'current_quantity' => 'integer',
        'adjusted_quantity' => 'integer',
        'quantity_change' => 'integer',
    ];

    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
