@props(['name', 'checked' => false, 'error' => null])

<input type="checkbox" name="{{ $name }}" value="1"
    {{ ($checked || old($name)) ? 'checked' : '' }}
    {{ $attributes->merge(['class' => 'rounded border-gray-300 text-burgundy-600 shadow-sm focus:border-burgundy-500 focus:ring-burgundy-500']) }}>

@if($error || $errors->has($name))
    <p class="mt-1 text-sm text-red-600">{{ $error ?? $errors->first($name) }}</p>
@endif

