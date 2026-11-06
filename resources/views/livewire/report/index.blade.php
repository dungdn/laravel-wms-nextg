<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                Reports & Inventory Audit Logs
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Real-time stock valuation, inventory movements, and warehouse audit history
            </p>
        </div>

        {{-- Executive KPI Metrics Cards --}}
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Card 1: Total Products --}}
            <div class="overflow-hidden rounded-lg bg-white p-5 shadow border border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-indigo-50 p-3 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-xs font-medium uppercase text-gray-500">Catalog Products</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['total_products']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Card 2: Total Units in Stock --}}
            <div class="overflow-hidden rounded-lg bg-white p-5 shadow border border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-blue-50 p-3 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-xs font-medium uppercase text-gray-500">Total Units in Stock</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['total_units']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Card 3: Inventory Valuation --}}
            <div class="overflow-hidden rounded-lg bg-white p-5 shadow border border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-green-50 p-3 text-green-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-xs font-medium uppercase text-gray-500">Total Valuation (Cost)</dt>
                            <dd class="text-2xl font-semibold text-indigo-600 font-mono">${{ number_format($metrics['total_valuation'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Card 4: Stock Alerts --}}
            <div class="overflow-hidden rounded-lg bg-white p-5 shadow border border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-amber-50 p-3 text-amber-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-xs font-medium uppercase text-gray-500">Stock Alerts</dt>
                            <dd class="text-sm font-semibold text-gray-900 mt-1 flex items-center gap-2">
                                <span class="text-amber-700 bg-amber-100 px-2 py-0.5 rounded text-xs font-bold">{{ $metrics['low_stock_count'] }} Low</span>
                                <span class="text-red-700 bg-red-100 px-2 py-0.5 rounded text-xs font-bold">{{ $metrics['out_of_stock_count'] }} Out</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Controls --}}
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button
                    type="button"
                    wire:click="setTab('audit')"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'audit' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Inventory Audit Log (Thẻ kho)
                </button>

                <button
                    type="button"
                    wire:click="setTab('warehouse')"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'warehouse' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Warehouse Stock Breakdown (Tổng hợp theo kho)
                </button>
            </nav>
        </div>

        {{-- TAB 1: INVENTORY AUDIT LOG --}}
        @if ($activeTab === 'audit')
            {{-- Filter Card --}}
            <x-ui.card class="mb-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
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

                    {{-- Product Filter --}}
                    <div>
                        <label for="filter-product" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            Product
                        </label>
                        <select
                            id="filter-product"
                            wire:model.live="productFilter"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">All Products</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Transaction Type Filter --}}
                    <div>
                        <label for="filter-type" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            Movement Type
                        </label>
                        <select
                            id="filter-type"
                            wire:model.live="typeFilter"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">All Types</option>
                            @foreach ($transactionTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label for="filter-from-date" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            From Date
                        </label>
                        <input
                            id="filter-from-date"
                            type="date"
                            wire:model.live="fromDate"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label for="filter-to-date" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            To Date
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                id="filter-to-date"
                                type="date"
                                wire:model.live="toDate"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >
                            @if ($warehouseFilter || $productFilter || $typeFilter || $fromDate || $toDate)
                                <button
                                    type="button"
                                    wire:click="resetFilters"
                                    class="text-xs text-gray-500 hover:text-red-600 underline whitespace-nowrap"
                                    title="Clear filters"
                                >
                                    Reset
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Audit Trail Table --}}
            <x-ui.table>
                <x-slot:head>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Timestamp
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Warehouse
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Product
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Type
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Before
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Change
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            After
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            User
                        </th>
                    </tr>
                </x-slot>

                <x-slot:body>
                    @forelse ($auditTrail as $tx)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-xs font-mono text-gray-600">
                                {{ $tx->created_at?->format('Y-m-d H:i') }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $tx->warehouse?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-800">
                                <div class="font-medium text-gray-900">{{ $tx->product?->name ?? '-' }}</div>
                                <div class="text-xs font-mono text-gray-500">{{ $tx->product?->sku ?? '' }}</div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($tx->transaction_type === 'IN')
                                    <x-ui.badge type="success">Stock In</x-ui.badge>
                                @elseif ($tx->transaction_type === 'OUT')
                                    <x-ui.badge type="danger">Stock Out</x-ui.badge>
                                @elseif ($tx->transaction_type === 'TRANSFER_IN')
                                    <x-ui.badge type="info">Transfer In</x-ui.badge>
                                @elseif ($tx->transaction_type === 'TRANSFER_OUT')
                                    <x-ui.badge type="warning">Transfer Out</x-ui.badge>
                                @else
                                    <x-ui.badge type="neutral">Adjustment</x-ui.badge>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-mono text-gray-500">
                                {{ number_format($tx->qty_before) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-mono font-bold">
                                @if ($tx->qty_change > 0)
                                    <span class="text-green-600">+{{ number_format($tx->qty_change) }}</span>
                                @elseif ($tx->qty_change < 0)
                                    <span class="text-red-600">{{ number_format($tx->qty_change) }}</span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-mono font-semibold text-gray-900">
                                {{ number_format($tx->qty_after) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600">
                                {{ $tx->creator?->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                No inventory transaction logs found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </x-slot>
            </x-ui.table>

            <div class="mt-6">
                {{ $auditTrail->links() }}
            </div>
        @endif

        {{-- TAB 2: WAREHOUSE STOCK BREAKDOWN --}}
        @if ($activeTab === 'warehouse')
            <x-ui.table>
                <x-slot:head>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Warehouse Code
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Warehouse Name
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Product Lines
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Total Units
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Valuation (Cost)
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Low Stock
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Out of Stock
                        </th>
                    </tr>
                </x-slot>

                <x-slot:body>
                    @forelse ($warehouseBreakdown as $whSummary)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-medium text-indigo-600">
                                {{ $whSummary['warehouse_code'] }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ $whSummary['warehouse_name'] }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-700">
                                {{ number_format($whSummary['total_products']) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-gray-900">
                                {{ number_format($whSummary['total_units']) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-indigo-600 font-mono">
                                ${{ number_format($whSummary['total_valuation'], 2) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                @if ($whSummary['low_stock_items'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">
                                        {{ $whSummary['low_stock_items'] }} items
                                    </span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                @if ($whSummary['out_of_stock_items'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                                        {{ $whSummary['out_of_stock_items'] }} items
                                    </span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                No warehouse inventory records found.
                            </td>
                        </tr>
                    @endforelse
                </x-slot>
            </x-ui.table>
        @endif

    </div>
</div>

