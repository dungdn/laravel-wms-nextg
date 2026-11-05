<?php

namespace App\Livewire\Inventory;

use App\Livewire\Forms\InventoryForm;
use App\Services\InventoryService;
use App\Services\ProductService;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public InventoryForm $form;

    public ?int $warehouseFilter = null;

    public string $search = '';

    public int $perPage = 15;

    public bool $isEditing = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedWarehouseFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->form->resetForm();
        if ($this->warehouseFilter) {
            $this->form->warehouse_id = $this->warehouseFilter;
        }
        $this->isEditing = false;
        $this->dispatch('open-modal', 'inventory-form-modal');
    }

    public function edit(int $id, InventoryService $service): void
    {
        $inventory = $service->findOrFail($id);
        $this->form->setInventory($inventory);
        $this->isEditing = true;
        $this->dispatch('open-modal', 'inventory-form-modal');
    }

    public function save(InventoryService $service): void
    {
        $this->form->validate();

        if ($this->isEditing && $this->form->inventoryId) {
            $service->update($this->form->inventoryId, $this->form->data());
            session()->flash('success', 'Inventory record updated successfully.');
        } else {
            $service->updateOrCreate(
                [
                    'warehouse_id' => $this->form->warehouse_id,
                    'product_id' => $this->form->product_id,
                ],
                [
                    'quantity' => $this->form->quantity,
                ]
            );
            session()->flash('success', 'Inventory recorded successfully.');
        }

        $this->dispatch('close-modal', 'inventory-form-modal');
        $this->form->resetForm();
    }

    public function delete(int $id, InventoryService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'Inventory record deleted successfully.');
    }

    public function render(
        InventoryService $inventoryService,
        WarehouseService $warehouseService,
        ProductService $productService
    ) {
        $inventories = $inventoryService->paginateWithFilters(
            $this->warehouseFilter ?: null,
            $this->search,
            $this->perPage
        );

        $warehouses = $warehouseService->getAll();
        $products = $productService->getAll();

        return view('livewire.inventory.index', [
            'inventories' => $inventories,
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }
}
