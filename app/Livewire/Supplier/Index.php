<?php

namespace App\Livewire\Supplier;

use App\Livewire\Forms\SupplierForm;
use App\Services\SupplierService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public SupplierForm $form;

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
        $this->dispatch('open-modal', 'supplier-form-modal');
    }

    public function edit(int $id, SupplierService $service): void
    {
        $supplier = $service->findOrFail($id);
        $this->form->setSupplier($supplier);
        $this->isEditing = true;
        $this->dispatch('open-modal', 'supplier-form-modal');
    }

    public function save(SupplierService $service): void
    {
        $this->form->validate();

        if ($this->isEditing && $this->form->supplierId) {
            $service->update($this->form->supplierId, $this->form->data());
            session()->flash('success', 'Supplier updated successfully.');
        } else {
            $service->create($this->form->data());
            session()->flash('success', 'Supplier created successfully.');
        }

        $this->dispatch('close-modal', 'supplier-form-modal');
        $this->form->resetForm();
    }

    public function delete(int $id, SupplierService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'Supplier deleted successfully.');
    }

    public function render(SupplierService $service)
    {
        $suppliers = $service->paginateWithSearch($this->search, $this->perPage);

        return view('livewire.supplier.index', [
            'suppliers' => $suppliers,
        ]);
    }
}
