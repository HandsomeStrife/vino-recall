@props(['name', 'options' => [], 'selected' => null, 'error' => null])

<select name="{{ $name }}"
    {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-burgundy-500 focus:ring-burgundy-500 px-3 py-2 text-base']) }}>
    @foreach($options as $value => $label)
        <option value="{{ $value === '' ? '' : $value }}" {{ ($selected ?? old($name)) == $value || ($value === '' && ($selected ?? old($name)) === null) ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>

@if($error || $errors->has($name))
    <p class="mt-1 text-sm text-red-600">{{ $error ?? $errors->first($name) }}</p>
@endif

