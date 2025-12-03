@props(['variant' => 'default'])

@if($variant === 'primary')
    <span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-burgundy-100 text-burgundy-800']) }}>
        {{ $slot }}
    </span>
@elseif($variant === 'success')
    <span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800']) }}>
        {{ $slot }}
    </span>
@elseif($variant === 'warning')
    <span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800']) }}>
        {{ $slot }}
    </span>
@elseif($variant === 'danger')
    <span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800']) }}>
        {{ $slot }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800']) }}>
        {{ $slot }}
    </span>
@endif

