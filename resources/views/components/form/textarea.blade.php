@props(['name', 'value' => '', 'error' => null])

<textarea name="{{ $name }}" rows="3"
    {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-burgundy-500 focus:ring-burgundy-500 px-3 py-2 text-base']) }}>{{ old($name, $value) }}</textarea>

@if($error || $errors->has($name))
    <p class="mt-1 text-sm text-red-600">{{ $error ?? $errors->first($name) }}</p>
@endif

