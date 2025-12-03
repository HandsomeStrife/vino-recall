@props(['type' => 'text', 'name', 'value' => '', 'error' => null])

<input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
    {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-burgundy-500 focus:ring-burgundy-500 sm:text-sm']) }}>

@if($error || $errors->has($name))
    <p class="mt-1 text-sm text-red-600">{{ $error ?? $errors->first($name) }}</p>
@endif

