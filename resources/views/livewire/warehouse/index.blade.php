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
                    Warehouses
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Manage your storage facilities and warehouse locations
                </p>
            </div>

            <x-ui.button type="button" wire:click="create">
                Add Warehouse
            </x-ui.button>
        </div>

        {{-- Search --}}
        <x-ui.card class="mb-6">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label
                        for="warehouse-search"
                        class="sr-only"
                    >
                        Search warehouses
                    </label>

                    <input
                        id="warehouse-search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by warehouse code or name..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>
        </x-ui.card>

        {{-- Warehouse Table --}}
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
                        Address
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

                @forelse ($warehouses as $warehouse)
                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $warehouse->code }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $warehouse->name }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                            {{ $warehouse->address ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($warehouse->is_active)
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
                                wire:click="edit({{ $warehouse->id }})"
                                class="text-xs px-2.5 py-1.5"
                            >
                                Edit
                            </x-ui.button>

                            <x-ui.button
                                type="button"
                                variant="danger"
                                wire:click="delete({{ $warehouse->id }})"
                                wire:confirm="Are you sure you want to delete this warehouse?"
                                class="text-xs px-2.5 py-1.5"
                            >
                                Delete
                            </x-ui.button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="5"
                            class="px-6 py-12 text-center text-sm text-gray-500"
                        >
                            No warehouses found.
                        </td>
                    </tr>
                @endforelse

            </x-slot>

        </x-ui.table>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $warehouses->links() }}
        </div>

    </div>

    {{-- Warehouse Create/Edit Modal --}}
    <x-modal name="warehouse-form-modal" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $isEditing ? 'Edit Warehouse' : 'Add New Warehouse' }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ $isEditing ? 'Update warehouse facility details below.' : 'Fill in the information below to register a new warehouse.' }}
            </p>

            <div class="mt-6 space-y-4">
                {{-- Code --}}
                <div>
                    <x-input-label for="warehouse-code" value="Warehouse Code *" />
                    <x-text-input
                        id="warehouse-code"
                        type="text"
                        class="mt-1 block w-full uppercase"
                        wire:model="form.code"
                        placeholder="e.g. WH001"
                        required
                    />
                    <x-input-error :messages="$errors->get('form.code')" class="mt-1" />
                </div>

                {{-- Name --}}
                <div>
                    <x-input-label for="warehouse-name" value="Warehouse Name *" />
                    <x-text-input
                        id="warehouse-name"
                        type="text"
                        class="mt-1 block w-full"
                        wire:model="form.name"
                        placeholder="e.g. Central Distribution Hub"
                        required
                    />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-1" />
                </div>

                {{-- Address --}}
                <div>
                    <x-input-label for="warehouse-address" value="Address" />
                    <textarea
                        id="warehouse-address"
                        wire:model="form.address"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        placeholder="e.g. 123 Logistics Boulevard"
                    ></textarea>
                    <x-input-error :messages="$errors->get('form.address')" class="mt-1" />
                </div>

                {{-- Is Active Checkbox --}}
                <div class="flex items-center pt-2">
                    <label for="warehouse-active" class="inline-flex items-center cursor-pointer">
                        <input
                            id="warehouse-active"
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
                    x-on:click="$dispatch('close-modal', 'warehouse-form-modal')"
                >
                    Cancel
                </x-ui.button>

                <x-ui.button type="submit" variant="primary">
                    {{ $isEditing ? 'Save Changes' : 'Create Warehouse' }}
                </x-ui.button>
            </div>
        </form>
    </x-modal>
</div>
