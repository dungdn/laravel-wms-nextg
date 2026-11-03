<div {{ $attributes->merge([
    'class' => 'overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm'
]) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            @isset($head)
                <thead class="bg-gray-50">
                    {{ $head }}
                </thead>
            @endisset

            @isset($body)
                <tbody class="divide-y divide-gray-200 bg-white">
                    {{ $body }}
                </tbody>
            @endisset
        </table>
    </div>
</div>