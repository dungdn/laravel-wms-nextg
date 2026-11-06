<?php

namespace App\Repositories\Contracts;

interface InventoryTransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $productId = null,
        ?string $transactionType = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        int $perPage = 15
    );

    public function getTransactionTypes(): array;
}
