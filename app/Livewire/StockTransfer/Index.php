<?php

namespace App\Livewire\StockTransfer;

use App\Services\ProductService;
use App\Services\StockTransferService;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $fromWarehouseFilter = null;

    public ?int $toWarehouseFilter = null;

    public string $search = '';

    public int $perPage = 15;

    // Create Form State
    public string $reference_no = '';

    public ?int $from_warehouse_id = null;

    public ?int $to_warehouse_id = null;

    public string $transfer_date = '';

    public ?string $note = null;

    public array $items = [];

    // View Detail State
    public ?int $viewStockTransferId = null;

    public function mount(): void
    {
        $this->transfer_date = now()->toDateString();
        $this->resetItemRows();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFromWarehouseFilter(): void
    {
        $this->resetPage();
    }

    public function updatedToWarehouseFilter(): void
    {
        $this->resetPage();
    }

    public function resetItemRows(): void
    {
        $this->items = [
            [
                'product_id' => '',
                'quantity' => 1,
            ],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function create(StockTransferService $service): void
    {
        $this->resetValidation();
        $this->reference_no = $service->generateReferenceNo();
        $this->from_warehouse_id = $this->fromWarehouseFilter ?: null;
        $this->to_warehouse_id = $this->toWarehouseFilter ?: null;
        $this->transfer_date = now()->toDateString();
        $this->note = null;
        $this->resetItemRows();

        $this->dispatch('open-modal', 'stock-transfer-create-modal');
    }

    public function viewDetail(int $id): void
    {
        $this->viewStockTransferId = $id;
        $this->dispatch('open-modal', 'stock-transfer-detail-modal');
    }

    public function save(StockTransferService $service): void
    {
        $this->validate([
            'reference_no' => ['nullable', 'string', 'max:50', 'unique:stock_transfers,reference_no'],
            'from_warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'integer', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'transfer_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'to_warehouse_id.different' => 'The destination warehouse must be different from the source warehouse.',
        ]);

        $service->createStockTransfer([
            'reference_no' => $this->reference_no,
            'from_warehouse_id' => $this->from_warehouse_id,
            'to_warehouse_id' => $this->to_warehouse_id,
            'transfer_date' => $this->transfer_date,
            'note' => $this->note,
            'items' => $this->items,
        ]);

        session()->flash('success', 'Stock transfer completed and inventory updated across both warehouses.');
        $this->dispatch('close-modal', 'stock-transfer-create-modal');
        $this->resetItemRows();
    }

    public function render(
        StockTransferService $stockTransferService,
        WarehouseService $warehouseService,
        ProductService $productService
    ) {
        $stockTransfers = $stockTransferService->paginateWithFilters(
            $this->fromWarehouseFilter,
            $this->toWarehouseFilter,
            $this->search,
            $this->perPage
        );

        $warehouses = $warehouseService->getAll();
        $products = $productService->getAll();

        $selectedStockTransfer = $this->viewStockTransferId
            ? $stockTransferService->findWithDetails($this->viewStockTransferId)
            : null;

        return view('livewire.stock-transfer.index', [
            'stockTransfers' => $stockTransfers,
            'warehouses' => $warehouses,
            'products' => $products,
            'selectedStockTransfer' => $selectedStockTransfer,
        ]);
    }
}
