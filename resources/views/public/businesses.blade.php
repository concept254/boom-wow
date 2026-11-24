@extends('layouts.app', ['title' => 'Our Businesses'])
@section('content')
<x-page-header slug="businesses" title="Our businesses" subtitle="Every business in the Boom & Wow family, with one account for all of them." />
<section class="section">
<div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($businesses as $b)
<a href="{{ route('business', $b) }}" class="card group overflow-hidden"><img src="{{ $b->cover_url }}" alt="" loading="lazy" class="h-44 w-full object-cover"><div class="p-5"><h2 class="text-xl font-semibold group-hover:text-brand-orange">{{ $b->name }}</h2><p class="mt-1 text-sm text-brand-graphite">{{ $b->tagline }}</p></div></a>
@endforeach</div></section>
@include('public.partials.brands-carousel', ['scope' => 'all'])
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Not sure where to start?" text="Tell us what you need and we will point you to the right business." label="Contact us" url="/contact" label2="Browse services" url2="/services" />
@endsection
