@props(['value' => 0, 'count' => null, 'size' => 'h-4 w-4'])
@php
    $pct = max(0, min(100, ((float) $value / 5) * 100));
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    <span class="relative inline-flex" role="img" aria-label="{{ number_format((float) $value, 1) }} out of 5 stars">
        <span class="inline-flex text-gray-300" aria-hidden="true">@foreach (range(1, 5) as $n)<x-icon name="star" class="{{ $size }} fill-current" />@endforeach</span>
        <span class="absolute inset-y-0 left-0 inline-flex overflow-hidden text-brand-orange" style="width: {{ $pct }}%" aria-hidden="true">@foreach (range(1, 5) as $n)<x-icon name="star" class="{{ $size }} fill-current" />@endforeach</span>
    </span>
    @if ($count !== null)<span class="text-xs font-semibold text-brand-graphite">{{ $count }}</span>@endif
</span>
