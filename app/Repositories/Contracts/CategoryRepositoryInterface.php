<?php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithSearch(
        ?string $search = null,
        int $perPage = 15
    );
}
