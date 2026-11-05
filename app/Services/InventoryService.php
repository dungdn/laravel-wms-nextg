<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryService extends BaseService
{
    public function __construct(
        InventoryRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->repository->paginateWithFilters($warehouseId, $search, $perPage);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->repository->updateOrCreate($attributes, $values);
    }
}
