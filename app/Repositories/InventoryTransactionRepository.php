<?php

namespace App\Repositories;

use App\Models\InventoryTransaction;
use App\Repositories\Contracts\InventoryTransactionRepositoryInterface;

class InventoryTransactionRepository extends BaseRepository implements InventoryTransactionRepositoryInterface
{
    public function __construct(InventoryTransaction $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $productId = null,
        ?string $transactionType = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        int $perPage = 15
    ) {
        return $this->query()
            ->with(['warehouse', 'product', 'creator'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($productId, function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->when($transactionType, function ($query) use ($transactionType) {
                $query->where('transaction_type', $transactionType);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function getTransactionTypes(): array
    {
        return [
            'IN' => 'Stock In',
            'OUT' => 'Stock Out',
            'TRANSFER_IN' => 'Transfer In',
            'TRANSFER_OUT' => 'Transfer Out',
            'ADJUSTMENT' => 'Adjustment',
        ];
    }
}
