<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Flash Notification --}}
        @if (session()->has('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Products
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Manage your warehouse products and inventory items
                </p>
            </div>

            <x-ui.button type="button" wire:click="create">
                Add Product
            </x-ui.button>
        </div>

        {{-- Search --}}
        <x-ui.card class="mb-6">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label
                        for="product-search"
                        class="sr-only"
                    >
                        Search products
                    </label>

                    <input
                        id="product-search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by SKU or product name..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>
        </x-ui.card>

        {{-- Product Table --}}
        <x-ui.table>

            <x-slot:head>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        SKU
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Name
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Category
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Supplier
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Unit
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Cost Price
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Selling Price
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Status
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Actions
                    </th>
                </tr>
            </x-slot>

            <x-slot:body>

                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $product->sku }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $product->name }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $product->category?->name ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $product->supplier?->name ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $product->unit }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-700">
                            ${{ number_format($product->cost_price, 2) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-700">
                            ${{ number_format($product->selling_price, 2) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($product->is_active)
                                <x-ui.badge type="success">
                                    Active
                                </x-ui.badge>
                            @else
                                <x-ui.badge type="danger">
                                    Inactive
                                </x-ui.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-2">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                wire:click="edit({{ $product->id }})"
                                class="text-xs px-2.5 py-1.5"
                            >
                                Edit
                            </x-ui.button>

                            <x-ui.button
                                type="button"
                                variant="danger"
                                wire:click="delete({{ $product->id }})"
                                wire:confirm="Are you sure you want to delete this product?"
                                class="text-xs px-2.5 py-1.5"
                            >
                                Delete
                            </x-ui.button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="9"
                            class="px-6 py-12 text-center text-sm text-gray-500"
                        >
                            No products found.
                        </td>
                    </tr>
                @endforelse

            </x-slot>

        </x-ui.table>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $products->links() }}
        </div>

    </div>

    {{-- Product Create/Edit Modal --}}
    <x-modal name="product-form-modal" maxWidth="2xl" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $isEditing ? 'Edit Product' : 'Add New Product' }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ $isEditing ? 'Update product information below.' : 'Fill in the information below to create a new product.' }}
            </p>

            <div class="mt-6 space-y-4">
                {{-- SKU and Name --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="product-sku" value="SKU *" />
                        <x-text-input
                            id="product-sku"
                            type="text"
                            class="mt-1 block w-full uppercase"
                            wire:model="form.sku"
                            placeholder="e.g. IP15PM-256-BLK"
                            required
                        />
                        <x-input-error :messages="$errors->get('form.sku')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="product-name" value="Product Name *" />
                        <x-text-input
                            id="product-name"
                            type="text"
                            class="mt-1 block w-full"
                            wire:model="form.name"
                            placeholder="e.g. iPhone 15 Pro Max 256GB"
                            required
                        />
                        <x-input-error :messages="$errors->get('form.name')" class="mt-1" />
                    </div>
                </div>

                {{-- Category and Supplier Selects --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="product-category" value="Category" />
                        <select
                            id="product-category"
                            wire:model="form.category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }} ({{ $category->code }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('form.category_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="product-supplier" value="Supplier" />
                        <select
                            id="product-supplier"
                            wire:model="form.supplier_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">
                                    {{ $supplier->name }} ({{ $supplier->code }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('form.supplier_id')" class="mt-1" />
                    </div>
                </div>

                {{-- Unit, Cost Price, Selling Price --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="product-unit" value="Unit *" />
                        <x-text-input
                            id="product-unit"
                            type="text"
                            class="mt-1 block w-full"
                            wire:model="form.unit"
                            placeholder="e.g. pcs, box"
                            required
                        />
                        <x-input-error :messages="$errors->get('form.unit')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="product-cost-price" value="Cost Price *" />
                        <x-text-input
                            id="product-cost-price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                            wire:model="form.cost_price"
                            placeholder="0.00"
                            required
                        />
                        <x-input-error :messages="$errors->get('form.cost_price')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="product-selling-price" value="Selling Price *" />
                        <x-text-input
                            id="product-selling-price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                            wire:model="form.selling_price"
                            placeholder="0.00"
                            required
                        />
                        <x-input-error :messages="$errors->get('form.selling_price')" class="mt-1" />
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <x-input-label for="product-description" value="Description" />
                    <textarea
                        id="product-description"
                        wire:model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        placeholder="Detailed product description..."
                    ></textarea>
                    <x-input-error :messages="$errors->get('form.description')" class="mt-1" />
                </div>

                {{-- Is Active Checkbox --}}
                <div class="flex items-center pt-2">
                    <label for="product-active" class="inline-flex items-center cursor-pointer">
                        <input
                            id="product-active"
                            type="checkbox"
                            wire:model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        >
                        <span class="ms-2 text-sm text-gray-700">Active Status</span>
                    </label>
                </div>
            </div>

            {{-- Modal Actions --}}
            <div class="mt-6 flex justify-end gap-3">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    x-on:click="$dispatch('close-modal', 'product-form-modal')"
                >
                    Cancel
                </x-ui.button>

                <x-ui.button type="submit" variant="primary">
                    {{ $isEditing ? 'Save Changes' : 'Create Product' }}
                </x-ui.button>
            </div>
        </form>
    </x-modal>
</div>

