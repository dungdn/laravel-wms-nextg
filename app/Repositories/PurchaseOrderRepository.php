<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use App\Repositories\Contracts\PurchaseOrderRepositoryInterface;

class PurchaseOrderRepository extends BaseRepository implements PurchaseOrderRepositoryInterface
{
    public function __construct(PurchaseOrder $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $supplierId = null,
        ?string $status = null,
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
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('po_number', 'like', "%{$search}%");
            })
            ->latest('order_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findWithDetails(int $id)
    {
        return $this->query()
            ->with(['supplier', 'warehouse', 'items.product', 'creator'])
            ->find($id);
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'PO-'.date('Ymd').'-';
        $latest = $this->query()
            ->where('po_number', 'like', "{$prefix}%")
            ->latest('id')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->po_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix.$nextNumber;
    }
}
