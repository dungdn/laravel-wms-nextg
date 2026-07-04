<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">

            <x-ui.card>
                <x-slot:header>
                    WMS Dashboard
                </x-slot:header>

                Welcome to Laravel WMS
            </x-ui.card>


            <x-ui.card>
                <x-slot:header>
                    UI Button Demo
                </x-slot:header>

                <div class="flex flex-wrap gap-3">
                    <x-ui.button>
                        Save
                    </x-ui.button>

                    <x-ui.button variant="secondary">
                        Cancel
                    </x-ui.button>

                    <x-ui.button variant="success">
                        Confirm
                    </x-ui.button>

                    <x-ui.button variant="danger">
                        Delete
                    </x-ui.button>

                    <x-ui.button disabled>
                        Disabled
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>