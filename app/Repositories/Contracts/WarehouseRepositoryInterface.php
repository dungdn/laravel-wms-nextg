<?php

namespace App\Repositories\Contracts;

interface WarehouseRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithSearch(
        ?string $search = null,
        int $perPage = 15
    );
}
