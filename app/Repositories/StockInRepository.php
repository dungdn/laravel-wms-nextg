<?php

namespace App\Repositories;

use App\Models\StockIn;
use App\Repositories\Contracts\StockInRepositoryInterface;

class StockInRepository extends BaseRepository implements StockInRepositoryInterface
{
    public function __construct(StockIn $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $supplierId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->query()
            ->with(['supplier', 'warehouse', 'items.product', 'creator'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($supplierId, function ($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('reference_no', 'like', "%{$search}%");
            })
            ->latest('transaction_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function generateReferenceNo(): string
    {
        $prefix = 'IN-'.date('Ymd').'-';
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
