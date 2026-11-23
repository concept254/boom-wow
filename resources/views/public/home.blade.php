@extends('layouts.app')
@section('content')
@php($s = fn ($k, $d = null) => \App\Services\SettingsService::get($k, $d))

{{-- 1. Hero slideshow (managed in Admin > Hero Slides) + global search --}}
@include('public.partials.hero-slider', ['slides' => $slides, 'heroImage' => $heroImage])
<div class="relative z-20 mx-auto -mt-10 max-w-4xl px-4 sm:px-6">
    <form action="{{ route('search') }}" method="GET" role="search" class="rounded-3xl bg-brand-white p-3 shadow-xl ring-1 ring-black/5">
        <div class="flex flex-col gap-2 sm:flex-row">
            <label class="sr-only" for="hero-q">Search services, products, events and properties</label>
            <input id="hero-q" name="q" type="search" minlength="2" required placeholder="Search services, products, events, properties..." class="input !border-0 !shadow-none !ring-0 sm:flex-1 !py-3 text-base">
            <button class="btn-primary !py-3">Search</button>
        </div>
        <div class="flex flex-wrap items-center gap-2 px-2 pb-1 pt-2 text-xs">
            <span class="font-bold text-brand-graphite">Popular:</span>
            <a class="chip hover:bg-brand-orange hover:text-brand-white" href="{{ route('portal.bookings.new') }}">Book a service</a>
            <a class="chip hover:bg-brand-orange hover:text-brand-white" href="{{ route('products') }}">Shop products</a>
            <a class="chip hover:bg-brand-orange hover:text-brand-white" href="{{ route('events') }}">Events</a>
            <a class="chip hover:bg-brand-orange hover:text-brand-white" href="{{ route('properties') }}">Properties</a>
        </div>
    </form>
</div>

{{-- 2. Featured stores carousel --}}
@if ($stores->isNotEmpty())
<section class="mt-6" x-data="{ scroll(d) { const t = $refs.track; t.scrollBy({ left: d * t.clientWidth * 0.8, behavior: 'smooth' }); } }">
    <div class="section">
        <div class="flex items-end justify-between gap-4">
            <div><p class="eyebrow">Our stores</p><h2 class="mt-2 text-3xl sm:text-4xl">Five businesses. Endless choice.</h2>
                <p class="mt-2 max-w-2xl text-brand-graphite">From salon pampering and wine to IT, property and events - find the right store under one roof.</p></div>
            <div class="hidden gap-2 sm:flex">
                <button type="button" @click="scroll(-1)" class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-offwhite text-xl hover:bg-brand-orange hover:text-brand-white" aria-label="Previous stores">&#8249;</button>
                <button type="button" @click="scroll(1)" class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-offwhite text-xl hover:bg-brand-orange hover:text-brand-white" aria-label="Next stores">&#8250;</button>
            </div>
        </div>
        <div x-ref="track" class="no-scrollbar -mx-4 mt-8 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6" tabindex="0" aria-label="Featured stores">
            @foreach ($stores as $b)
                @php($open = $b->isOpenNow())
                <article class="card group relative w-72 shrink-0 snap-start overflow-hidden sm:w-80">
                    <div class="relative">
                        <img src="{{ $b->cover_url }}" alt="" loading="lazy" class="h-44 w-full object-cover transition duration-300 group-hover:scale-105" onerror="this.style.display='none'">
                        @if ($open !== null)<span class="badge absolute left-3 top-3 {{ $open ? 'bg-green-600 text-brand-white' : 'bg-brand-charcoal text-brand-white' }}">{{ $open ? 'Open now' : 'Closed now' }}</span>@endif
                    </div>
                    <div class="p-5">
                        <div class="-mt-12 mb-2 flex h-14 z-10 absolute w-14 items-center justify-center overflow-hidden rounded-2xl bg-brand-white shadow ring-1 ring-black/5">
                            @if ($b->logo_url)<img src="{{ $b->logo_url }}" alt="" class="h-full w-full object-contain p-1">@else<span class="font-display text-xl font-bold text-brand-orange">{{ mb_substr($b->name, 0, 1) }}</span>@endif
                        </div>
                        <h3 class="text-xl mt-4"><a href="{{ route('business', $b) }}" class="after:absolute after:inset-0 hover:text-brand-orange">{{ $b->name }}</a></h3>
                        <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-sm text-brand-graphite">{{ $b->tagline }}</p>
                        @php($h = $b->todayHours())
                        <p class="mt-3 text-xs font-bold text-brand-graphite">Today: {{ $h ? ($h->is_closed ? 'Closed' : substr($h->opens_at, 0, 5).' - '.substr($h->closes_at, 0, 5)) : 'See store page' }}</p>
                        <div class="relative z-10 mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('business', $b) }}" class="btn-primary !px-4 !py-2">Visit store</a>
                            @if ($b->accepts_bookings)<a href="{{ route('portal.bookings.new', ['business' => $b->id]) }}" class="btn-outline !px-4 !py-2">Book</a>@endif
                            @if ($b->sells_products)<a href="{{ route('products', ['business' => $b->slug]) }}" class="btn-outline !px-4 !py-2">Shop</a>@endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-4 text-center"><a href="{{ route('businesses') }}" class="text-sm font-bold text-brand-orange hover:text-brand-orange-dark">See all businesses &rarr;</a></div>
    </div>
