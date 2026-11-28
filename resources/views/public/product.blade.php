@extends('layouts.app', ['title' => $product->name.' | '.$business->name, 'description' => $product->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 155)])
@php
    $url = route('product', [$business, $product->slug]);
    $slides = $product->gallery_slides;
    $qty = $product->track_stock ? (int) ($product->inventory?->quantity ?? 0) : null;
    $low = $qty !== null && $qty > 0 && $qty <= 5;
    $maxQty = $qty !== null ? max(1, min(99, $qty)) : 99;
    $onSale = $product->discount_percent > 0;
    $onRequest = (bool) $product->price_on_request;
    $restricted = $product->is_age_restricted || $business->age_restricted;
    $wa = preg_replace('/\D/', '', (string) \App\Services\SettingsService::get('whatsapp_number', ''));
    $ask = 'https://wa.me/'.$wa.'?text='.rawurlencode("Hi, I'd like to know more about {$product->name} ".$url);
    $ld = [
        '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name, 'sku' => $product->sku, 'url' => $url,
        'image' => array_column($slides, 'url'), 'description' => $product->short_description ?: strip_tags((string) $product->description),
        'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand] : null,
        'offers' => $product->price_on_request ? null : ['@type' => 'Offer', 'priceCurrency' => 'ZAR', 'price' => number_format((float) $product->current_price, 2, '.', ''), 'url' => $url,
            'availability' => 'https://schema.org/'.($product->in_stock ? 'InStock' : 'OutOfStock'), 'seller' => ['@type' => 'Organization', 'name' => $business->name]],
    ];
    if ($stats['count'] > 0) {
        $ld['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $stats['avg'], 'reviewCount' => $stats['count']];
    }
    $ld = array_filter($ld, fn ($v) => $v !== null && $v !== '');
@endphp
@push('head')
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $product->name }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:image" content="{{ $slides[0]['url'] }}">
    <meta property="og:description" content="{{ $product->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 155) }}">
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
<x-page-header :title="$product->name" :crumbs="[['Products', route('products')], [$business->name, route('business', $business)]]" />

