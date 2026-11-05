<?php

namespace App\Livewire\Warehouse;

use App\Livewire\Forms\WarehouseForm;
use App\Services\WarehouseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public WarehouseForm $form;

    public string $search = '';

    public int $perPage = 15;

    public bool $isEditing = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->form->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-modal', 'warehouse-form-modal');
    }

    public function edit(int $id, WarehouseService $service): void
    {
        $warehouse = $service->findOrFail($id);
        $this->form->setWarehouse($warehouse);
        $this->isEditing = true;
        $this->dispatch('open-modal', 'warehouse-form-modal');
    }

    public function save(WarehouseService $service): void
    {
        $this->form->validate();

        if ($this->isEditing && $this->form->warehouseId) {
            $service->update($this->form->warehouseId, $this->form->data());
            session()->flash('success', 'Warehouse updated successfully.');
        } else {
            $service->create($this->form->data());
            session()->flash('success', 'Warehouse created successfully.');
        }

        $this->dispatch('close-modal', 'warehouse-form-modal');
        $this->form->resetForm();
    }

    public function delete(int $id, WarehouseService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'Warehouse deleted successfully.');
    }

    public function render(WarehouseService $service)
    {
        $warehouses = $service->paginateWithSearch($this->search, $this->perPage);

        return view('livewire.warehouse.index', [
            'warehouses' => $warehouses,
        ]);
    }
}
