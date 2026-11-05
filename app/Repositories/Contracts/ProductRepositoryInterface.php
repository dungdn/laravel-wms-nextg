<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithSearch(
        ?string $search = null,
        int $perPage = 15
    );
}
