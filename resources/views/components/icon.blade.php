@props(['name'])
{{-- Inline SVG icon from config/icons.php. Size and colour come from classes, e.g. <x-icon name="phone" class="h-5 w-5 text-brand-orange" />. --}}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }}>{!! config('icons.'.$name, '<circle cx="12" cy="12" r="9"/>') !!}</svg>