<div x-data="{ tab: (location.hash === '#reviews') ? 'reviews' : 'description' }">
<section class="section grid gap-10 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
    @include('public.partials.gallery', ['slides' => $slides, 'badge' => $onSale ? 'Save '.$product->discount_percent.'%' : null])

    {{-- Buy box --}}
    <div>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
            <a href="{{ route('business', $business) }}" class="inline-flex items-center gap-1.5 font-bold text-brand-orange hover:text-brand-orange-dark"><x-icon :name="$business->icon_name" class="h-4 w-4" />{{ $business->name }}</a>
            @if ($product->brand)<span class="text-brand-graphite">Brand: <strong>{{ $product->brand }}</strong></span>@endif
        </div>

        <a href="#reviews" x-on:click="tab = 'reviews'" class="mt-3 inline-flex items-center gap-2 hover:opacity-80">
            @if ($stats['count'] > 0)<x-rating-stars :value="$stats['avg']" size="h-5 w-5" /><span class="text-sm font-bold">{{ number_format($stats['avg'], 1) }}</span><span class="text-sm text-brand-graphite underline">{{ $stats['count'] }} {{ \Illuminate\Support\Str::plural('review', $stats['count']) }}</span>
            @else<span class="text-sm text-brand-graphite underline">Be the first to review</span>@endif
        </a>

        @if ($onRequest)
            <div class="mt-5"><span class="badge bg-brand-charcoal text-brand-white">Exclusive</span>
                <p class="mt-3 font-display text-3xl font-bold text-brand-charcoal">Price on request</p>
                <p class="mt-1 text-sm text-brand-graphite">This is an exclusive item. Send us an enquiry and we will come back to you with the price and availability.</p></div>
        @else
        <div class="mt-5 flex flex-wrap items-end gap-x-3 gap-y-1">
            <span class="font-display text-4xl font-bold text-brand-charcoal">R{{ number_format($product->current_price, 2) }}</span>
            @if ($onSale)<span class="pb-1 text-lg text-gray-400 line-through">R{{ number_format($product->price, 2) }}</span><span class="badge mb-1 bg-brand-orange text-brand-white">Save {{ $product->discount_percent }}%</span>@endif
        </div>
        @if ($onSale)<p class="mt-1 text-sm font-semibold text-green-700">You save R{{ number_format((float) $product->price - (float) $product->current_price, 2) }}@if ($product->promo_ends_at) &middot; offer ends {{ $product->promo_ends_at->format('j M Y') }}@endif</p>@endif
        @endif

        @if ($product->short_description)<p class="mt-4 text-brand-graphite">{{ $product->short_description }}</p>@endif

        @if (! empty($product->highlights))
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($product->highlights as $point)<li class="flex items-start gap-2"><x-icon name="check-circle" class="mt-0.5 h-4 w-4 text-brand-orange" />{{ $point }}</li>@endforeach
            </ul>
        @endif

        <div class="mt-5">
            @if ($onRequest)
                <p class="flex items-center gap-2 font-bold text-brand-graphite"><x-icon name="star" class="h-5 w-5 text-brand-orange" />Available on request</p>
            @elseif (! $product->in_stock)
                <p class="flex items-center gap-2 font-bold text-red-600"><x-icon name="alert" class="h-5 w-5" />Currently out of stock</p>
            @elseif ($low)
                <p class="flex items-center gap-2 font-bold text-brand-orange-dark"><x-icon name="clock" class="h-5 w-5" />Only {{ $qty }} left in stock - order soon</p>
            @else
                <p class="flex items-center gap-2 font-bold text-green-700"><x-icon name="check-circle" class="h-5 w-5" />In stock</p>
            @endif
        </div>

        @if ($restricted)<p class="mt-4 flex items-start gap-2 rounded border border-brand-orange bg-brand-offwhite p-3 text-sm"><x-icon name="alert" class="mt-0.5 h-4 w-4 text-brand-orange" />{{ \App\Services\SettingsService::get('liquor_notice', 'Enjoy responsibly. 18+ only.') }}</p>@endif

        @if ($onRequest)
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ $product->quote_url }}" class="btn-primary !px-8 !py-3 text-base"><x-icon name="message" class="h-5 w-5" />Request a quote</a>
            </div>
        @elseif ($product->in_stock)
            <form method="POST" action="{{ route('cart.add', $product) }}" x-data="qtyStepper({{ $maxQty }})" class="mt-6">
                @csrf
                <p class="label">Quantity</p>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center rounded-full ring-1 ring-gray-300">
                        <button type="button" x-on:click="dec()" aria-label="Decrease quantity" class="flex h-11 w-11 items-center justify-center rounded-full hover:bg-brand-offwhite"><x-icon name="minus" class="h-4 w-4" /></button>
                        <input type="number" name="quantity" x-model.number="qty" x-on:change="clamp()" min="1" max="{{ $maxQty }}" aria-label="Quantity" class="w-12 border-0 bg-transparent p-0 text-center text-sm font-bold focus:ring-0">
                        <button type="button" x-on:click="inc()" aria-label="Increase quantity" class="flex h-11 w-11 items-center justify-center rounded-full hover:bg-brand-offwhite"><x-icon name="plus" class="h-4 w-4" /></button>
                    </div>
                    <button class="btn-primary !px-8 !py-3 text-base"><x-icon name="cart" class="h-5 w-5" />Add to cart</button>
                    <button name="buy_now" value="1" class="btn-dark !px-8 !py-3 text-base"><x-icon name="credit-card" class="h-5 w-5" />Buy now</button>
                </div>
            </form>
        @endif

        @if ($wa)<a href="{{ $ask }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-brand-graphite hover:text-green-600"><x-icon name="whatsapp" class="h-5 w-5" />Questions? Ask us on WhatsApp</a>@endif

        <ul class="mt-6 grid gap-3 rounded bg-brand-offwhite p-4 text-sm sm:grid-cols-3">
            <li class="flex items-center gap-2"><x-icon name="lock" class="h-5 w-5 shrink-0 text-brand-orange" /><span class="font-semibold">{{ $onRequest ? 'Private and confidential' : 'Secure checkout' }}</span></li>
            <li class="flex items-center gap-2"><x-icon name="truck" class="h-5 w-5 shrink-0 text-brand-orange" /><span class="font-semibold">Delivery or in-store collection</span></li>
            <li class="flex items-center gap-2"><x-icon name="message" class="h-5 w-5 shrink-0 text-brand-orange" /><span class="font-semibold">Friendly help from our team</span></li>
        </ul>

        <div class="mt-5 flex items-center gap-3 rounded border border-gray-200 p-3 text-sm">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-orange/10 text-brand-orange"><x-icon :name="$business->icon_name" class="h-5 w-5" /></span>
            <div class="flex-1"><p class="text-xs text-gray-500">Sold by</p><p class="font-bold">{{ $business->name }}</p></div>
            <a href="{{ route('business', $business) }}" class="inline-flex items-center gap-1 font-bold text-brand-orange hover:text-brand-orange-dark">Visit store<x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>

        <div class="mt-5">@include('public.partials.share', ['url' => $url, 'title' => $product->name.' - '.$business->name])</div>
    </div>
