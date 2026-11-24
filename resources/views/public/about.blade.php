@extends('layouts.app', ['title' => $page->meta_title ?? 'About Us', 'description' => $page->meta_description ?? null])
@section('content')
{{-- Banner --}}
<x-page-header slug="about" :title="$page->title ?? 'About Boom & Wow Holdings'" subtitle="One family of businesses, one customer account and one standard of service." eyebrow="About us">
    <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('businesses') }}" class="btn-primary">Explore our businesses</a><a href="{{ route('contact') }}" class="btn-outline-light">Contact us</a></div>
</x-page-header>

{{-- Story --}}
<section class="section">
    <div class="grid gap-10 lg:grid-cols-[3fr_2fr]">
        <div>
            <p class="eyebrow">Our story</p><h2 class="mt-2 text-3xl sm:text-4xl">Who we are</h2>
            <div class="mt-5 space-y-4 text-brand-graphite">
                @forelse ($paragraphs as $p)<p>{{ $p }}</p>@empty<p>Boom &amp; Wow Holdings operates a salon, a liquor store, IT consultants, property management and events management - bringing exceptional services, products and experiences together under one roof.</p>@endforelse
            </div>
        </div>
        <dl class="grid grid-cols-2 gap-4 self-start">
            @foreach ($stats as $label => $value)
                <div class="rounded-3xl bg-brand-offwhite p-6 text-center"><dd class="font-display text-4xl font-bold text-brand-orange">{{ $value }}</dd><dt class="mt-1 text-sm font-bold text-brand-graphite">{{ $label }}</dt></div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Values --}}
<section class="bg-brand-offwhite"><div class="section">
    <p class="eyebrow">Why choose us</p><h2 class="mt-2 text-3xl sm:text-4xl">What we stand for</h2>
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([['Quality first', 'Experienced teams and carefully chosen products in every business.'], ['One account', 'Book, order and enquire across every store with a single login.'], ['Always transparent', 'Clear pricing, clear availability, clear confirmations.'], ['Community minded', 'Local people serving local customers, responsibly.']] as [$t, $d])
            <div class="card p-6"><div class="h-1.5 w-10 rounded-full bg-brand-orange"></div><h3 class="mt-4 text-lg">{{ $t }}</h3><p class="mt-2 text-sm text-brand-graphite">{{ $d }}</p></div>
        @endforeach
    </div>
</div></section>

{{-- Our businesses --}}
<section class="section">
    <div class="flex items-end justify-between gap-4"><div><p class="eyebrow">Our businesses</p><h2 class="mt-2 text-3xl sm:text-4xl">Find the right store</h2></div><a href="{{ route('businesses') }}" class="text-sm font-bold text-brand-orange hover:text-brand-orange-dark">View all &rarr;</a></div>
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($businesses as $b)
            <a href="{{ route('business', $b) }}" class="card group overflow-hidden"><img src="{{ $b->cover_url }}" alt="" loading="lazy" class="h-36 w-full object-cover" onerror="this.style.display='none'"><div class="p-5"><h3 class="text-lg group-hover:text-brand-orange">{{ $b->name }}</h3><p class="mt-1 line-clamp-2 text-sm text-brand-graphite">{{ $b->tagline }}</p></div></a>
        @endforeach
    </div>
</section>

{{-- Brands --}}
@include('public.partials.brands-carousel')

{{-- Management & team (Admin > Team & Management) --}}
@if ($management->isNotEmpty() || $team->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section" x-data="{ tab: '{{ $management->isNotEmpty() ? 'management' : 'team' }}' }">
    <p class="eyebrow">Our people</p><h2 class="mt-2 text-3xl sm:text-4xl">Meet the team</h2>
    @if ($management->isNotEmpty() && $team->isNotEmpty())
        <div class="mt-6 inline-flex rounded-full bg-brand-white p-1 ring-1 ring-black/5" role="tablist">
            <button type="button" role="tab" @click="tab = 'management'" :aria-selected="tab === 'management'" class="rounded-full px-5 py-2 text-sm font-bold" :class="tab === 'management' ? 'bg-brand-orange text-brand-white' : 'text-brand-graphite'">Management</button>
            <button type="button" role="tab" @click="tab = 'team'" :aria-selected="tab === 'team'" class="rounded-full px-5 py-2 text-sm font-bold" :class="tab === 'team' ? 'bg-brand-orange text-brand-white' : 'text-brand-graphite'">Team</button>
        </div>
    @endif
    @foreach (['management' => $management, 'team' => $team] as $key => $people)
        @if ($people->isNotEmpty())
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4" x-show="tab === '{{ $key }}'" @if ($loop->index > 0) x-cloak @endif>
            @foreach ($people as $m)
                <article class="card p-6 text-center">
                    @if ($m->photo_url)<img src="{{ $m->photo_url }}" alt="{{ $m->name }}" loading="lazy" class="mx-auto h-28 w-28 rounded-full object-cover">
                    @else<div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-brand-orange font-display text-3xl font-bold text-brand-white" aria-hidden="true">{{ $m->initials }}</div>@endif
                    <h3 class="mt-4 text-lg">{{ $m->name }}</h3>
                    @if ($m->job_title)<p class="text-sm font-bold text-brand-orange">{{ $m->job_title }}</p>@endif
                    @if ($m->business)<p class="text-xs text-gray-500">{{ $m->business->name }}</p>@endif
                    @if ($m->bio)<p class="mt-3 text-sm text-brand-graphite">{{ $m->bio }}</p>@endif
                    <div class="mt-3 flex justify-center gap-3 text-sm font-bold">
                        @if ($m->email)<a href="mailto:{{ $m->email }}" class="text-brand-orange hover:text-brand-orange-dark">Email</a>@endif
                        @if ($m->linkedin_url)<a href="{{ \App\Support\Link::href($m->linkedin_url) }}" rel="noopener" class="text-brand-orange hover:text-brand-orange-dark">LinkedIn</a>@endif
                    </div>
                </article>
            @endforeach
        </div>
        @endif
    @endforeach
</div></section>
@endif

{{-- Testimonials --}}
@if ($testimonials->isNotEmpty())
<section class="section">
    <p class="eyebrow">Testimonials</p><h2 class="mt-2 text-3xl sm:text-4xl">Kind words</h2>
    <div class="mt-8 grid gap-5 md:grid-cols-3">@foreach ($testimonials as $t)<figure class="card p-6"><div class="text-brand-orange" aria-label="{{ $t->rating }} out of 5">{{ str_repeat('★', (int) $t->rating) }}</div><blockquote class="mt-2 text-brand-graphite">&ldquo;{{ $t->quote }}&rdquo;</blockquote><figcaption class="mt-4 text-sm font-bold">{{ $t->author_name }}<span class="block font-normal text-gray-500">{{ $t->author_role }}</span></figcaption></figure>@endforeach</div>
</section>
@endif

{{-- FAQs --}}
@if ($faqs->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section">
    <div class="grid gap-10 lg:grid-cols-[1fr_2fr]">
        <div><p class="eyebrow">FAQs</p><h2 class="mt-2 text-3xl sm:text-4xl">Good to know</h2><a href="{{ route('faqs') }}" class="btn-outline mt-5">See all FAQs</a></div>
        <div>@include('public.partials.faq-list', ['faqs' => $faqs])</div>
    </div>
</div></section>
@endif

<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Ready to experience Boom & Wow?" text="Book a service, shop online or send us an enquiry - we'd love to hear from you." label="Book a service" :url="route('portal.bookings.new')" label2="Contact us" :url2="route('contact')" />
@endsection
