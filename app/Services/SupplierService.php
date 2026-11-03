<?php

namespace App\Services;

use App\Repositories\Contracts\SupplierRepositoryInterface;

class SupplierService extends BaseService
{
    public function __construct(
        SupplierRepositoryInterface $repository
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
