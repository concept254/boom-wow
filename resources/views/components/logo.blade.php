@props(['variant' => 'light', 'class' => 'h-10 w-auto'])
@php
    // The official logo is a JPG on a white background. On dark surfaces we use the optional
    // dark variant if one has been supplied; otherwise the original is shown unaltered on a white chip.
    $darkPath = config('boomwow.logo.dark');
    $lightPath = config('boomwow.logo.light');
    $useDark = $variant === 'dark' && $darkPath && file_exists(public_path($darkPath));
    $path = $useDark ? $darkPath : $lightPath;
    $exists = $path && file_exists(public_path($path));
    $chip = $variant === 'dark' && ! $useDark;
@endphp
@if ($exists)
    @if ($chip)
        <span class="inline-flex rounded-md bg-brand-white p-1.5"><img src="{{ asset($path) }}" alt="Boom &amp; Wow Holdings" {{ $attributes->merge(['class' => $class]) }}></span>
    @else
        <img src="{{ asset($path) }}" alt="Boom &amp; Wow Holdings" {{ $attributes->merge(['class' => $class]) }}>
    @endif
@else
    <span {{ $attributes->merge(['class' => 'font-display text-xl font-bold tracking-tight '.($variant === 'dark' ? 'text-brand-white' : 'text-brand-charcoal')]) }}>Boom <span class="text-brand-orange">&amp;</span> Wow</span>
@endif
