@extends('layouts.app', ['title' => $business->meta_title ?: $business->name, 'description' => $business->meta_description ?: $business->tagline])
@section('content')
@php($days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])
<x-page-header :title="$business->name" :subtitle="$business->tagline" :image="$business->cover_url" :crumbs="[['Our businesses', route('businesses')]]">
    @if ($business->logo_url)<img src="{{ $business->logo_url }}" alt="{{ $business->name }} logo" class="mt-6 h-16 w-16 rounded bg-brand-white object-contain p-2">@endif
</x-page-header>

<div class="section grid gap-12 lg:grid-cols-3">
<div class="space-y-14 lg:col-span-2">
    <div><h2 class="text-2xl font-bold">About</h2><p class="mt-3 text-brand-graphite">{{ $business->description }}</p></div>

    @if ($business->age_restricted)<div class="rounded-md border border-brand-orange bg-brand-offwhite p-4 text-sm" role="note">{{ \App\Services\SettingsService::get('liquor_notice', 'Enjoy responsibly. 18+ only.') }}</div>@endif

    @if ($services->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Services</h2>
        <div class="mt-4 divide-y divide-gray-200 rounded-lg bg-brand-white ring-1 ring-black/5">@foreach ($services as $svc)
            <div class="flex flex-wrap items-center justify-between gap-3 p-4"><div><p class="font-semibold">{{ $svc->name }}</p><p class="text-sm text-brand-graphite">{{ $svc->duration_minutes }} min · {{ $svc->price_label }} R{{ number_format($svc->price, 2) }}</p></div>
            @if ($business->accepts_bookings && $svc->is_bookable)<a class="btn-primary" href="{{ route('portal.bookings.new', ['service' => $svc->id]) }}">Book</a>@endif</div>
        @endforeach</div></div>
    @endif

    @if ($packages->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Packages</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach ($packages as $pk)<div class="card p-5"><h3 class="font-semibold">{{ $pk->name }}</h3><p class="mt-1 text-brand-orange">R{{ number_format($pk->price, 2) }} <span class="text-xs text-gray-500">/ {{ $pk->billing }}</span></p><p class="mt-2 text-sm text-brand-graphite">{{ $pk->description }}</p><ul class="mt-3 list-disc pl-5 text-sm">@foreach ($pk->features ?? [] as $f)<li>{{ $f }}</li>@endforeach</ul></div>@endforeach</div></div>
    @endif

    @if ($products->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Products</h2><div class="mt-4 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">@foreach ($products as $p)@php($p->setRelation('business', $business))@include('public.partials.product-card', ['product' => $p])@endforeach</div></div>
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

    @if ($business->staff->isNotEmpty())
    <div><h2 class="text-2xl font-bold">Our team</h2><div class="mt-4 grid gap-4 sm:grid-cols-3">@foreach ($business->staff as $st)<div class="card p-4 text-center"><p class="font-semibold">{{ $st->display_name }}</p><p class="text-sm text-brand-graphite">{{ $st->job_title }}</p></div>@endforeach</div></div>
    @endif

    <div><h2 class="text-2xl font-bold">Send an enquiry</h2><div class="mt-4">@include('public.partials.enquiry-form', ['type' => $business->enquiry_kind, 'business' => $business])</div></div>
</div>

<aside class="space-y-6">
    <div class="card p-5"><h2 class="text-lg font-semibold">Contact</h2><ul class="mt-3 space-y-1 text-sm text-brand-graphite"><li>{{ $business->address }}, {{ $business->city }}</li><li>{{ $business->phone }}</li><li>{{ $business->email }}</li>
        @foreach (($business->social_links ?? []) as $name => $url)<li><a class="text-brand-orange hover:text-brand-orange-dark" rel="noopener" href="{{ $url }}">{{ ucfirst($name) }}</a></li>@endforeach</ul>
        @if ($business->latitude && $business->longitude)<a class="mt-3 inline-block text-sm text-brand-orange" rel="noopener" href="https://www.openstreetmap.org/?mlat={{ $business->latitude }}&mlon={{ $business->longitude }}#map=16/{{ $business->latitude }}/{{ $business->longitude }}">View on map</a>@endif</div>
    <div class="card p-5"><h2 class="text-lg font-semibold">Opening hours</h2><table class="mt-3 w-full text-sm">@foreach ($business->hours as $h)<tr><td class="py-1">{{ $days[$h->day_of_week] }}</td><td class="py-1 text-right text-brand-graphite">{{ $h->is_closed ? 'Closed' : substr($h->opens_at, 0, 5).' – '.substr($h->closes_at, 0, 5) }}</td></tr>@endforeach</table></div>
</aside>
</div>
@include('public.partials.brands-carousel', ['business' => $business, 'heading' => 'Brands at '.$business->name, 'intro' => 'A selection of the brands you will find here.'])
<x-ad-slot placement="inner_banner" />
@endsection
