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
                    Inventory
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Track and manage stock levels across all warehouse facilities
                </p>
            </div>

            <x-ui.button type="button" wire:click="create">
                Adjust Stock
            </x-ui.button>
        </div>

        {{-- Filters & Search --}}
        <x-ui.card class="mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                {{-- Warehouse Filter --}}
                <div>
                    <label for="warehouse-filter" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Filter by Warehouse
                    </label>
                    <select
                        id="warehouse-filter"
                        wire:model.live="warehouseFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="">All Warehouses</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">
                                {{ $warehouse->name }} ({{ $warehouse->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Product Search --}}
                <div class="sm:col-span-2">
                    <label for="inventory-search" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Search Products
                    </label>
                    <input
                        id="inventory-search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by product SKU or name..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                </div>
            </div>
        </x-ui.card>

        {{-- Inventory Table --}}
        <x-ui.table>

            <x-slot:head>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Warehouse
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Product SKU
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Product Name
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Unit
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Quantity
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

                @forelse ($inventories as $inventory)
                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $inventory->warehouse?->name ?? '-' }}
                            <span class="text-xs text-gray-500 block">
                                {{ $inventory->warehouse?->code }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
                            {{ $inventory->product?->sku ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $inventory->product?->name ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $inventory->product?->unit ?? 'pcs' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ number_format($inventory->quantity) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($inventory->quantity > 10)
                                <x-ui.badge type="success">
                                    In Stock
                                </x-ui.badge>
                            @elseif ($inventory->quantity > 0)
                                <x-ui.badge type="warning">
                                    Low Stock
                                </x-ui.badge>
                            @else
                                <x-ui.badge type="danger">
                                    Out of Stock
                                </x-ui.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-2">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                wire:click="edit({{ $inventory->id }})"
                                class="text-xs px-2.5 py-1.5"
                            >
                                Edit
                            </x-ui.button>

                            <x-ui.button
                                type="button"
                                variant="danger"
                                wire:click="delete({{ $inventory->id }})"
                                wire:confirm="Are you sure you want to delete this inventory record?"
                                class="text-xs px-2.5 py-1.5"
                            >
                                Delete
                            </x-ui.button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="7"
                            class="px-6 py-12 text-center text-sm text-gray-500"
                        >
                            No inventory records found.
                        </td>
                    </tr>
                @endforelse

            </x-slot>

        </x-ui.table>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $inventories->links() }}
        </div>

    </div>

    {{-- Inventory Create/Edit Modal --}}
    <x-modal name="inventory-form-modal" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $isEditing ? 'Edit Stock Level' : 'Set Stock Level' }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ $isEditing ? 'Update the quantity of this product in the selected warehouse.' : 'Set initial or adjusted stock quantity for a product in a warehouse.' }}
            </p>

            <div class="mt-6 space-y-4">
                {{-- Warehouse Select --}}
                <div>
                    <x-input-label for="inv-warehouse" value="Warehouse *" />
                    <select
                        id="inv-warehouse"
                        wire:model="form.warehouse_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        @if ($isEditing) disabled @endif
                    >
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">
                                {{ $warehouse->name }} ({{ $warehouse->code }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('form.warehouse_id')" class="mt-1" />
                </div>

                {{-- Product Select --}}
                <div>
                    <x-input-label for="inv-product" value="Product *" />
                    <select
                        id="inv-product"
                        wire:model="form.product_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        @if ($isEditing) disabled @endif
                    >
                        <option value="">Select Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('form.product_id')" class="mt-1" />
                </div>

                {{-- Quantity Input --}}
                <div>
                    <x-input-label for="inv-quantity" value="Quantity in Stock *" />
                    <x-text-input
                        id="inv-quantity"
                        type="number"
                        min="0"
                        step="1"
                        class="mt-1 block w-full"
                        wire:model="form.quantity"
                        placeholder="0"
                        required
                    />
                    <x-input-error :messages="$errors->get('form.quantity')" class="mt-1" />
                </div>
            </div>

            {{-- Modal Actions --}}
            <div class="mt-6 flex justify-end gap-3">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    x-on:click="$dispatch('close-modal', 'inventory-form-modal')"
                >
                    Cancel
                </x-ui.button>

                <x-ui.button type="submit" variant="primary">
                    {{ $isEditing ? 'Update Stock' : 'Save Stock' }}
                </x-ui.button>
            </div>
        </form>
    </x-modal>
</div>

