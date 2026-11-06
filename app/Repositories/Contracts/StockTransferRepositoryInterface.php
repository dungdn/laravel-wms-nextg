<?php

namespace App\Repositories\Contracts;

interface StockTransferRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        ?int $fromWarehouseId = null,
        ?int $toWarehouseId = null,
        ?string $search = null,
        int $perPage = 15
    );

    public function findWithDetails(int $id);

    public function generateReferenceNo(): string;
}
