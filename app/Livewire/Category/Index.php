<?php

namespace App\Livewire\Category;

use App\Livewire\Forms\CategoryForm;
use App\Services\CategoryService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public CategoryForm $form;

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
        $this->dispatch('open-modal', 'category-form-modal');
    }

    public function edit(int $id, CategoryService $service): void
    {
        $category = $service->findOrFail($id);
        $this->form->setCategory($category);
        $this->isEditing = true;
        $this->dispatch('open-modal', 'category-form-modal');
    }

    public function save(CategoryService $service): void
    {
        $this->form->validate();

        if ($this->isEditing && $this->form->categoryId) {
            $service->update($this->form->categoryId, $this->form->data());
            session()->flash('success', 'Category updated successfully.');
        } else {
            $service->create($this->form->data());
            session()->flash('success', 'Category created successfully.');
        }

        $this->dispatch('close-modal', 'category-form-modal');
        $this->form->resetForm();
    }

    public function delete(int $id, CategoryService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'Category deleted successfully.');
    }

    public function render(CategoryService $service)
    {
        $categories = $service->paginateWithSearch($this->search, $this->perPage);

        return view('livewire.category.index', [
            'categories' => $categories,
        ]);
    }
}
