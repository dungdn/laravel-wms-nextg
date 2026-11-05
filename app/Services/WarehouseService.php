<?php

namespace App\Services;

use App\Repositories\Contracts\WarehouseRepositoryInterface;

class WarehouseService extends BaseService
{
    public function __construct(
        WarehouseRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function paginateWithSearch(
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->repository->paginateWithSearch($search, $perPage);
    }
}
