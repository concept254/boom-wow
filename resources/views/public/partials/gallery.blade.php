{{-- Photo gallery. Vars: $slides (list of url/alt/caption), $badge (optional text over the photo). Thumbnails, arrows, hover zoom, lightbox. --}}
@php
    $first = $slides[0];
    $many = count($slides) > 1;
@endphp
<div x-data="productGallery(@js($slides))" x-on:keydown.window.escape="close()" x-on:keydown.window.arrow-right="lightbox && next()" x-on:keydown.window.arrow-left="lightbox && prev()" class="flex flex-col-reverse gap-3 lg:flex-row">
    @if ($many)
        <div class="no-scrollbar flex gap-2 overflow-x-auto lg:max-h-[34rem] lg:w-20 lg:flex-col lg:overflow-y-auto" role="tablist" aria-label="Photos">
            @foreach ($slides as $idx => $s)
                <button type="button" role="tab" x-on:click="i = {{ $idx }}" x-bind:aria-selected="i === {{ $idx }}" title="{{ $s['caption'] ?? $s['alt'] }}"
                        class="h-16 w-16 shrink-0 overflow-hidden rounded border-2 bg-brand-offwhite lg:h-20 lg:w-20" x-bind:class="i === {{ $idx }} ? 'border-brand-orange' : 'border-transparent hover:border-gray-300'">
                    <img src="{{ $s['url'] }}" alt="{{ $s['alt'] }}" loading="lazy" class="h-full w-full object-cover">
                </button>
            @endforeach
        </div>
    @endif

    <div class="relative flex-1">
        <div class="relative aspect-square overflow-hidden rounded-lg bg-brand-offwhite ring-1 ring-black/5" x-bind:class="current.placeholder ? '' : 'cursor-zoom-in'"
             x-on:click="open()" x-on:mouseenter="zoom = true" x-on:mouseleave="zoom = false" x-on:mousemove="move($event)">
            <img src="{{ $first['url'] }}" alt="{{ $first['alt'] }}" x-bind:src="current.url" x-bind:alt="current.alt" fetchpriority="high"
                 class="h-full w-full object-contain transition-transform duration-200"
                 x-bind:style="zoom && !current.placeholder ? 'transform: scale(1.9); transform-origin: ' + origin : ''">
            @if (! empty($badge))<span class="badge absolute left-3 top-3 bg-brand-orange text-brand-white">{{ $badge }}</span>@endif
            <span x-cloak x-show="!current.placeholder" class="pointer-events-none absolute bottom-3 right-3 flex items-center gap-1 rounded bg-brand-charcoal/70 px-2 py-1 text-[11px] font-bold text-brand-white"><x-icon name="zoom-in" class="h-3.5 w-3.5" />Click to enlarge</span>
        </div>
        @if ($many)
            <button type="button" x-on:click="prev()" aria-label="Previous photo" class="absolute left-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-brand-white/90 shadow hover:bg-brand-orange hover:text-brand-white"><x-icon name="chevron-left" class="h-5 w-5" /></button>
            <button type="button" x-on:click="next()" aria-label="Next photo" class="absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-brand-white/90 shadow hover:bg-brand-orange hover:text-brand-white"><x-icon name="chevron-right" class="h-5 w-5" /></button>
            <p x-cloak x-show="current.caption" class="mt-2 text-center text-xs font-semibold text-brand-graphite">Option: <span x-text="current.caption"></span></p>
        @endif
    </div>

    {{-- Full-screen lightbox --}}
    <div x-cloak x-show="lightbox" x-transition.opacity x-on:click.self="close()" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90 p-4" role="dialog" aria-modal="true" aria-label="Photo viewer">
        <button type="button" x-on:click="close()" aria-label="Close" class="absolute right-4 top-4 flex h-11 w-11 items-center justify-center rounded-full bg-brand-white/15 text-brand-white hover:bg-brand-orange"><x-icon name="x" class="h-6 w-6" /></button>
        @if ($many)
            <button type="button" x-on:click="prev()" aria-label="Previous photo" class="absolute left-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-brand-white/15 text-brand-white hover:bg-brand-orange"><x-icon name="chevron-left" class="h-6 w-6" /></button>
            <button type="button" x-on:click="next()" aria-label="Next photo" class="absolute right-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-brand-white/15 text-brand-white hover:bg-brand-orange"><x-icon name="chevron-right" class="h-6 w-6" /></button>
        @endif
        <figure class="max-h-full max-w-5xl text-center">
            <img x-bind:src="current.url" x-bind:alt="current.alt" class="mx-auto max-h-[80vh] w-auto rounded object-contain">
            <figcaption class="mt-3 text-sm text-gray-200"><span x-text="current.caption || current.alt"></span> <span class="text-gray-400" x-text="slides.length > 1 ? '(' + (i + 1) + ' / ' + slides.length + ')' : ''"></span></figcaption>
        </figure>
    </div>
</div>
