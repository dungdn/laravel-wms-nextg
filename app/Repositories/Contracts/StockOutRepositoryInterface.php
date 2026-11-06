<?php

namespace App\Repositories\Contracts;

interface StockOutRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $search = null,
        int $perPage = 15
    );

    public function findWithDetails(int $id);

    public function generateReferenceNo(): string;
}
