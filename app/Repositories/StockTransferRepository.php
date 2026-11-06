<?php

namespace App\Repositories;

use App\Models\StockTransfer;
use App\Repositories\Contracts\StockTransferRepositoryInterface;

class StockTransferRepository extends BaseRepository implements StockTransferRepositoryInterface
{
    public function __construct(StockTransfer $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $fromWarehouseId = null,
        ?int $toWarehouseId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->query()
            ->with(['fromWarehouse', 'toWarehouse', 'items.product', 'creator'])
            ->when($fromWarehouseId, function ($query) use ($fromWarehouseId) {
                $query->where('from_warehouse_id', $fromWarehouseId);
            })
            ->when($toWarehouseId, function ($query) use ($toWarehouseId) {
                $query->where('to_warehouse_id', $toWarehouseId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('reference_no', 'like', "%{$search}%");
            })
            ->latest('transfer_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findWithDetails(int $id)
    {
        return $this->query()
            ->with(['fromWarehouse', 'toWarehouse', 'items.product', 'creator'])
            ->find($id);
    }

    public function generateReferenceNo(): string
    {
        $prefix = 'TR-'.date('Ymd').'-';
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
