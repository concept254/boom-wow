@extends('layouts.app', ['title' => $business->meta_title ?: $business->name, 'description' => $business->meta_description ?: $business->tagline])
@section('content')
@php
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $products->each(fn ($p) => $p->setRelation('business', $business));
    $aboutText = $business->about_body ?: $business->description;
    $hasAbout = $business->show_about && ($aboutText || ! empty($business->highlights));
    $slides = $business->show_gallery ? $business->gallery_slides : [];
    $promoCount = $business->show_promos ? \App\Models\Promotion::forPlacement('business_page')->forBusiness($business->id)->count() : 0;
    $showTeam = $business->show_team && $business->staff->isNotEmpty();
    $nav = array_filter([
        'About' => $hasAbout ? '#about' : null,
        'Offers' => $promoCount ? '#offers' : null,
        'Gallery' => $slides ? '#gallery' : null,
        'Services' => $services->isNotEmpty() ? '#services' : null,
        'Products' => $products->isNotEmpty() ? '#products' : null,
        'Articles' => $posts->isNotEmpty() ? '#articles' : null,
        'Team' => $showTeam ? '#team' : null,
        'Contact' => '#contact',
    ]);
@endphp
<x-page-header :title="$business->name" :subtitle="$business->tagline" :image="$business->cover_url" :crumbs="[['Our businesses', route('businesses')]]">
    @if ($business->logo_url)<img src="{{ $business->logo_url }}" alt="{{ $business->name }} logo" class="mt-6 h-16 w-16 rounded bg-brand-white object-contain p-2">@endif
</x-page-header>

@if (count($nav) > 2)
<nav class="border-b border-gray-200 bg-brand-white" aria-label="On this page">
    <ul class="mx-auto flex max-w-7xl gap-6 overflow-x-auto whitespace-nowrap px-4 py-3 text-sm font-bold sm:px-6 lg:px-8">
        @foreach ($nav as $label => $href)<li><a href="{{ $href }}" class="text-brand-graphite hover:text-brand-orange">{{ $label }}</a></li>@endforeach
    </ul>
</nav>
@endif

@if ($promoCount)
<div id="offers" class="scroll-mt-28"><x-ad-slot placement="business_page" :business="$business->id" :limit="3" layout="cards" eyebrow="Offers" :title="'Current offers at '.$business->name" /></div>
@endif

@if ($hasAbout)
<section id="about" class="scroll-mt-28 {{ $promoCount ? 'bg-brand-offwhite' : '' }}">
    <div class="section grid gap-10 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <p class="eyebrow">Our story</p>
            <h2 class="mt-2 text-3xl sm:text-4xl">{{ $business->about_title ?: 'About '.$business->name }}</h2>
            <div class="mt-5 max-w-3xl space-y-4 text-brand-graphite">
                @foreach (preg_split('/\n\s*\n/', (string) $aboutText) as $para)@if (trim($para))<p>{!! nl2br(e(trim($para))) !!}</p>@endif @endforeach
            </div>
        </div>
        @if (! empty($business->highlights))
        <div class="card h-fit p-6">
            <h3 class="flex items-center gap-2 text-lg font-semibold"><x-icon name="award" class="h-5 w-5 text-brand-orange" />Why choose us</h3>
            <ul class="mt-4 space-y-3 text-sm">@foreach ($business->highlights as $point)<li class="flex items-start gap-2"><x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-brand-orange" />{{ $point }}</li>@endforeach</ul>
        </div>
        @endif
    </div>
</section>
@endif

@if ($slides)
<section id="gallery" class="scroll-mt-28"><div class="section">
    <p class="eyebrow">Gallery</p><h2 class="mt-2 text-3xl sm:text-4xl">Take a look inside</h2>
    @include('public.partials.business-gallery', ['slides' => $slides])
</div></section>
@endif

