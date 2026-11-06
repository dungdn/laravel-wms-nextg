<?php

namespace App\Repositories\Contracts;

interface StockAdjustmentRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $reason = null,
        ?string $search = null,
        int $perPage = 15
    );

    public function findWithDetails(int $id);

    public function generateReferenceNo(): string;
}
