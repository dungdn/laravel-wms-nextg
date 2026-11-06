<?php

namespace App\Repositories\Contracts;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $search = null,
        int $perPage = 15
    );

    public function findByWarehouseAndProduct(int $warehouseId, int $productId);

    public function updateOrCreate(array $attributes, array $values);
}
