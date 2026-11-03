<?php

namespace App\Repositories\Contracts;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithSearch(
        ?string $search = null,
        int $perPage = 15
    );
}
