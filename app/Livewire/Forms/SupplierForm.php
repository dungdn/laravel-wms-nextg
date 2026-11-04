<?php

namespace App\Livewire\Forms;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Form;

class SupplierForm extends Form
{
    public ?int $supplierId = null;

    public string $code = '';

    public string $name = '';

    public ?string $contact_name = null;

    public ?string $phone = null;

    public ?string $email = null;

    public ?string $address = null;

    public bool $is_active = true;

    /**
     * Validation rules for supplier form.
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->ignore($this->supplierId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Populate form fields with existing supplier data.
     */
    public function setSupplier(Supplier $supplier): void
    {
        $this->supplierId = $supplier->id;
        $this->code = $supplier->code;
        $this->name = $supplier->name;
        $this->contact_name = $supplier->contact_name;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->is_active = (bool) $supplier->is_active;
    }

    /**
     * Get validated data formatted for persistence.
     */
    public function data(): array
    {
        return [
            'code' => trim($this->code),
            'name' => trim($this->name),
            'contact_name' => $this->contact_name ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Reset form fields and validation errors.
     */
    public function resetForm(): void
    {
        $this->supplierId = null;
        $this->reset(['code', 'name', 'contact_name', 'phone', 'email', 'address']);
        $this->is_active = true;
        $this->resetValidation();
    }
}
