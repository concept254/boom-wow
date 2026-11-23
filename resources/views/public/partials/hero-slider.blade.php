{{-- Hero slideshow. $slides: collection of HeroSlide (saved or transient), $heroImage: fallback photo for slides without an image. --}}
<section class="relative isolate bg-brand-charcoal text-brand-white" x-data="heroSlider({{ $slides->count() }})" @mouseenter="paused = true" @mouseleave="paused = false" @focusin="paused = true" @focusout="paused = false"
         role="group" aria-roledescription="carousel" aria-label="Featured highlights">
    <div class="relative min-h-[34rem] sm:min-h-[40rem]">
        @foreach ($slides as $idx => $slide)
            <div class="absolute inset-0" x-show="i === {{ $idx }}" @if ($idx > 0) x-cloak @endif
                 x-transition:enter="transition-opacity duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 role="group" aria-roledescription="slide" aria-label="{{ $idx + 1 }} of {{ $slides->count() }}">
                <img src="{{ $slide->hasImage() ? $slide->src : $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover" @if ($idx > 0) loading="lazy" @else fetchpriority="high" @endif onerror="this.style.display='none'">
                <div class="absolute inset-0 bg-brand-charcoal/65"></div>
                <div class="relative mx-auto flex h-full max-w-7xl items-center px-4 pb-24 pt-16 sm:px-6 lg:px-8">
                    <div class="max-w-2xl">
                        @if ($slide->caption)<span class="chip !bg-brand-orange !text-brand-white">{{ $slide->caption }}</span>@endif
                        <h2 class="mt-4 text-4xl font-bold leading-tight !text-brand-white sm:text-5xl lg:text-6xl">{{ $slide->heading }}</h2>
                        @if ($slide->subheading)<p class="mt-5 max-w-xl text-lg text-gray-100">{{ $slide->subheading }}</p>@endif
                        <div class="mt-8 flex flex-wrap gap-3">
                            @if ($slide->cta_label)<a href="{{ \App\Support\Link::href($slide->cta_url) }}" class="btn-primary !px-7 !py-3 text-base">{{ $slide->cta_label }}</a>@endif
                            @if ($slide->cta2_label)<a href="{{ \App\Support\Link::href($slide->cta2_url) }}" class="btn-outline-light !px-7 !py-3 text-base">{{ $slide->cta2_label }}</a>@endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if ($slides->count() > 1)
            <div class="absolute inset-x-0 bottom-20 z-10 mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex gap-2" role="tablist" aria-label="Choose slide">
                    @foreach ($slides as $idx => $slide)
                        <button type="button" role="tab" @click="go({{ $idx }})" :aria-selected="i === {{ $idx }}" aria-label="Slide {{ $idx + 1 }}"
                                class="h-2.5 rounded-full transition-all" :class="i === {{ $idx }} ? 'w-8 bg-brand-orange' : 'w-2.5 bg-brand-white/60 hover:bg-brand-white'"></button>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="prev()" class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-white/15 text-xl hover:bg-brand-orange" aria-label="Previous slide">&#8249;</button>
                    <button type="button" @click="next()" class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-white/15 text-xl hover:bg-brand-orange" aria-label="Next slide">&#8250;</button>
                </div>
            </div>
        @endif
    </div>
</section>
