<?php

namespace App\Livewire\PurchaseOrder;

use App\Services\ProductService;
use App\Services\PurchaseOrderService;
use App\Services\SupplierService;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $warehouseFilter = null;

    public ?int $supplierFilter = null;

    public ?string $statusFilter = null;

    public string $search = '';

    public int $perPage = 15;

    // Create Form State
    public string $po_number = '';

    public ?int $supplier_id = null;

    public ?int $warehouse_id = null;

    public string $order_date = '';

    public ?string $expected_delivery_date = null;

    public string $status = 'pending';

    public ?string $note = null;

    public array $items = [];

    // View Detail State
    public ?int $viewPurchaseOrderId = null;

    public function mount(): void
    {
        $this->order_date = now()->toDateString();
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

    public function updatedStatusFilter(): void
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
        // When product_id is selected, auto-fill unit_cost with product's cost_price
        if (str_ends_with($key, '.product_id') && ! empty($value)) {
            $parts = explode('.', $key);
            $index = (int) $parts[0];

            $product = app(ProductService::class)->find((int) $value);
            if ($product) {
                $this->items[$index]['unit_cost'] = (float) $product->cost_price;
            }
        }
    }

    public function create(PurchaseOrderService $service): void
    {
        $this->resetValidation();
        $this->po_number = $service->generateOrderNumber();
        $this->supplier_id = $this->supplierFilter ?: null;
        $this->warehouse_id = $this->warehouseFilter ?: null;
        $this->order_date = now()->toDateString();
        $this->expected_delivery_date = null;
        $this->status = 'pending';
        $this->note = null;
        $this->resetItemRows();

        $this->dispatch('open-modal', 'purchase-order-create-modal');
    }

    public function viewDetail(int $id): void
    {
        $this->viewPurchaseOrderId = $id;
        $this->dispatch('open-modal', 'purchase-order-detail-modal');
    }

    public function save(PurchaseOrderService $service): void
    {
        $this->validate([
            'po_number' => ['nullable', 'string', 'max:50', 'unique:purchase_orders,po_number'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', 'string', 'in:pending,completed,cancelled'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $service->createPurchaseOrder([
            'po_number' => $this->po_number,
            'supplier_id' => $this->supplier_id,
            'warehouse_id' => $this->warehouse_id,
            'order_date' => $this->order_date,
            'expected_delivery_date' => $this->expected_delivery_date,
            'status' => $this->status,
            'note' => $this->note,
            'items' => $this->items,
        ]);

        session()->flash('success', 'Purchase Order created successfully.');
        $this->dispatch('close-modal', 'purchase-order-create-modal');
        $this->resetItemRows();
    }

    public function render(
        PurchaseOrderService $purchaseOrderService,
        WarehouseService $warehouseService,
        SupplierService $supplierService,
        ProductService $productService
    ) {
        $purchaseOrders = $purchaseOrderService->paginateWithFilters(
            $this->warehouseFilter,
            $this->supplierFilter,
            $this->statusFilter,
            $this->search,
            $this->perPage
        );

        $warehouses = $warehouseService->getAll();
        $suppliers = $supplierService->getAll();
        $products = $productService->getAll();

        $selectedPurchaseOrder = $this->viewPurchaseOrderId
            ? $purchaseOrderService->findWithDetails($this->viewPurchaseOrderId)
            : null;

        return view('livewire.purchase-order.index', [
            'purchaseOrders' => $purchaseOrders,
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
            'products' => $products,
            'selectedPurchaseOrder' => $selectedPurchaseOrder,
        ]);
    }
}
