<?php

namespace App\Livewire\Product;

use App\Livewire\Forms\ProductForm;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\SupplierService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ProductForm $form;

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
        $this->dispatch('open-modal', 'product-form-modal');
    }

    public function edit(int $id, ProductService $service): void
    {
        $product = $service->findOrFail($id);
        $this->form->setProduct($product);
        $this->isEditing = true;
        $this->dispatch('open-modal', 'product-form-modal');
    }

    public function save(ProductService $service): void
    {
        $this->form->validate();

        if ($this->isEditing && $this->form->productId) {
            $service->update($this->form->productId, $this->form->data());
            session()->flash('success', 'Product updated successfully.');
        } else {
            $service->create($this->form->data());
            session()->flash('success', 'Product created successfully.');
        }

        $this->dispatch('close-modal', 'product-form-modal');
        $this->form->resetForm();
    }

    public function delete(int $id, ProductService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'Product deleted successfully.');
    }

    public function render(
        ProductService $productService,
        CategoryService $categoryService,
        SupplierService $supplierService
    ) {
        $products = $productService->paginateWithSearch($this->search, $this->perPage);
        $categories = $categoryService->getAll();
        $suppliers = $supplierService->getAll();

        return view('livewire.product.index', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }
}
