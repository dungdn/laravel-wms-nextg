<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\InventoryTransactionRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ReportService
{
    public function __construct(
        protected InventoryTransactionRepositoryInterface $transactionRepository,
        protected InventoryRepositoryInterface $inventoryRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getAuditTrail(
        ?int $warehouseId = null,
        ?int $productId = null,
        ?string $transactionType = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        int $perPage = 15
    ) {
        return $this->transactionRepository->paginateWithFilters(
            $warehouseId,
            $productId,
            $transactionType,
            $fromDate,
            $toDate,
            $perPage
        );
    }

    public function getTransactionTypes(): array
    {
        return $this->transactionRepository->getTransactionTypes();
    }

    public function getExecutiveMetrics(?int $warehouseId = null): array
    {
        $valuation = $this->inventoryRepository->getValuationMetrics($warehouseId);
        $totalProducts = $this->productRepository->getAll()->count();

        return [
            'total_products' => $totalProducts,
            'total_units' => $valuation['total_units'],
            'total_valuation' => $valuation['total_valuation'],
            'low_stock_count' => $valuation['low_stock_count'],
            'out_of_stock_count' => $valuation['out_of_stock_count'],
        ];
    }

    public function getWarehouseBreakdown(): array
    {
        return $this->inventoryRepository->getWarehouseStockSummary();
    }
}
