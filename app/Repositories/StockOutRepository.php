<?php

namespace App\Repositories;

use App\Models\StockOut;
use App\Repositories\Contracts\StockOutRepositoryInterface;

class StockOutRepository extends BaseRepository implements StockOutRepositoryInterface
{
    public function __construct(StockOut $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->query()
            ->with(['warehouse', 'items.product', 'creator'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('reference_no', 'like', "%{$search}%");
            })
            ->latest('transaction_date')
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
        $prefix = 'OUT-'.date('Ymd').'-';
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
