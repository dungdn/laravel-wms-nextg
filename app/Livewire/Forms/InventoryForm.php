<?php

namespace App\Livewire\Forms;

use App\Models\Inventory;
use Livewire\Form;

class InventoryForm extends Form
{
    public ?int $inventoryId = null;

    public ?int $warehouse_id = null;

    public ?int $product_id = null;

    public int $quantity = 0;

    /**
     * Validation rules for inventory form.
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Populate form fields with existing inventory data.
     */
    public function setInventory(Inventory $inventory): void
    {
        $this->inventoryId = $inventory->id;
        $this->warehouse_id = $inventory->warehouse_id;
        $this->product_id = $inventory->product_id;
        $this->quantity = (int) $inventory->quantity;
    }

    /**
     * Get validated data formatted for persistence.
     */
    public function data(): array
    {
        return [
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
        ];
    }

    /**
     * Reset form fields and validation errors.
     */
    public function resetForm(): void
    {
        $this->inventoryId = null;
        $this->warehouse_id = null;
        $this->product_id = null;
        $this->quantity = 0;
        $this->resetValidation();
    }
}

