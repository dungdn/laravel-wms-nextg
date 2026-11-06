<?php

namespace App\Livewire\StockIn;

use App\Models\Product;
use App\Models\StockIn;
use App\Services\ProductService;
use App\Services\StockInService;
use App\Services\SupplierService;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $warehouseFilter = null;

    public ?int $supplierFilter = null;

    public string $search = '';

    public int $perPage = 15;

    // Create Form State
    public string $reference_no = '';

    public ?int $supplier_id = null;

    public ?int $warehouse_id = null;

    public string $transaction_date = '';

    public ?string $note = null;

    public array $items = [];

    // View Detail State
    public ?int $viewStockInId = null;

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

    public function updatedSupplierFilter(): void
    {
        $this->resetPage();
    }

    public function resetItemRows(): void
    {
        $this->items = [
            [
                'product_id' => '',
                'quantity' => 1,
                'unit_cost' => 0,
            ],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_cost' => 0,
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
        // When product_id is selected, auto-fill unit_cost with product's default cost_price
        if (str_ends_with($key, '.product_id') && ! empty($value)) {
            $parts = explode('.', $key);
            $index = (int) $parts[0];

            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_cost'] = (float) $product->cost_price;
            }
        }
    }

    public function create(StockInService $service): void
    {
        $this->resetValidation();
        $this->reference_no = $service->generateReferenceNo();
        $this->supplier_id = null;
        $this->warehouse_id = $this->warehouseFilter ?: null;
        $this->transaction_date = now()->toDateString();
        $this->note = null;
        $this->resetItemRows();

        $this->dispatch('open-modal', 'stock-in-create-modal');
    }

    public function viewDetail(int $id): void
    {
        $this->viewStockInId = $id;
        $this->dispatch('open-modal', 'stock-in-detail-modal');
    }

    public function save(StockInService $service): void
    {
        $this->validate([
            'reference_no' => ['nullable', 'string', 'max:50', 'unique:stock_ins,reference_no'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'transaction_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $service->createStockIn([
            'reference_no' => $this->reference_no,
            'supplier_id' => $this->supplier_id,
            'warehouse_id' => $this->warehouse_id,
            'transaction_date' => $this->transaction_date,
            'note' => $this->note,
            'items' => $this->items,
        ]);

        session()->flash('success', 'Stock In voucher created and inventory successfully updated.');
        $this->dispatch('close-modal', 'stock-in-create-modal');
        $this->resetItemRows();
    }

    public function render(
        StockInService $stockInService,
        WarehouseService $warehouseService,
        SupplierService $supplierService,
        ProductService $productService
    ) {
        $stockIns = $stockInService->paginateWithFilters(
            $this->warehouseFilter,
            $this->supplierFilter,
            $this->search,
            $this->perPage
        );

        $warehouses = $warehouseService->getAll();
        $suppliers = $supplierService->getAll();
        $products = $productService->getAll();

        $selectedStockIn = $this->viewStockInId
            ? StockIn::with(['supplier', 'warehouse', 'items.product', 'creator'])->find($this->viewStockInId)
            : null;

        return view('livewire.stock-in.index', [
            'stockIns' => $stockIns,
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
            'products' => $products,
            'selectedStockIn' => $selectedStockIn,
        ]);
    }
}
