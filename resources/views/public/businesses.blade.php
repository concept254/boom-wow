@extends('layouts.app', ['title' => 'Our Businesses'])
@section('content')
<section class="section"><h1 class="text-4xl font-bold">Our businesses</h1>
<div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($businesses as $b)
<a href="{{ route('business', $b) }}" class="card group overflow-hidden"><img src="{{ $b->cover_url }}" alt="" loading="lazy" class="h-44 w-full object-cover"><div class="p-5"><h2 class="text-xl font-semibold group-hover:text-brand-orange">{{ $b->name }}</h2><p class="mt-1 text-sm text-brand-graphite">{{ $b->tagline }}</p></div></a>
@endforeach</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Not sure where to start?" text="Tell us what you need and we will point you to the right business." label="Contact us" url="/contact" label2="Browse services" url2="/services" />
@endsection
