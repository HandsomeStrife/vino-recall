@props(['title', 'value', 'accentColor' => 'burgundy-500'])

<div class="bg-white p-6 rounded-lg border-2 border-black border-t-4 border-t-{{ $accentColor }}">
    <h3 class="text-sm font-semibold text-gray-600 uppercase mb-2">{{ $title }}</h3>
    <p class="text-4xl font-bold text-burgundy-900">{{ $value }}</p>
</div>

