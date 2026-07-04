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

    public function paginate(
        ?string $search = null,
        int $perPage = 10
    ) {
        return $this->repository->paginate($search, $perPage);
    }
}