<?php

namespace App\Repositories;

use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;

class StockAdjustmentRepository extends BaseRepository implements StockAdjustmentRepositoryInterface
{
    public function __construct(StockAdjustment $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $reason = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->query()
            ->with(['warehouse', 'items.product', 'creator'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($reason, function ($query) use ($reason) {
                $query->where('reason', $reason);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('reference_no', 'like', "%{$search}%");
            })
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findWithDetails(int $id)
    {
        return $this->query()
            ->with(['warehouse', 'items.product', 'creator'])
            ->find($id);
    }

    public function generateReferenceNo(): string
    {
        $prefix = 'ADJ-'.date('Ymd').'-';
        $latest = $this->query()
            ->where('reference_no', 'like', "{$prefix}%")
            ->latest('id')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->reference_no, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix.$nextNumber;
    }
}
