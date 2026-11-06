<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOut extends Model
{
    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'transaction_date',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOutItem::class);
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }
}
