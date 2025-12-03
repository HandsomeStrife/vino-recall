@props(['variant' => 'primary', 'type' => 'button'])

@if($variant === 'secondary')
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'px-4 py-2 rounded-lg font-medium transition bg-gray-200 text-gray-800 hover:bg-gray-300']) }}>
        {{ $slot }}
    </button>
@elseif($variant === 'danger')
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'px-4 py-2 rounded-lg font-medium transition bg-red-500 text-white hover:bg-red-600']) }}>
        {{ $slot }}
    </button>
@elseif($variant === 'success')
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'px-4 py-2 rounded-lg font-medium transition bg-green-500 text-white hover:bg-green-600']) }}>
        {{ $slot }}
    </button>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'px-4 py-2 rounded-lg font-medium transition bg-burgundy-500 text-white hover:bg-burgundy-600']) }}>
        {{ $slot }}
    </button>
@endif

