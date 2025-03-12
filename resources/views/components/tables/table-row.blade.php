@props(['isHead' => false])
<tr {{ $attributes->class(['even:bg-gray-50 hover:bg-indigo-50' => !$isHead])->merge() }}>
    {{ $slot }}
</tr>
