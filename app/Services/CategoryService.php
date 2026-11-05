<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryService extends BaseService
{
    public function __construct(
        CategoryRepositoryInterface $repository
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
