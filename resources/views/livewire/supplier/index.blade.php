<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Suppliers
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Manage your suppliers
                </p>
            </div>

            <x-ui.button type="button">
                Add Supplier
            </x-ui.button>
        </div>

        {{-- Search --}}
        <x-ui.card class="mb-6">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label
                        for="supplier-search"
                        class="sr-only"
                    >
                        Search suppliers
                    </label>

                    <input
                        id="supplier-search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by code or name..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>
        </x-ui.card>

        {{-- Supplier Table --}}
        <x-ui.table>

            <x-slot:head>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Code
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Name
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Contact
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Phone
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Status
                    </th>
                </tr>
            </x-slot>

            <x-slot:body>

                @forelse ($suppliers as $supplier)
                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $supplier->code }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $supplier->name }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $supplier->contact_name ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $supplier->phone ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($supplier->is_active)
                                <x-ui.badge type="success">
                                    Active
                                </x-ui.badge>
                            @else
                                <x-ui.badge type="danger">
                                    Inactive
                                </x-ui.badge>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="5"
                            class="px-6 py-12 text-center text-sm text-gray-500"
                        >
                            No suppliers found.
                        </td>
                    </tr>
                @endforelse

            </x-slot>

        </x-ui.table>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $suppliers->links() }}
        </div>

    </div>
</div>