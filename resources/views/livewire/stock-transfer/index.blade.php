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
                    Stock Transfers
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Move and rebalance inventory between warehouses with dual-entry audit logging
                </p>
            </div>

            <x-ui.button type="button" wire:click="create">
                New Transfer
            </x-ui.button>
        </div>

        {{-- Filters & Search --}}
        <x-ui.card class="mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                {{-- Source Warehouse Filter --}}
                <div>
                    <label for="filter-from-wh" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Source Warehouse
                    </label>
                    <select
                        id="filter-from-wh"
                        wire:model.live="fromWarehouseFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="">All Source Warehouses</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Destination Warehouse Filter --}}
                <div>
                    <label for="filter-to-wh" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Destination Warehouse
                    </label>
                    <select
                        id="filter-to-wh"
                        wire:model.live="toWarehouseFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="">All Destination Warehouses</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Reference Search --}}
                <div>
                    <label for="search-ref" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Search Reference No
                    </label>
                    <input
                        id="search-ref"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search transfer code (e.g. TR-202611...)"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                </div>
            </div>
        </x-ui.card>

        {{-- Stock Transfer Table --}}
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
                        Source Warehouse
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Destination Warehouse
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Total Qty
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

                @forelse ($stockTransfers as $transfer)
                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-medium text-indigo-600">
                            {{ $transfer->reference_no }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $transfer->transfer_date?->format('Y-m-d') }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            <span class="inline-flex items-center gap-1 font-medium text-gray-900">
                                {{ $transfer->fromWarehouse?->name ?? '-' }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            <span class="inline-flex items-center gap-1 font-medium text-indigo-700">
                                &rarr; {{ $transfer->toWarehouse?->name ?? '-' }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ number_format($transfer->total_quantity) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            <x-ui.badge type="success">
                                {{ ucfirst($transfer->status) }}
                            </x-ui.badge>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                wire:click="viewDetail({{ $transfer->id }})"
                                class="text-xs px-2.5 py-1.5"
                            >
                                View Details
                            </x-ui.button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="7"
                            class="px-6 py-12 text-center text-sm text-gray-500"
                        >
                            No stock transfer records found.
                        </td>
                    </tr>
                @endforelse

            </x-slot>

        </x-ui.table>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $stockTransfers->links() }}
        </div>

    </div>

    {{-- Stock Transfer Create Modal --}}
    <x-modal name="stock-transfer-create-modal" maxWidth="4xl" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-xl font-semibold text-gray-900">
                Create Stock Transfer
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Transfer inventory from one warehouse to another with validation and automated stock movement audit logs.
            </p>

            <div class="mt-6 space-y-4">
                {{-- Master Voucher Info --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="transfer-ref" value="Reference No" />
                        <x-text-input
                            id="transfer-ref"
                            type="text"
                            class="mt-1 block w-full font-mono text-sm bg-gray-50"
                            wire:model="reference_no"
                            placeholder="Auto-generated"
                        />
                        <x-input-error :messages="$errors->get('reference_no')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="transfer-date" value="Transfer Date *" />
                        <x-text-input
                            id="transfer-date"
                            type="date"
                            class="mt-1 block w-full text-sm"
                            wire:model="transfer_date"
                            required
                        />
                        <x-input-error :messages="$errors->get('transfer_date')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="transfer-from-wh" value="Source Warehouse (From) *" />
                        <select
                            id="transfer-from-wh"
                            wire:model.live="from_warehouse_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required
                        >
                            <option value="">Select Source Warehouse</option>
                            @foreach ($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('from_warehouse_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="transfer-to-wh" value="Destination Warehouse (To) *" />
                        <select
                            id="transfer-to-wh"
                            wire:model.live="to_warehouse_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required
                        >
                            <option value="">Select Destination Warehouse</option>
                            @foreach ($warehouses as $wh)
                                <option value="{{ $wh->id }}" @disabled($wh->id == $from_warehouse_id)>
                                    {{ $wh->name }} ({{ $wh->code }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('to_warehouse_id')" class="mt-1" />
                    </div>
                </div>

                {{-- Note --}}
                <div>
                    <x-input-label for="transfer-note" value="Note / Remarks" />
                    <textarea
                        id="transfer-note"
                        wire:model="note"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        placeholder="Optional remarks regarding this stock rebalance or transfer reason..."
                    ></textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-1" />
                </div>

                {{-- Dynamic Items Table --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700">
                            Items to Transfer
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
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 w-36">Quantity *</th>
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
                    x-on:click="$dispatch('close-modal', 'stock-transfer-create-modal')"
                >
                    Cancel
                </x-ui.button>

                <x-ui.button type="submit" variant="primary">
                    Confirm & Transfer Stock
                </x-ui.button>
            </div>
        </form>
    </x-modal>

    {{-- Stock Transfer Detail Modal --}}
    @if ($selectedStockTransfer)
        <x-modal name="stock-transfer-detail-modal" maxWidth="3xl" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 font-mono">
                            {{ $selectedStockTransfer->reference_no }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            Transferred on {{ $selectedStockTransfer->transfer_date?->format('F d, Y') }}
                        </p>
                    </div>

                    <x-ui.badge type="success">
                        {{ ucfirst($selectedStockTransfer->status) }}
                    </x-ui.badge>
                </div>

                {{-- Header Details --}}
                <div class="grid grid-cols-2 gap-4 py-4 text-sm sm:grid-cols-4 border-b border-gray-200">
                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Source (From)</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockTransfer->fromWarehouse?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Destination (To)</span>
                        <span class="font-medium text-indigo-700">{{ $selectedStockTransfer->toWarehouse?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Created By</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockTransfer->creator?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Total Items</span>
                        <span class="font-semibold text-gray-900">{{ $selectedStockTransfer->items->count() }} line items</span>
                    </div>
                </div>

                @if ($selectedStockTransfer->note)
                    <div class="py-3 text-sm text-gray-600 bg-gray-50 rounded-md p-3 my-3">
                        <strong class="text-gray-700">Note:</strong> {{ $selectedStockTransfer->note }}
                    </div>
                @endif

                {{-- Items Table --}}
                <div class="mt-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700 mb-2">
                        Transferred Products
                    </h3>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">SKU</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Product Name</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Transferred Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($selectedStockTransfer->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-gray-700">{{ $item->product?->sku ?? '-' }}</td>
                                        <td class="px-4 py-2 text-gray-900 font-medium">{{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-gray-900">{{ number_format($item->quantity) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <th colspan="2" class="px-4 py-2 text-left font-semibold text-gray-900">Total Quantity</th>
                                    <th class="px-4 py-2 text-right font-bold text-indigo-600">{{ number_format($selectedStockTransfer->total_quantity) }}</th>
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
                        x-on:click="$dispatch('close-modal', 'stock-transfer-detail-modal')"
                    >
                        Close
                    </x-ui.button>
                </div>
            </div>
        </x-modal>
    @endif
</div>

