@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
        'success' => 'bg-green-600 text-white border-transparent hover:bg-green-700 focus:ring-green-500',
        'danger' => 'bg-red-600 text-white border-transparent hover:bg-red-700 focus:ring-red-500',
        'warning' => 'bg-amber-500 text-white border-transparent hover:bg-amber-600 focus:ring-amber-500',
        default => 'bg-indigo-600 text-white border-transparent hover:bg-indigo-700 focus:ring-indigo-500',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-2.5 py-1.5 text-xs',
        'lg' => 'px-5 py-2.5 text-base',
        default => 'px-4 py-2 text-sm',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "inline-flex items-center justify-center border font-medium rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed {$sizeClasses} {$variantClasses}"
    ]) }}
>
    {{ $slot }}
</button>