</section>
@endif

{{-- 3. Featured services --}}
@if ($services->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section">
    <div class="flex items-end justify-between gap-4"><div><p class="eyebrow">Featured services</p><h2 class="mt-2 text-3xl sm:text-4xl">Book with us</h2></div><a href="{{ route('services') }}" class="text-sm font-bold text-brand-orange hover:text-brand-orange-dark">All services &rarr;</a></div>
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($services as $svc)
            <div class="card flex flex-col p-6">
                <span class="chip self-start !bg-brand-white">{{ $svc->business->name }}</span>
                <h3 class="mt-3 text-xl">{{ $svc->name }}</h3>
                @if ($svc->summary ?? false)<p class="mt-1 line-clamp-2 text-sm text-brand-graphite">{{ $svc->summary }}</p>@endif
                <p class="mt-3 text-sm font-bold text-brand-graphite"><span class="text-lg text-brand-orange">{{ $svc->price_label }} R{{ number_format($svc->price, 2) }}</span> &middot; {{ $svc->duration_minutes }} min</p>
                <a href="{{ route('portal.bookings.new', ['service' => $svc->id]) }}" class="btn-primary mt-5 self-start">Book now</a>
            </div>
        @endforeach
    </div>
</div></section>
@endif

{{-- 4. At-a-glance: numbers, opening hours today, find us --}}
<section class="bg-brand-charcoal text-brand-white"><div class="section">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-brand-graphite p-7">
            <p class="eyebrow">At a glance</p><h2 class="mt-2 text-2xl !text-brand-white">Boom &amp; Wow in numbers</h2>
            <dl class="mt-6 grid grid-cols-2 gap-5">
                @foreach ($stats as $label => $value)
                    <div><dd class="font-display text-4xl font-bold text-brand-orange">{{ $value }}</dd><dt class="text-sm text-gray-300">{{ $label }}</dt></div>
                @endforeach
            </dl>
        </div>
        <div class="rounded-3xl bg-brand-graphite p-7">
            <p class="eyebrow">Opening hours</p><h2 class="mt-2 text-2xl !text-brand-white">Open today</h2>
            <ul class="mt-5 space-y-3 text-sm">
                @foreach ($businesses as $b)
                    @php($h = $b->todayHours())
                    <li class="flex items-center justify-between gap-3 border-b border-brand-charcoal/60 pb-2 last:border-0">
                        <span class="flex items-center gap-2 font-bold"><span class="h-2.5 w-2.5 rounded-full {{ $b->isOpenNow() ? 'bg-green-500' : 'bg-gray-500' }}" aria-hidden="true"></span>{{ $b->name }}</span>
                        <span class="text-gray-300">{{ $h ? ($h->is_closed ? 'Closed' : substr($h->opens_at, 0, 5).' - '.substr($h->closes_at, 0, 5)) : '-' }}</span>
                    </li>
                @endforeach
            </ul>
            @if ($s('trading_note'))<p class="mt-4 text-xs text-gray-400">{{ $s('trading_note') }}</p>@endif
        </div>
        <div class="rounded-3xl bg-brand-orange p-7 text-brand-white">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-white/80">Find us</p><h2 class="mt-2 text-2xl !text-brand-white">Come say hello</h2>
            <ul class="mt-5 space-y-2 text-sm font-semibold">
                @if ($s('contact_address'))<li>{{ $s('contact_address') }}</li>@endif
                @if ($s('contact_phone'))<li><a href="tel:{{ preg_replace('/[^+\d]/', '', $s('contact_phone')) }}" class="underline underline-offset-2">{{ $s('contact_phone') }}</a></li>@endif
                @if ($s('contact_email'))<li><a href="mailto:{{ $s('contact_email') }}" class="underline underline-offset-2">{{ $s('contact_email') }}</a></li>@endif
            </ul>
            <div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('contact') }}" class="btn-light">Contact &amp; map</a>
                @if ($s('whatsapp_number'))<a href="https://wa.me/{{ preg_replace('/\D/', '', $s('whatsapp_number')) }}" rel="noopener" class="btn-outline-light">WhatsApp us</a>@endif</div>
        </div>
    </div>
</div></section>

