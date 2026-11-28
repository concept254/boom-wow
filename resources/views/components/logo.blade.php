@props(['variant' => 'light', 'class' => 'h-10 w-auto', 'mark' => false])
@php
    // Vector logos with transparent backgrounds (traced from the client's print JPG: emblem, name line and web address bar; `mark` = emblem only).
    // 'dark' is for dark surfaces such as the header and footer. If an SVG is missing we fall back to
    // the original JPG (on a white chip for dark surfaces) so the site never shows a broken image.
    $darkPath = config('boomwow.logo.dark');
    $lightPath = config('boomwow.logo.light');
    $fallback = config('boomwow.logo.fallback');
    $wanted = $mark
        ? ($variant === 'dark' ? config('boomwow.logo.mark_dark') : config('boomwow.logo.mark_light'))
        : ($variant === 'dark' ? $darkPath : $lightPath);
    if (! $wanted || ! file_exists(public_path($wanted))) {
        $wanted = $variant === 'dark' ? $darkPath : $lightPath;
    }
    $path = $wanted && file_exists(public_path($wanted)) ? $wanted : null;
    $chip = false;
    if (! $path && $fallback && file_exists(public_path($fallback))) {
        $path = $fallback;
        $chip = $variant === 'dark';
    }
@endphp
@if ($path && $chip)
    <span class="inline-flex rounded-md bg-brand-white p-1.5"><img src="{{ asset($path) }}" alt="Boom &amp; Wow Holdings" {{ $attributes->merge(['class' => $class]) }}></span>
@elseif ($path)
    <img src="{{ asset($path) }}" alt="Boom &amp; Wow Holdings" {{ $attributes->merge(['class' => $class]) }}>
@else
    <span {{ $attributes->merge(['class' => 'font-display text-xl font-bold tracking-tight '.($variant === 'dark' ? 'text-brand-white' : 'text-brand-charcoal')]) }}>Boom <span class="text-brand-orange">&amp;</span> Wow</span>
@endif
