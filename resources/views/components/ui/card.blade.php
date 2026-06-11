<div {{ $attributes->merge([
    'class' => 'bg-white rounded-lg shadow-sm border border-gray-200'
]) }}>
    @isset($header)
        <div class="px-6 py-4 border-b border-gray-200">
            {{ $header }}
        </div>
    @endisset

    <div class="p-6">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $footer }}
        </div>
    @endisset
</div>