{{-- 5. Specials & adverts (Admin > Promotions & Adverts) --}}
<x-ad-slot placement="home_specials" :limit="6" layout="cards" eyebrow="Specials" title="This month's specials & offers" />
<x-ad-slot placement="home_banner" :limit="1" />

{{-- Featured products --}}
@if ($products->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section">
    <div class="flex items-end justify-between gap-4"><div><p class="eyebrow">Featured products</p><h2 class="mt-2 text-3xl sm:text-4xl">Shop favourites</h2></div><a href="{{ route('products') }}" class="text-sm font-bold text-brand-orange hover:text-brand-orange-dark">All products &rarr;</a></div>
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach ($products as $p)@include('public.partials.product-card', ['product' => $p])@endforeach</div>
</div></section>
@endif

{{-- 6. Subscription --}}
<section id="subscribe" class="bg-brand-orange text-brand-white"><div class="section !py-12">
    <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-3xl !text-brand-white sm:text-4xl">Never miss a special</h2>
        <p class="mt-2 text-brand-white/90">Join our list for offers, events and news from all our businesses. No spam - unsubscribe any time.</p>
        <div class="text-left">@include('public.partials.subscribe-form')</div>
    </div>
</div></section>

{{-- 7. Short about --}}
<section class="section">
    <div class="grid items-center gap-10 lg:grid-cols-2">
        <div>
            <p class="eyebrow">About us</p><h2 class="mt-2 text-3xl sm:text-4xl">One family of businesses</h2>
            <p class="mt-4 text-brand-graphite">{{ $aboutExcerpt }}</p>
            <ul class="mt-5 grid gap-3 text-sm font-bold sm:grid-cols-3">
                @foreach (['One account for every business', 'Quality you can trust', 'Clear pricing & availability'] as $point)
                    <li class="rounded-2xl bg-brand-offwhite px-4 py-3"><span class="text-brand-orange">&#10003;</span> {{ $point }}</li>
                @endforeach
            </ul>
            <div class="mt-7 flex flex-wrap gap-3"><a href="{{ route('about') }}" class="btn-primary">Our story</a><a href="{{ route('contact') }}" class="btn-outline">Get in touch</a></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            @foreach ($businesses->take(4) as $k => $b)
                <img src="{{ $b->cover_url }}" alt="{{ $b->name }}" loading="lazy" class="h-44 w-full rounded-3xl object-cover sm:h-52 {{ $k % 2 ? 'mt-6' : '' }}" onerror="this.style.display='none'">
            @endforeach
        </div>
    </div>
</section>

{{-- 8. FAQs --}}
@if ($faqs->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section">
    <div class="grid gap-10 lg:grid-cols-[1fr_2fr]">
        <div><p class="eyebrow">FAQs</p><h2 class="mt-2 text-3xl sm:text-4xl">Quick answers</h2><p class="mt-3 text-brand-graphite">Can't find what you need? Our team is happy to help.</p>
            <div class="mt-5 flex flex-wrap gap-3"><a href="{{ route('faqs') }}" class="btn-outline">All FAQs</a><a href="{{ route('contact') }}" class="btn-primary">Ask us</a></div></div>
        <div>@include('public.partials.faq-list', ['faqs' => $faqs])</div>
    </div>
</div></section>
@endif

{{-- Testimonials --}}
@if ($testimonials->isNotEmpty())
<section class="section">
    <p class="eyebrow">Testimonials</p><h2 class="mt-2 text-3xl sm:text-4xl">What clients say</h2>
    <div class="mt-8 grid gap-5 md:grid-cols-3">@foreach ($testimonials as $t)<figure class="card p-6"><div class="text-brand-orange" aria-label="{{ $t->rating }} out of 5">{{ str_repeat('★', (int) $t->rating) }}</div><blockquote class="mt-2 text-brand-graphite">&ldquo;{{ $t->quote }}&rdquo;</blockquote><figcaption class="mt-4 text-sm font-bold">{{ $t->author_name }}<span class="block font-normal text-gray-500">{{ $t->author_role }}</span></figcaption></figure>@endforeach</div>
</section>
@endif

{{-- Contact --}}
<section class="bg-brand-offwhite"><div class="section grid gap-10 lg:grid-cols-2">
    <div><p class="eyebrow">Contact</p><h2 class="mt-2 text-3xl sm:text-4xl">Let's talk</h2><p class="mt-4 text-brand-graphite">{{ $s('contact_address') }}<br>{{ $s('contact_phone') }}<br>{{ $s('contact_email') }}</p>
        <a href="{{ route('contact') }}" class="btn-outline mt-6">Directions &amp; opening hours</a></div>
    @include('public.partials.enquiry-form', ['type' => 'general', 'business' => null])
</div></section>
@endsection
