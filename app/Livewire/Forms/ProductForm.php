<?php

namespace App\Livewire\Forms;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProductForm extends Form
{
    public ?int $productId = null;

    public ?int $supplier_id = null;

    public ?int $category_id = null;

    public string $sku = '';

    public string $name = '';

    public ?string $description = null;

    public string $unit = 'pcs';

    public int|float|string $cost_price = 0;

    public int|float|string $selling_price = 0;

    public bool $is_active = true;

    /**
     * Validation rules for product form.
     */
    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($this->productId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:50'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Populate form fields with existing product data.
     */
    public function setProduct(Product $product): void
    {
        $this->productId = $product->id;
        $this->supplier_id = $product->supplier_id;
        $this->category_id = $product->category_id;
        $this->sku = $product->sku;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->unit = $product->unit ?? 'pcs';
        $this->cost_price = (float) $product->cost_price;
        $this->selling_price = (float) $product->selling_price;
        $this->is_active = (bool) $product->is_active;
    }

    /**
     * Get validated data formatted for persistence.
     */
    public function data(): array
    {
        return [
            'supplier_id' => $this->supplier_id ?: null,
            'category_id' => $this->category_id ?: null,
            'sku' => trim($this->sku),
            'name' => trim($this->name),
            'description' => $this->description ?: null,
            'unit' => trim($this->unit) ?: 'pcs',
            'cost_price' => $this->cost_price ?: 0,
            'selling_price' => $this->selling_price ?: 0,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Reset form fields and validation errors.
     */
    public function resetForm(): void
    {
        $this->productId = null;
        $this->supplier_id = null;
        $this->category_id = null;
        $this->reset(['sku', 'name', 'description']);
        $this->unit = 'pcs';
        $this->cost_price = 0;
        $this->selling_price = 0;
        $this->is_active = true;
        $this->resetValidation();
    }
}

