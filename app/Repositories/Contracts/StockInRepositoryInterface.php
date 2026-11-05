<?php

namespace App\Repositories\Contracts;

interface StockInRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $supplierId = null,
        ?string $search = null,
        int $perPage = 15
    );

    public function generateReferenceNo(): string;
}
