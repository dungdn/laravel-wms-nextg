<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            UI Components
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Buttons --}}
            <x-ui.card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Buttons
                </h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Variants</h4>
                        <div class="flex flex-wrap gap-3">
                            <x-ui.button>
                                Primary
                            </x-ui.button>

                            <x-ui.button variant="secondary">
                                Secondary
                            </x-ui.button>

                            <x-ui.button variant="success">
                                Success
                            </x-ui.button>

                            <x-ui.button variant="danger">
                                Danger
                            </x-ui.button>

                            <x-ui.button variant="warning">
                                Warning
                            </x-ui.button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Sizes</h4>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.button size="sm">
                                Small (sm)
                            </x-ui.button>

                            <x-ui.button size="md">
                                Medium (md)
                            </x-ui.button>

                            <x-ui.button size="lg">
                                Large (lg)
                            </x-ui.button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Disabled State</h4>
                        <div class="flex flex-wrap gap-3">
                            <x-ui.button disabled>
                                Primary
                            </x-ui.button>

                            <x-ui.button variant="secondary" disabled>
                                Secondary
                            </x-ui.button>

                            <x-ui.button variant="success" disabled>
                                Success
                            </x-ui.button>

                            <x-ui.button variant="danger" disabled>
                                Danger
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Badges --}}
            <x-ui.card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Badges
                </h3>

                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.badge type="success">
                        Active
                    </x-ui.badge>

                    <x-ui.badge type="danger">
                        Inactive
                    </x-ui.badge>

                    <x-ui.badge type="warning">
                        Warning
                    </x-ui.badge>

                    <x-ui.badge type="info">
                        Information
                    </x-ui.badge>

                    <x-ui.badge type="neutral">
                        Neutral
                    </x-ui.badge>
                </div>
            </x-ui.card>

            {{-- Table --}}
            <x-ui.card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Table
                </h3>

                <x-ui.table>

                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>

                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </x-slot:head>

                    <x-slot:body>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                SUP001
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Dell Vietnam
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge type="success">
                                    Active
                                </x-ui.badge>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <x-ui.button variant="secondary" size="sm">
                                    Edit
                                </x-ui.button>
                                <x-ui.button variant="danger" size="sm">
                                    Delete
                                </x-ui.button>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                SUP002
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Logitech Vietnam
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge type="success">
                                    Active
                                </x-ui.badge>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <x-ui.button variant="secondary" size="sm">
                                    Edit
                                </x-ui.button>
                                <x-ui.button variant="danger" size="sm">
                                    Delete
                                </x-ui.button>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                SUP003
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Samsung Vietnam
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge type="danger">
                                    Inactive
                                </x-ui.badge>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <x-ui.button variant="secondary" size="sm">
                                    Edit
                                </x-ui.button>
                                <x-ui.button variant="danger" size="sm">
                                    Delete
                                </x-ui.button>
                            </td>
                        </tr>
                    </x-slot:body>

                </x-ui.table>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>