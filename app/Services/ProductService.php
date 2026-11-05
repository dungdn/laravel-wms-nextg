<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductService extends BaseService
{
    public function __construct(
        ProductRepositoryInterface $repository
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
