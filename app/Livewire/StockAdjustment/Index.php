<?php

namespace App\Livewire\StockAdjustment;

use App\Services\InventoryService;
use App\Services\ProductService;
use App\Services\StockAdjustmentService;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $warehouseFilter = null;

    public ?string $reasonFilter = null;

    public string $search = '';

    public int $perPage = 15;

    // Create Form State
    public string $reference_no = '';

    public ?int $warehouse_id = null;

    public string $adjustment_date = '';

    public string $reason = 'Cycle Count';

    public ?string $note = null;

    public array $items = [];

    // View Detail State
    public ?int $viewStockAdjustmentId = null;

    public function mount(): void
    {
        $this->adjustment_date = now()->toDateString();
        $this->resetItemRows();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedWarehouseFilter(): void
    {
        $this->resetPage();
    }

    public function updatedReasonFilter(): void
    {
        $this->resetPage();
    }

    public function resetItemRows(): void
    {
        $this->items = [
            [
                'product_id' => '',
                'current_quantity' => 0,
                'adjusted_quantity' => 0,
                'note' => '',
            ],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'current_quantity' => 0,
            'adjusted_quantity' => 0,
            'note' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function updatedWarehouseId(): void
    {
        if ($this->warehouse_id) {
            foreach ($this->items as $index => $item) {
                if (! empty($item['product_id'])) {
                    $inv = app(InventoryService::class)->findByWarehouseAndProduct($this->warehouse_id, (int) $item['product_id']);
                    $this->items[$index]['current_quantity'] = $inv ? (int) $inv->quantity : 0;
                }
            }
        }
    }

    public function updatedItems($value, $key): void
    {
        // When product_id is selected, auto-fill current_quantity and default adjusted_quantity
        if (str_ends_with($key, '.product_id') && ! empty($value)) {
            $parts = explode('.', $key);
            $index = (int) $parts[0];

            $currentQty = 0;
            if ($this->warehouse_id) {
                $inv = app(InventoryService::class)->findByWarehouseAndProduct($this->warehouse_id, (int) $value);
                $currentQty = $inv ? (int) $inv->quantity : 0;
            }

            $this->items[$index]['current_quantity'] = $currentQty;
            $this->items[$index]['adjusted_quantity'] = $currentQty;
        }
    }

    public function create(StockAdjustmentService $service): void
    {
        $this->resetValidation();
        $this->reference_no = $service->generateReferenceNo();
        $this->warehouse_id = $this->warehouseFilter ?: null;
        $this->adjustment_date = now()->toDateString();
        $this->reason = 'Cycle Count';
        $this->note = null;
        $this->resetItemRows();

        $this->dispatch('open-modal', 'stock-adjustment-create-modal');
    }

    public function viewDetail(int $id): void
    {
        $this->viewStockAdjustmentId = $id;
        $this->dispatch('open-modal', 'stock-adjustment-detail-modal');
    }

    public function save(StockAdjustmentService $service): void
    {
        $this->validate([
            'reference_no' => ['nullable', 'string', 'max:50', 'unique:stock_adjustments,reference_no'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'adjustment_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.adjusted_quantity' => ['required', 'integer', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $service->createStockAdjustment([
            'reference_no' => $this->reference_no,
            'warehouse_id' => $this->warehouse_id,
            'adjustment_date' => $this->adjustment_date,
            'reason' => $this->reason,
            'note' => $this->note,
            'items' => $this->items,
        ]);

        session()->flash('success', 'Stock adjustment completed and inventory reconciled successfully.');
        $this->dispatch('close-modal', 'stock-adjustment-create-modal');
        $this->resetItemRows();
    }

    public function render(
        StockAdjustmentService $stockAdjustmentService,
        WarehouseService $warehouseService,
        ProductService $productService
    ) {
        $stockAdjustments = $stockAdjustmentService->paginateWithFilters(
            $this->warehouseFilter,
            $this->reasonFilter,
            $this->search,
            $this->perPage
        );

        $warehouses = $warehouseService->getAll();
        $products = $productService->getAll();

        $selectedStockAdjustment = $this->viewStockAdjustmentId
            ? $stockAdjustmentService->findWithDetails($this->viewStockAdjustmentId)
            : null;

        return view('livewire.stock-adjustment.index', [
            'stockAdjustments' => $stockAdjustments,
            'warehouses' => $warehouses,
            'products' => $products,
            'selectedStockAdjustment' => $selectedStockAdjustment,
        ]);
    }
}
