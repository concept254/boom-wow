{{-- Photo grid with full-screen viewer. Vars: $slides (list of url/alt/caption). --}}
<div x-data="galleryGrid(@js($slides))" x-on:keydown.window.escape="close()" x-on:keydown.window.arrow-right="open && next()" x-on:keydown.window.arrow-left="open && prev()">
    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach ($slides as $idx => $s)
            <button type="button" x-on:click="show({{ $idx }})" aria-label="View photo {{ $idx + 1 }}{{ $s['caption'] ? ': '.$s['caption'] : '' }}"
                    class="group relative overflow-hidden rounded-lg bg-brand-offwhite ring-1 ring-black/5 {{ $idx === 0 && count($slides) >= 5 ? 'col-span-2 row-span-2' : '' }}">
                <img src="{{ $s['url'] }}" alt="{{ $s['alt'] }}" loading="lazy" class="aspect-square h-full w-full object-cover transition duration-300 group-hover:scale-105">
                @if ($s['caption'])<span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-3 text-left text-xs font-semibold text-brand-white">{{ $s['caption'] }}</span>@endif
            </button>
        @endforeach
    </div>

    <div x-cloak x-show="open" x-transition.opacity x-on:click.self="close()" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90 p-4" role="dialog" aria-modal="true" aria-label="Photo viewer">
        <button type="button" x-on:click="close()" aria-label="Close" class="absolute right-4 top-4 flex h-11 w-11 items-center justify-center rounded-full bg-brand-white/15 text-brand-white hover:bg-brand-white/30"><x-icon name="x" class="h-5 w-5" /></button>
        @if (count($slides) > 1)
            <button type="button" x-on:click="prev()" aria-label="Previous photo" class="absolute left-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-brand-white/15 text-brand-white hover:bg-brand-white/30"><x-icon name="chevron-left" class="h-6 w-6" /></button>
            <button type="button" x-on:click="next()" aria-label="Next photo" class="absolute right-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-brand-white/15 text-brand-white hover:bg-brand-white/30"><x-icon name="chevron-right" class="h-6 w-6" /></button>
        @endif
        <figure class="relative min-h-[12rem] min-w-[12rem] max-h-full max-w-5xl">
            <img x-bind:src="current.url" x-bind:alt="current.alt" data-spinner class="max-h-[80vh] w-auto rounded object-contain">
            <figcaption x-show="current.caption" x-text="current.caption" class="mt-3 text-center text-sm text-brand-white"></figcaption>
        </figure>
    </div>
</div>
