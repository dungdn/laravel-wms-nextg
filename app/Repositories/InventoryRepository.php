<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->query()
            ->with(['warehouse', 'product'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findByWarehouseAndProduct(int $warehouseId, int $productId)
    {
        return $this->query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->query()->updateOrCreate($attributes, $values);
    }
}
