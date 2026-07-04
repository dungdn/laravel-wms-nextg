<?php

namespace App\Repositories\Contracts;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function paginate(
        ?string $search = null,
        int $perPage = 15
    );
}
