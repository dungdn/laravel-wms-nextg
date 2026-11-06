<?php

namespace App\Livewire\Report;

use App\Services\ProductService;
use App\Services\ReportService;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'audit';

    public ?int $warehouseFilter = null;

    public ?int $productFilter = null;

    public ?string $typeFilter = null;

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public int $perPage = 15;

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['audit', 'warehouse'], true) ? $tab : 'audit';
        $this->resetPage();
    }

    public function updatedWarehouseFilter(): void
    {
        $this->resetPage();
    }

    public function updatedProductFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFromDate(): void
    {
        $this->resetPage();
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->warehouseFilter = null;
        $this->productFilter = null;
        $this->typeFilter = null;
        $this->fromDate = null;
        $this->toDate = null;
        $this->resetPage();
    }

    public function render(
        ReportService $reportService,
        WarehouseService $warehouseService,
        ProductService $productService
    ) {
        $metrics = $reportService->getExecutiveMetrics($this->warehouseFilter);
        $transactionTypes = $reportService->getTransactionTypes();
        $warehouses = $warehouseService->getAll();
        $products = $productService->getAll();

        $auditTrail = $this->activeTab === 'audit'
            ? $reportService->getAuditTrail(
                $this->warehouseFilter,
                $this->productFilter,
                $this->typeFilter,
                $this->fromDate,
                $this->toDate,
                $this->perPage
            )
            : null;

        $warehouseBreakdown = $this->activeTab === 'warehouse'
            ? $reportService->getWarehouseBreakdown()
            : null;

        return view('livewire.report.index', [
            'metrics' => $metrics,
            'transactionTypes' => $transactionTypes,
            'warehouses' => $warehouses,
            'products' => $products,
            'auditTrail' => $auditTrail,
            'warehouseBreakdown' => $warehouseBreakdown,
        ]);
    }
}