<div class="section grid gap-12 lg:grid-cols-3">
<div class="space-y-14 lg:col-span-2">
    @if ($business->age_restricted)<div class="rounded-md border border-brand-orange bg-brand-offwhite p-4 text-sm" role="note">{{ \App\Services\SettingsService::get('liquor_notice', 'Enjoy responsibly. 18+ only.') }}</div>@endif

    @if ($services->isNotEmpty())
    <div id="services" class="scroll-mt-28"><h2 class="text-2xl font-bold">Services</h2>
        <div class="mt-4 divide-y divide-gray-200 rounded-lg bg-brand-white ring-1 ring-black/5">@foreach ($services as $svc)
            <div class="flex flex-wrap items-center justify-between gap-3 p-4"><div><p class="font-semibold">{{ $svc->name }}</p><p class="text-sm text-brand-graphite">{{ $svc->duration_minutes }} min · {{ $svc->price_text }}</p></div>
            @if ($business->accepts_bookings && $svc->is_bookable)<a class="btn-primary" href="{{ route('portal.bookings.new', ['service' => $svc->id]) }}"><x-icon name="calendar-check" class="h-4 w-4" />Book</a>@endif</div>
        @endforeach</div></div>
    @endif

    @if ($packages->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Packages</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach ($packages as $pk)<div class="card p-5"><h3 class="font-semibold">{{ $pk->name }}</h3><p class="mt-1 text-brand-orange">R{{ number_format($pk->price, 2) }} <span class="text-xs text-gray-500">/ {{ $pk->billing }}</span></p><p class="mt-2 text-sm text-brand-graphite">{{ $pk->description }}</p><ul class="mt-3 list-disc pl-5 text-sm">@foreach ($pk->features ?? [] as $f)<li>{{ $f }}</li>@endforeach</ul></div>@endforeach</div></div>
    @endif

    @if ($products->isNotEmpty())
    <div id="products" class="scroll-mt-28"><h2 class="text-2xl font-bold">Products</h2><div class="mt-4 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">@foreach ($products as $p)@include('public.partials.product-card', ['product' => $p])@endforeach</div></div>
    @endif

    @if ($properties->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Properties</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach ($properties as $pr)<a href="{{ route('property', $pr) }}" class="card p-5"><p class="text-xs font-semibold uppercase text-brand-orange">{{ $pr->listing_type === 'rent' ? 'To rent' : 'For sale' }} · {{ ucfirst($pr->availability) }}</p><h3 class="mt-1 font-semibold">{{ $pr->title }}</h3><p class="text-sm text-brand-graphite">{{ $pr->suburb }} · R{{ number_format($pr->price, 0) }}</p></a>@endforeach</div></div>
    @endif

    @if ($events->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Upcoming events</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach ($events as $ev)<a href="{{ route('event', $ev) }}" class="card p-5"><p class="text-xs font-semibold uppercase text-brand-orange">{{ $ev->starts_at->format('D j M Y, H:i') }}</p><h3 class="mt-1 font-semibold">{{ $ev->title }}</h3><p class="text-sm text-brand-graphite">{{ $ev->venue }}</p></a>@endforeach</div></div>
    @endif

    @if ($portfolio->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Portfolio &amp; case studies</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach ($portfolio as $pi)<div class="card p-5"><p class="text-xs font-semibold uppercase text-brand-orange">{{ str_replace('_', ' ', $pi->kind) }}</p><h3 class="mt-1 font-semibold">{{ $pi->title }}</h3><p class="text-sm text-brand-graphite">{{ $pi->summary }}</p></div>@endforeach</div></div>
    @endif

    @if ($showTeam)
    <div id="team" class="scroll-mt-28"><h2 class="text-2xl font-bold">Our team</h2><div class="mt-4 grid gap-4 sm:grid-cols-3">@foreach ($business->staff as $st)<div class="card p-4 text-center"><p class="font-semibold">{{ $st->display_name }}</p><p class="text-sm text-brand-graphite">{{ $st->job_title }}</p></div>@endforeach</div></div>
    @endif

    <div id="contact" class="scroll-mt-28"><h2 class="text-2xl font-bold">Send an enquiry</h2><div class="mt-4">@include('public.partials.enquiry-form', ['type' => $business->enquiry_kind, 'business' => $business])</div></div>
</div>

<aside class="space-y-6">
    <div class="card p-5"><h2 class="flex items-center gap-2 text-lg font-semibold"><x-icon name="map-pin" class="h-5 w-5 text-brand-orange" />Contact</h2><ul class="mt-3 space-y-2 text-sm text-brand-graphite"><li class="flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 h-4 w-4 text-brand-orange" /><span>{{ $business->address }}, {{ $business->city }}</span></li><li class="flex items-center gap-2"><x-icon name="phone" class="h-4 w-4 text-brand-orange" />{{ $business->phone }}</li><li class="flex items-center gap-2"><x-icon name="mail" class="h-4 w-4 text-brand-orange" />{{ $business->email }}</li>
        @foreach (($business->social_links ?? []) as $name => $url)<li><a class="text-brand-orange hover:text-brand-orange-dark" rel="noopener" href="{{ $url }}">{{ ucfirst($name) }}</a></li>@endforeach</ul>
        @if ($business->latitude && $business->longitude)<a class="mt-3 inline-block text-sm text-brand-orange" rel="noopener" href="https://www.openstreetmap.org/?mlat={{ $business->latitude }}&mlon={{ $business->longitude }}#map=16/{{ $business->latitude }}/{{ $business->longitude }}">View on map</a>@endif</div>
    <div class="card p-5"><h2 class="flex items-center gap-2 text-lg font-semibold"><x-icon name="clock" class="h-5 w-5 text-brand-orange" />Opening hours</h2><table class="mt-3 w-full text-sm">@foreach ($business->hours as $h)<tr><td class="py-1">{{ $days[$h->day_of_week] }}</td><td class="py-1 text-right text-brand-graphite">{{ $h->is_closed ? 'Closed' : substr($h->opens_at, 0, 5).' – '.substr($h->closes_at, 0, 5) }}</td></tr>@endforeach</table></div>
</aside>
</div>
@if ($posts->isNotEmpty())
<section id="articles" class="scroll-mt-28 bg-brand-offwhite"><div class="section">
    <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="eyebrow">Read</p><h2 class="mt-2 text-3xl sm:text-4xl">News &amp; articles from {{ $business->name }}</h2></div>
        @if ($postCount > $posts->count())<a href="{{ route('blog', ['business' => $business->slug]) }}" class="inline-flex items-center gap-1 font-bold text-brand-orange hover:text-brand-orange-dark">All articles<x-icon name="arrow-right" class="h-4 w-4" /></a>@endif</div>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($posts as $post)@include('public.partials.post-card', ['post' => $post])@endforeach</div>
</div></section>
@endif
@include('public.partials.brands-carousel', ['business' => $business, 'heading' => 'Brands at '.$business->name, 'intro' => 'A selection of the brands you will find here.'])
<x-ad-slot placement="inner_banner" />
@endsection
