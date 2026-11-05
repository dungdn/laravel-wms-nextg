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
                    Stock In
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Manage goods receipt vouchers and inbound inventory transactions
                </p>
            </div>

            <x-ui.button type="button" wire:click="create">
                New Stock In
            </x-ui.button>
        </div>

        {{-- Filters & Search --}}
        <x-ui.card class="mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                {{-- Warehouse Filter --}}
                <div>
                    <label for="filter-warehouse" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Warehouse
                    </label>
                    <select
                        id="filter-warehouse"
                        wire:model.live="warehouseFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="">All Warehouses</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Supplier Filter --}}
                <div>
                    <label for="filter-supplier" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Supplier
                    </label>
                    <select
                        id="filter-supplier"
                        wire:model.live="supplierFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="">All Suppliers</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Reference Search --}}
                <div class="sm:col-span-2">
                    <label for="search-ref" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Search Reference No
                    </label>
                    <input
                        id="search-ref"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search voucher by reference code (e.g. IN-202611...)"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                </div>
            </div>
        </x-ui.card>

        {{-- Stock In Table --}}
        <x-ui.table>

            <x-slot:head>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Reference No
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Date
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Supplier
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Warehouse
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Total Qty
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Total Amount
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

                @forelse ($stockIns as $stockIn)
                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-medium text-indigo-600">
                            {{ $stockIn->reference_no }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $stockIn->transaction_date?->format('Y-m-d') }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $stockIn->supplier?->name ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $stockIn->warehouse?->name ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ number_format($stockIn->total_quantity) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">
                            ${{ number_format($stockIn->total_amount, 2) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            <x-ui.badge type="success">
                                {{ ucfirst($stockIn->status) }}
                            </x-ui.badge>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                wire:click="viewDetail({{ $stockIn->id }})"
                                class="text-xs px-2.5 py-1.5"
                            >
                                View Details
                            </x-ui.button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="8"
                            class="px-6 py-12 text-center text-sm text-gray-500"
                        >
                            No stock in records found.
                        </td>
                    </tr>
                @endforelse

            </x-slot>

        </x-ui.table>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $stockIns->links() }}
        </div>

    </div>

    {{-- Stock In Create Modal --}}
    <x-modal name="stock-in-create-modal" maxWidth="4xl" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-xl font-semibold text-gray-900">
                Create Stock In Voucher
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Receive products into warehouse inventory and automatically record stock audit logs.
            </p>

            <div class="mt-6 space-y-4">
                {{-- Master Voucher Info --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <x-input-label for="voucher-ref" value="Reference No" />
                        <x-text-input
                            id="voucher-ref"
                            type="text"
                            class="mt-1 block w-full font-mono text-sm bg-gray-50"
                            wire:model="reference_no"
                            placeholder="Auto-generated"
                        />
                        <x-input-error :messages="$errors->get('reference_no')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="voucher-date" value="Transaction Date *" />
                        <x-text-input
                            id="voucher-date"
                            type="date"
                            class="mt-1 block w-full text-sm"
                            wire:model="transaction_date"
                            required
                        />
                        <x-input-error :messages="$errors->get('transaction_date')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="voucher-supplier" value="Supplier *" />
                        <select
                            id="voucher-supplier"
                            wire:model="supplier_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required
                        >
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="voucher-warehouse" value="Receiving Warehouse *" />
                        <select
                            id="voucher-warehouse"
                            wire:model="warehouse_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required
                        >
                            <option value="">Select Warehouse</option>
                            @foreach ($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1" />
                    </div>
                </div>

                {{-- Note --}}
                <div>
                    <x-input-label for="voucher-note" value="Note / Remarks" />
                    <textarea
                        id="voucher-note"
                        wire:model="note"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        placeholder="Optional remarks regarding this shipment or invoice reference..."
                    ></textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-1" />
                </div>

                {{-- Dynamic Items Table --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700">
                            Items to Receive
                        </h3>

                        <x-ui.button type="button" variant="secondary" wire:click="addItem" class="text-xs px-2.5 py-1">
                            + Add Row
                        </x-ui.button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Product *</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 w-32">Quantity *</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 w-36">Unit Cost ($) *</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 w-36">Subtotal</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500 w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($items as $index => $item)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <select
                                                wire:model.live="items.{{ $index }}.product_id"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                required
                                            >
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->name }} ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('items.'.$index.'.product_id')" class="mt-1" />
                                        </td>

                                        <td class="px-4 py-2">
                                            <x-text-input
                                                type="number"
                                                min="1"
                                                step="1"
                                                wire:model.live="items.{{ $index }}.quantity"
                                                class="w-full text-right text-sm"
                                                required
                                            />
                                            <x-input-error :messages="$errors->get('items.'.$index.'.quantity')" class="mt-1" />
                                        </td>

                                        <td class="px-4 py-2">
                                            <x-text-input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                wire:model.live="items.{{ $index }}.unit_cost"
                                                class="w-full text-right text-sm"
                                                required
                                            />
                                            <x-input-error :messages="$errors->get('items.'.$index.'.unit_cost')" class="mt-1" />
                                        </td>

                                        <td class="px-4 py-2 text-right font-mono text-gray-900 font-medium">
                                            ${{ number_format(((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_cost'] ?? 0)), 2) }}
                                        </td>

                                        <td class="px-4 py-2 text-center">
                                            @if (count($items) > 1)
                                                <button
                                                    type="button"
                                                    wire:click="removeItem({{ $index }})"
                                                    class="text-red-500 hover:text-red-700 font-bold"
                                                    title="Remove item"
                                                >
                                                    &times;
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="mt-1" />
                </div>
            </div>

            {{-- Modal Actions --}}
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    x-on:click="$dispatch('close-modal', 'stock-in-create-modal')"
                >
                    Cancel
                </x-ui.button>

                <x-ui.button type="submit" variant="primary">
                    Confirm & Receive Stock
                </x-ui.button>
            </div>
        </form>
    </x-modal>

    {{-- Stock In Detail Modal --}}
    @if ($selectedStockIn)
        <x-modal name="stock-in-detail-modal" maxWidth="3xl" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 font-mono">
                            {{ $selectedStockIn->reference_no }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            Recorded on {{ $selectedStockIn->transaction_date?->format('F d, Y') }}
                        </p>
                    </div>

                    <x-ui.badge type="success">
                        {{ ucfirst($selectedStockIn->status) }}
                    </x-ui.badge>
                </div>

                {{-- Header Details --}}
                <div class="grid grid-cols-2 gap-4 py-4 text-sm sm:grid-cols-4 border-b border-gray-200">
                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Supplier</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockIn->supplier?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Warehouse</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockIn->warehouse?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Created By</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockIn->creator?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Total Items</span>
                        <span class="font-semibold text-gray-900">{{ $selectedStockIn->items->count() }} line items</span>
                    </div>
                </div>

                @if ($selectedStockIn->note)
                    <div class="py-3 text-sm text-gray-600 bg-gray-50 rounded-md p-3 my-3">
                        <strong class="text-gray-700">Note:</strong> {{ $selectedStockIn->note }}
                    </div>
                @endif

                {{-- Items Table --}}
                <div class="mt-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700 mb-2">
                        Received Products
                    </h3>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">SKU</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Product Name</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Quantity</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Unit Cost</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($selectedStockIn->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-gray-700">{{ $item->product?->sku ?? '-' }}</td>
                                        <td class="px-4 py-2 text-gray-900 font-medium">{{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-gray-900">{{ number_format($item->quantity) }}</td>
                                        <td class="px-4 py-2 text-right text-gray-700">${{ number_format($item->unit_cost, 2) }}</td>
                                        <td class="px-4 py-2 text-right font-mono font-medium text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <th colspan="2" class="px-4 py-2 text-left font-semibold text-gray-900">Total</th>
                                    <th class="px-4 py-2 text-right font-bold text-gray-900">{{ number_format($selectedStockIn->total_quantity) }}</th>
                                    <th></th>
                                    <th class="px-4 py-2 text-right font-bold text-indigo-600 font-mono">${{ number_format($selectedStockIn->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="mt-6 flex justify-end">
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        x-on:click="$dispatch('close-modal', 'stock-in-detail-modal')"
                    >
                        Close
                    </x-ui.button>
                </div>
            </div>
        </x-modal>
    @endif
</div>

