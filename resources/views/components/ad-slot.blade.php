@props(['placement' => 'inner_banner', 'limit' => 1, 'layout' => 'banner', 'title' => null, 'eyebrow' => null])
@php
    $ads = \App\Models\Promotion::forPlacement($placement)->limit($limit)->get();
    $themes = [
        'orange' => 'bg-brand-orange text-brand-white',
        'charcoal' => 'bg-brand-charcoal text-brand-white',
        'light' => 'bg-brand-offwhite text-brand-charcoal ring-1 ring-black/5',
    ];
@endphp
@if ($ads->isNotEmpty())
    @if ($layout === 'cards')
        <section class="section">
            @if ($title)<p class="eyebrow">{{ $eyebrow ?? 'Specials' }}</p><h2 class="mt-2 text-3xl sm:text-4xl">{{ $title }}</h2>@endif
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($ads as $ad)
                    <article class="{{ $themes[$ad->theme] ?? $themes['orange'] }} relative flex flex-col overflow-hidden rounded-3xl shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                        @if ($ad->image_url)<img src="{{ $ad->image_url }}" alt="" loading="lazy" class="h-40 w-full object-cover">@endif
                        <div class="flex flex-1 flex-col p-6">
                            @if ($ad->badge)<span class="badge self-start bg-brand-white text-brand-orange">{{ $ad->badge }}</span>@endif
                            <h3 class="mt-2 text-xl !text-inherit">{{ $ad->title }}</h3>
                            @if ($ad->description)<p class="mt-2 text-sm opacity-90">{{ $ad->description }}</p>@endif
                            @if ($ad->ends_on)<p class="mt-3 flex items-center gap-1.5 text-xs font-bold opacity-80"><x-icon name="clock" class="h-3.5 w-3.5" />Ends {{ $ad->ends_on->format('d M Y') }}</p>@endif
                            @if ($ad->link_url)<a href="{{ \App\Support\Link::href($ad->link_url) }}" class="btn-light mt-auto self-start !mt-5">{{ $ad->cta_label ?: 'Learn more' }}<x-icon name="arrow-right" class="h-4 w-4" /></a>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @else
        <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" aria-label="Advertisement">
            @foreach ($ads as $ad)
                <div class="{{ $themes[$ad->theme] ?? $themes['orange'] }} flex flex-col gap-5 overflow-hidden rounded-3xl p-6 shadow-sm sm:p-8 md:flex-row md:items-center">
                    @if ($ad->image_url)<img src="{{ $ad->image_url }}" alt="" loading="lazy" class="h-32 w-full rounded-2xl object-cover md:h-36 md:w-60">@endif
                    <div class="flex-1">
                        @if ($ad->badge)<span class="badge bg-brand-white text-brand-orange">{{ $ad->badge }}</span>@endif
                        <h3 class="mt-1 text-2xl !text-inherit">{{ $ad->title }}</h3>
                        @if ($ad->description)<p class="mt-1 max-w-2xl text-sm opacity-90">{{ $ad->description }}</p>@endif
                    </div>
                    @if ($ad->link_url)<a href="{{ \App\Support\Link::href($ad->link_url) }}" class="btn-light shrink-0">{{ $ad->cta_label ?: 'Learn more' }}<x-icon name="arrow-right" class="h-4 w-4" /></a>@endif
                </div>
            @endforeach
        </section>
    @endif
@endif
