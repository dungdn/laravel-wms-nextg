<?php

namespace App\Livewire\StockOut;

use App\Services\ProductService;
use App\Services\StockOutService;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $warehouseFilter = null;

    public string $search = '';

    public int $perPage = 15;

    // Create Form State
    public string $reference_no = '';

    public ?int $warehouse_id = null;

    public string $transaction_date = '';

    public ?string $note = null;

    public array $items = [];

    // View Detail State
    public ?int $viewStockOutId = null;

    public function mount(): void
    {
        $this->transaction_date = now()->toDateString();
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

    public function resetItemRows(): void
    {
        $this->items = [
            [
                'product_id' => '',
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function updatedItems($value, $key): void
    {
        // When product_id is selected, auto-fill unit_price with product's selling_price
        if (str_ends_with($key, '.product_id') && ! empty($value)) {
            $parts = explode('.', $key);
            $index = (int) $parts[0];

            $product = app(ProductService::class)->find((int) $value);
            if ($product) {
                $this->items[$index]['unit_price'] = (float) $product->selling_price;
            }
        }
    }

    public function create(StockOutService $service): void
    {
        $this->resetValidation();
        $this->reference_no = $service->generateReferenceNo();
        $this->warehouse_id = $this->warehouseFilter ?: null;
        $this->transaction_date = now()->toDateString();
        $this->note = null;
        $this->resetItemRows();

        $this->dispatch('open-modal', 'stock-out-create-modal');
    }

    public function viewDetail(int $id): void
    {
        $this->viewStockOutId = $id;
        $this->dispatch('open-modal', 'stock-out-detail-modal');
    }

    public function save(StockOutService $service): void
    {
        $this->validate([
            'reference_no' => ['nullable', 'string', 'max:50', 'unique:stock_outs,reference_no'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'transaction_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $service->createStockOut([
            'reference_no' => $this->reference_no,
            'warehouse_id' => $this->warehouse_id,
            'transaction_date' => $this->transaction_date,
            'note' => $this->note,
            'items' => $this->items,
        ]);

        session()->flash('success', 'Stock Out voucher created and inventory successfully updated.');
        $this->dispatch('close-modal', 'stock-out-create-modal');
        $this->resetItemRows();
    }

    public function render(
        StockOutService $stockOutService,
        WarehouseService $warehouseService,
        ProductService $productService
    ) {
        $stockOuts = $stockOutService->paginateWithFilters(
            $this->warehouseFilter,
            $this->search,
            $this->perPage
        );

        $warehouses = $warehouseService->getAll();
        $products = $productService->getAll();

        $selectedStockOut = $this->viewStockOutId
            ? $stockOutService->findWithDetails($this->viewStockOutId)
            : null;

        return view('livewire.stock-out.index', [
            'stockOuts' => $stockOuts,
            'warehouses' => $warehouses,
            'products' => $products,
            'selectedStockOut' => $selectedStockOut,
        ]);
    }
}
