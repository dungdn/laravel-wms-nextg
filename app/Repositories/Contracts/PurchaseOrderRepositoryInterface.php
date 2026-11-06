<?php

namespace App\Repositories\Contracts;

interface PurchaseOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $supplierId = null,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 15
    );

    public function findWithDetails(int $id);

    public function generateOrderNumber(): string;
}