</section>

{{-- Details / reviews --}}
<section class="section !pt-0">
    <div class="flex gap-1 overflow-x-auto border-b border-gray-200 text-sm font-bold" role="tablist">
        <button type="button" role="tab" x-on:click="tab = 'description'" class="whitespace-nowrap border-b-2 px-5 py-3" x-bind:class="tab === 'description' ? 'border-brand-orange text-brand-orange' : 'border-transparent text-brand-graphite hover:text-brand-charcoal'">Description</button>
        <button type="button" role="tab" x-on:click="tab = 'details'" class="whitespace-nowrap border-b-2 px-5 py-3" x-bind:class="tab === 'details' ? 'border-brand-orange text-brand-orange' : 'border-transparent text-brand-graphite hover:text-brand-charcoal'">Product details</button>
        <button type="button" role="tab" x-on:click="tab = 'reviews'" class="whitespace-nowrap border-b-2 px-5 py-3" x-bind:class="tab === 'reviews' ? 'border-brand-orange text-brand-orange' : 'border-transparent text-brand-graphite hover:text-brand-charcoal'">Reviews ({{ $stats['count'] }})</button>
    </div>
    <div class="pt-6">
        <div x-show="tab === 'description'" class="max-w-3xl text-brand-graphite">
            @if ($product->description){!! nl2br(e($product->description)) !!}@else<p>No description has been added for this product yet.</p>@endif
        </div>
        <div x-cloak x-show="tab === 'details'" class="max-w-xl">
            <dl class="divide-y divide-gray-200 text-sm">
                @foreach (array_filter(['Brand' => $product->brand, 'Size' => $product->size, 'Category' => $product->category?->name, 'Unit' => $product->unit, 'SKU' => $product->sku, 'Sold by' => $business->name]) as $label => $value)
                    <div class="grid grid-cols-3 gap-4 py-3"><dt class="font-bold text-brand-graphite">{{ $label }}</dt><dd class="col-span-2">{{ $value }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div x-cloak x-show="tab === 'reviews'">
            @include('public.partials.reviews', ['item' => $product, 'kind' => 'product'])
        </div>
    </div>
</section>
</div>

@if ($related->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section">
    <h2 class="text-2xl sm:text-3xl">You may also like</h2>
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach ($related as $p)@include('public.partials.product-card', ['product' => $p])@endforeach</div>
</div></section>
@endif

@if ($recent->isNotEmpty())
<section class="section">
    <h2 class="text-2xl sm:text-3xl">Recently viewed</h2>
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach ($recent as $p)@include('public.partials.product-card', ['product' => $p])@endforeach</div>
</section>
@endif

{{-- Sticky add-to-cart bar on phones once the buy box has scrolled away --}}
@if ($product->is_purchasable)
<div x-data="{ show: false }" x-on:scroll.window.passive="show = window.scrollY > 700" x-cloak x-show="show" x-transition class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-brand-white p-3 pr-20 shadow-[0_-4px_12px_rgba(0,0,0,.08)] lg:hidden">
    <form method="POST" action="{{ route('cart.add', $product) }}" class="mx-auto flex max-w-xl items-center gap-3">@csrf<input type="hidden" name="quantity" value="1">
        <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold">{{ $product->name }}</p><p class="text-sm font-bold text-brand-orange">R{{ number_format($product->current_price, 2) }}</p></div>
        <button class="btn-primary !px-5 !py-2.5"><x-icon name="cart" class="h-4 w-4" />Add to cart</button>
    </form>
</div>
@endif
@endsection
