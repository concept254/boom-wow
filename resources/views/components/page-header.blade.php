@props(['title', 'subtitle' => null, 'crumbs' => [], 'slug' => null, 'image' => null, 'eyebrow' => null])
@php
    // Admin > Page Headers can override the title/subtitle and set a background image per page. Everything is optional:
    // an explicit $image wins, then the page's own image, then the site-wide default / online fallback (unless the
    // page's "use fallback" box is unticked, which gives a plain dark banner).
    $row = \App\Models\PageHeader::for($slug);
    $default = \App\Models\PageHeader::for('default');
    $heading = $row?->title ?: $title;
    $sub = $row?->subtitle ?: $subtitle;
    $src = $image ?: $row?->ownImage();
    if (! $src && (! $row || $row->use_fallback)) {
        $src = $default?->ownImage();
        $fallbackUrl = config('boomwow.page_header.fallback_url');
        if (! $src && $fallbackUrl) {
            $src = str_replace('{seed}', preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($slug ?: 'page'))), $fallbackUrl);
        }
    }
    $trail = array_merge([['Home', route('home')]], $crumbs, [[$heading, null]]);
@endphp
<section class="relative isolate overflow-hidden border-b-4 border-brand-orange bg-brand-charcoal text-brand-white">
    @if ($src)
        <img src="{{ $src }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" onerror="this.remove()">
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-charcoal/95 via-brand-charcoal/80 to-brand-charcoal/50"></div>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-gray-300">
                @foreach ($trail as $i => $c)
                    <li class="flex items-center gap-2">
                        @if ($i > 0)<span aria-hidden="true" class="text-brand-orange">/</span>@endif
                        @if ($c[1] && ! $loop->last)
                            <a href="{{ $c[1] }}" class="inline-flex items-center gap-1.5 hover:text-brand-white">@if ($i === 0)<x-icon name="home" class="h-3.5 w-3.5" />@endif{{ $c[0] }}</a>
                        @else
                            <span class="text-brand-white" @if ($loop->last) aria-current="page" @endif>{{ $c[0] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
        @if ($eyebrow)<p class="eyebrow mt-5">{{ $eyebrow }}</p>@endif
        <h1 class="{{ $eyebrow ? 'mt-2' : 'mt-5' }} max-w-3xl text-3xl !text-brand-white sm:text-5xl">{{ $heading }}</h1>
        @if ($sub)<p class="mt-3 max-w-2xl text-base text-gray-200 sm:text-lg">{{ $sub }}</p>@endif
        {{ $slot }}
    </div>
</section>
