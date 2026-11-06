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
                    Stock Adjustments
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Reconcile physical stock counts, report variances, and record inventory adjustments
                </p>
            </div>

            <x-ui.button type="button" wire:click="create">
                New Adjustment
            </x-ui.button>
        </div>

        {{-- Filters & Search --}}
        <x-ui.card class="mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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

                {{-- Reason Filter --}}
                <div>
                    <label for="filter-reason" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Reason
                    </label>
                    <select
                        id="filter-reason"
                        wire:model.live="reasonFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="">All Reasons</option>
                        <option value="Cycle Count">Cycle Count</option>
                        <option value="Damaged Goods">Damaged Goods</option>
                        <option value="Loss / Shrinkage">Loss / Shrinkage</option>
                        <option value="Audit Variance">Audit Variance</option>
                        <option value="Found Stock">Found Stock</option>
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
                        placeholder="Search adjustment code (e.g. ADJ-202611...)"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                </div>
            </div>
        </x-ui.card>

        {{-- Stock Adjustment Table --}}
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
                        Warehouse
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Reason
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Total Items
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Net Variance
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

                @forelse ($stockAdjustments as $adj)
                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-medium text-indigo-600">
                            {{ $adj->reference_no }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $adj->adjustment_date?->format('Y-m-d') }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $adj->warehouse?->name ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $adj->reason ?? 'Cycle Count' }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-700">
                            {{ number_format($adj->total_items) }} items
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold">
                            @if ($adj->total_quantity_change > 0)
                                <span class="text-green-600 font-mono">+{{ number_format($adj->total_quantity_change) }}</span>
                            @elseif ($adj->total_quantity_change < 0)
                                <span class="text-red-600 font-mono">{{ number_format($adj->total_quantity_change) }}</span>
                            @else
                                <span class="text-gray-500 font-mono">0</span>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            <x-ui.badge type="info">
                                {{ ucfirst($adj->status) }}
                            </x-ui.badge>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                wire:click="viewDetail({{ $adj->id }})"
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
                            No stock adjustment records found.
                        </td>
                    </tr>
                @endforelse

            </x-slot>

        </x-ui.table>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $stockAdjustments->links() }}
        </div>

    </div>

    {{-- Stock Adjustment Create Modal --}}
    <x-modal name="stock-adjustment-create-modal" maxWidth="4xl" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-xl font-semibold text-gray-900">
                Create Stock Adjustment
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Reconcile physical inventory quantities against system stock with variance calculation and audit logs.
            </p>

            <div class="mt-6 space-y-4">
                {{-- Master Voucher Info --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <x-input-label for="adj-ref" value="Reference No" />
                        <x-text-input
                            id="adj-ref"
                            type="text"
                            class="mt-1 block w-full font-mono text-sm bg-gray-50"
                            wire:model="reference_no"
                            placeholder="Auto-generated"
                        />
                        <x-input-error :messages="$errors->get('reference_no')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="adj-date" value="Adjustment Date *" />
                        <x-text-input
                            id="adj-date"
                            type="date"
                            class="mt-1 block w-full text-sm"
                            wire:model="adjustment_date"
                            required
                        />
                        <x-input-error :messages="$errors->get('adjustment_date')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="adj-warehouse" value="Warehouse *" />
                        <select
                            id="adj-warehouse"
                            wire:model.live="warehouse_id"
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

                    <div>
                        <x-input-label for="adj-reason" value="Reason *" />
                        <select
                            id="adj-reason"
                            wire:model="reason"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required
                        >
                            <option value="Cycle Count">Cycle Count</option>
                            <option value="Damaged Goods">Damaged Goods</option>
                            <option value="Loss / Shrinkage">Loss / Shrinkage</option>
                            <option value="Audit Variance">Audit Variance</option>
                            <option value="Found Stock">Found Stock</option>
                            <option value="Other">Other</option>
                        </select>
                        <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                    </div>
                </div>

                {{-- Note --}}
                <div>
                    <x-input-label for="adj-note" value="Note / Remarks" />
                    <textarea
                        id="adj-note"
                        wire:model="note"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        placeholder="Optional remarks regarding this stock reconciliation or counting team..."
                    ></textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-1" />
                </div>

                {{-- Dynamic Items Table --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700">
                            Products to Reconcile
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
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 w-28">System Qty</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 w-32">Adjusted Qty *</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 w-28">Variance</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 w-44">Line Note</th>
                                    <th class="px-4 py-2 text-center font-medium text-gray-500 w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($items as $index => $item)
                                    @php
                                        $curr = (int) ($item['current_quantity'] ?? 0);
                                        $adj = (int) ($item['adjusted_quantity'] ?? 0);
                                        $diff = $adj - $curr;
                                    @endphp
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

                                        <td class="px-4 py-2 text-right font-mono text-gray-600 bg-gray-50">
                                            {{ number_format($curr) }}
                                        </td>

                                        <td class="px-4 py-2">
                                            <x-text-input
                                                type="number"
                                                min="0"
                                                step="1"
                                                wire:model.live="items.{{ $index }}.adjusted_quantity"
                                                class="w-full text-right text-sm"
                                                required
                                            />
                                            <x-input-error :messages="$errors->get('items.'.$index.'.adjusted_quantity')" class="mt-1" />
                                        </td>

                                        <td class="px-4 py-2 text-right font-mono font-semibold">
                                            @if ($diff > 0)
                                                <span class="text-green-600">+{{ number_format($diff) }}</span>
                                            @elseif ($diff < 0)
                                                <span class="text-red-600">{{ number_format($diff) }}</span>
                                            @else
                                                <span class="text-gray-400">0</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-2">
                                            <x-text-input
                                                type="text"
                                                wire:model="items.{{ $index }}.note"
                                                placeholder="e.g. Expired, broken box..."
                                                class="w-full text-xs"
                                            />
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
                    x-on:click="$dispatch('close-modal', 'stock-adjustment-create-modal')"
                >
                    Cancel
                </x-ui.button>

                <x-ui.button type="submit" variant="primary">
                    Confirm & Reconcile Stock
                </x-ui.button>
            </div>
        </form>
    </x-modal>

    {{-- Stock Adjustment Detail Modal --}}
    @if ($selectedStockAdjustment)
        <x-modal name="stock-adjustment-detail-modal" maxWidth="3xl" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 font-mono">
                            {{ $selectedStockAdjustment->reference_no }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            Reconciled on {{ $selectedStockAdjustment->adjustment_date?->format('F d, Y') }}
                        </p>
                    </div>

                    <x-ui.badge type="info">
                        {{ ucfirst($selectedStockAdjustment->status) }}
                    </x-ui.badge>
                </div>

                {{-- Header Details --}}
                <div class="grid grid-cols-2 gap-4 py-4 text-sm sm:grid-cols-4 border-b border-gray-200">
                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Warehouse</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockAdjustment->warehouse?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Reason</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockAdjustment->reason ?? 'Cycle Count' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Created By</span>
                        <span class="font-medium text-gray-900">{{ $selectedStockAdjustment->creator?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 block uppercase">Net Variance</span>
                        <span class="font-bold {{ $selectedStockAdjustment->total_quantity_change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $selectedStockAdjustment->total_quantity_change >= 0 ? '+'.$selectedStockAdjustment->total_quantity_change : $selectedStockAdjustment->total_quantity_change }}
                        </span>
                    </div>
                </div>

                @if ($selectedStockAdjustment->note)
                    <div class="py-3 text-sm text-gray-600 bg-gray-50 rounded-md p-3 my-3">
                        <strong class="text-gray-700">Note:</strong> {{ $selectedStockAdjustment->note }}
                    </div>
                @endif

                {{-- Items Table --}}
                <div class="mt-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700 mb-2">
                        Adjusted Products
                    </h3>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">SKU</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Product Name</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">System Qty</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Adjusted Qty</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Variance</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Item Note</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($selectedStockAdjustment->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-gray-700">{{ $item->product?->sku ?? '-' }}</td>
                                        <td class="px-4 py-2 text-gray-900 font-medium">{{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right text-gray-500 font-mono">{{ number_format($item->current_quantity) }}</td>
                                        <td class="px-4 py-2 text-right text-gray-900 font-bold font-mono">{{ number_format($item->adjusted_quantity) }}</td>
                                        <td class="px-4 py-2 text-right font-mono font-semibold">
                                            @if ($item->quantity_change > 0)
                                                <span class="text-green-600">+{{ number_format($item->quantity_change) }}</span>
                                            @elseif ($item->quantity_change < 0)
                                                <span class="text-red-600">{{ number_format($item->quantity_change) }}</span>
                                            @else
                                                <span class="text-gray-400">0</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-500">{{ $item->note ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <th colspan="4" class="px-4 py-2 text-left font-semibold text-gray-900">Total Net Variance</th>
                                    <th class="px-4 py-2 text-right font-bold font-mono {{ $selectedStockAdjustment->total_quantity_change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $selectedStockAdjustment->total_quantity_change >= 0 ? '+'.$selectedStockAdjustment->total_quantity_change : $selectedStockAdjustment->total_quantity_change }}
                                    </th>
                                    <th></th>
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
                        x-on:click="$dispatch('close-modal', 'stock-adjustment-detail-modal')"
                    >
                        Close
                    </x-ui.button>
                </div>
            </div>
        </x-modal>
    @endif
</div>

