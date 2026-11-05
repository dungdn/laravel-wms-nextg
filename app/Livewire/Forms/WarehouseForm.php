<?php

namespace App\Livewire\Forms;

use App\Models\Warehouse;
use Illuminate\Validation\Rule;
use Livewire\Form;

class WarehouseForm extends Form
{
    public ?int $warehouseId = null;

    public string $code = '';

    public string $name = '';

    public ?string $address = null;

    public bool $is_active = true;

    /**
     * Validation rules for warehouse form.
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->ignore($this->warehouseId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Populate form fields with existing warehouse data.
     */
    public function setWarehouse(Warehouse $warehouse): void
    {
        $this->warehouseId = $warehouse->id;
        $this->code = $warehouse->code;
        $this->name = $warehouse->name;
        $this->address = $warehouse->address;
        $this->is_active = (bool) $warehouse->is_active;
    }

    /**
     * Get validated data formatted for persistence.
     */
    public function data(): array
    {
        return [
            'code' => trim($this->code),
            'name' => trim($this->name),
            'address' => $this->address ?: null,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Reset form fields and validation errors.
     */
    public function resetForm(): void
    {
        $this->warehouseId = null;
        $this->reset(['code', 'name', 'address']);
        $this->is_active = true;
        $this->resetValidation();
    }
}
