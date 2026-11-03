<?php

namespace App\Livewire\Supplier;

use App\Services\SupplierService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $suppliers = app(SupplierService::class)
            ->paginateWithSearch($this->search, $this->perPage);

        return view('livewire.supplier.index', [
            'suppliers' => $suppliers,
        ]);
    }
}
