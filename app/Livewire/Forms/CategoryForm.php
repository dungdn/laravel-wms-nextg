<?php

namespace App\Livewire\Forms;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CategoryForm extends Form
{
    public ?int $categoryId = null;

    public string $code = '';

    public string $name = '';

    public ?string $description = null;

    public bool $is_active = true;

    /**
     * Validation rules for category form.
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'code')->ignore($this->categoryId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Populate form fields with existing category data.
     */
    public function setCategory(Category $category): void
    {
        $this->categoryId = $category->id;
        $this->code = $category->code;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = (bool) $category->is_active;
    }

    /**
     * Get validated data formatted for persistence.
     */
    public function data(): array
    {
        return [
            'code' => trim($this->code),
            'name' => trim($this->name),
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Reset form fields and validation errors.
     */
    public function resetForm(): void
    {
        $this->categoryId = null;
        $this->reset(['code', 'name', 'description']);
        $this->is_active = true;
        $this->resetValidation();
    }
}
