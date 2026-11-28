@extends('layouts.app', ['title' => $service->name.' | '.$business->name, 'description' => \Illuminate\Support\Str::limit(strip_tags((string) $service->description), 155)])
@php
    $url = route('service', $service);
    $slides = $service->gallery_slides;
    $bookable = $service->is_bookable && $business->accepts_bookings;
    $wa = preg_replace('/\D/', '', (string) \App\Services\SettingsService::get('whatsapp_number', ''));
    $ask = 'https://wa.me/'.$wa.'?text='.rawurlencode("Hi, I'd like to know more about {$service->name} ".$url);
    $ld = [
        '@context' => 'https://schema.org', '@type' => 'Service', 'name' => $service->name, 'url' => $url,
        'image' => array_column($slides, 'url'), 'description' => strip_tags((string) $service->description),
        'provider' => ['@type' => 'Organization', 'name' => $business->name],
        'offers' => $service->price_on_request ? null : ['@type' => 'Offer', 'priceCurrency' => 'ZAR', 'price' => number_format((float) $service->price, 2, '.', '')],
    ];
    if ($stats['count'] > 0) {
        $ld['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $stats['avg'], 'reviewCount' => $stats['count']];
    }
    $ld = array_filter($ld, fn ($v) => $v !== null && $v !== '');
@endphp
@push('head')
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $service->name }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:image" content="{{ $slides[0]['url'] }}">
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
<x-page-header :title="$service->name" :crumbs="[['Services', route('services')], [$business->name, route('business', $business)]]" />

<div x-data="{ tab: (location.hash === '#reviews') ? 'reviews' : 'description' }">
<section class="section grid gap-10 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
    @include('public.partials.gallery', ['slides' => $slides])
    <div>
        <a href="{{ route('business', $business) }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-orange hover:text-brand-orange-dark"><x-icon :name="$business->icon_name" class="h-4 w-4" />{{ $business->name }}</a>
        <a href="#reviews" x-on:click="tab = 'reviews'" class="mt-3 flex items-center gap-2 hover:opacity-80">
            @if ($stats['count'] > 0)<x-rating-stars :value="$stats['avg']" size="h-5 w-5" /><span class="text-sm font-bold">{{ number_format($stats['avg'], 1) }}</span><span class="text-sm text-brand-graphite underline">{{ $stats['count'] }} {{ \Illuminate\Support\Str::plural('review', $stats['count']) }}</span>
            @else<span class="text-sm text-brand-graphite underline">Be the first to review</span>@endif
        </a>
        <p class="mt-5 font-display text-4xl font-bold text-brand-charcoal">{{ $service->price_text }}</p>
        <p class="mt-2 flex items-center gap-2 text-sm text-brand-graphite"><x-icon name="clock" class="h-4 w-4 text-brand-orange" />About {{ $service->duration_minutes }} minutes</p>

        @if (! empty($service->highlights))
            <ul class="mt-5 space-y-2 text-sm">@foreach ($service->highlights as $point)<li class="flex items-start gap-2"><x-icon name="check-circle" class="mt-0.5 h-4 w-4 text-brand-orange" />{{ $point }}</li>@endforeach</ul>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            @if ($service->price_on_request)<a href="{{ $service->quote_url }}" class="btn-primary !px-8 !py-3 text-base"><x-icon name="message" class="h-5 w-5" />Request a quote</a>
            @elseif ($bookable)<a href="{{ route('portal.bookings.new', ['service' => $service->id]) }}" class="btn-primary !px-8 !py-3 text-base"><x-icon name="calendar-check" class="h-5 w-5" />Book now</a>
            @else<a href="{{ route('contact') }}" class="btn-primary !px-8 !py-3 text-base"><x-icon name="message" class="h-5 w-5" />Send an enquiry</a>@endif
            @if ($wa)<a href="{{ $ask }}" target="_blank" rel="noopener" class="btn-outline !px-6 !py-3"><x-icon name="whatsapp" class="h-5 w-5" />Ask on WhatsApp</a>@endif
        </div>

        <ul class="mt-6 grid gap-3 rounded bg-brand-offwhite p-4 text-sm sm:grid-cols-3">
            <li class="flex items-center gap-2"><x-icon name="calendar-check" class="h-5 w-5 shrink-0 text-brand-orange" /><span class="font-semibold">Easy online booking</span></li>
            <li class="flex items-center gap-2"><x-icon name="lock" class="h-5 w-5 shrink-0 text-brand-orange" /><span class="font-semibold">Secure and private</span></li>
            <li class="flex items-center gap-2"><x-icon name="message" class="h-5 w-5 shrink-0 text-brand-orange" /><span class="font-semibold">Friendly help from our team</span></li>
        </ul>

        @if ($service->staff->isNotEmpty())
            <div class="mt-5"><p class="label">Our specialists</p><div class="flex flex-wrap gap-2">@foreach ($service->staff as $s)<span class="badge bg-brand-offwhite text-brand-charcoal">{{ $s->display_name ?? $s->user?->name }}</span>@endforeach</div></div>
        @endif
        <div class="mt-5">@include('public.partials.share', ['url' => $url, 'title' => $service->name.' - '.$business->name])</div>
    </div>
</section>

<section class="section !pt-0">
    <div class="flex gap-1 overflow-x-auto border-b border-gray-200 text-sm font-bold" role="tablist">
        <button type="button" role="tab" x-on:click="tab = 'description'" class="whitespace-nowrap border-b-2 px-5 py-3" x-bind:class="tab === 'description' ? 'border-brand-orange text-brand-orange' : 'border-transparent text-brand-graphite hover:text-brand-charcoal'">About this service</button>
        <button type="button" role="tab" x-on:click="tab = 'reviews'" class="whitespace-nowrap border-b-2 px-5 py-3" x-bind:class="tab === 'reviews' ? 'border-brand-orange text-brand-orange' : 'border-transparent text-brand-graphite hover:text-brand-charcoal'">Reviews ({{ $stats['count'] }})</button>
    </div>
    <div class="pt-6">
        <div x-show="tab === 'description'" class="max-w-3xl text-brand-graphite">@if ($service->description){!! nl2br(e($service->description)) !!}@else<p>More details coming soon. Ask our team anything.</p>@endif</div>
        <div x-cloak x-show="tab === 'reviews'">@include('public.partials.reviews', ['item' => $service, 'kind' => 'service'])</div>
    </div>
</section>
</div>

@if ($related->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section">
    <h2 class="text-2xl sm:text-3xl">Related services</h2>
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach ($related as $svc)@include('public.partials.service-card', ['svc' => $svc])@endforeach</div>
</div></section>
@endif
@endsection
