@extends('layouts.app', ['title' => 'Properties'])
@section('content')
<x-page-header slug="properties" title="Properties" subtitle="Homes and commercial spaces to rent or buy." />
<section class="section">
<div class="mt-4 flex gap-3 text-sm"><a class="btn-outline" href="{{ route('properties') }}">All</a><a class="btn-outline" href="{{ route('properties', ['listing' => 'rent']) }}">To rent</a><a class="btn-outline" href="{{ route('properties', ['listing' => 'sale']) }}">For sale</a></div>
<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@forelse ($properties as $p)<a href="{{ route('property', $p) }}" class="card p-5"><p class="text-xs font-semibold uppercase text-brand-orange">{{ $p->listing_type === 'rent' ? 'To rent' : 'For sale' }} · {{ ucfirst($p->availability) }}</p><h2 class="mt-1 text-lg font-semibold">{{ $p->title }}</h2><p class="text-sm text-brand-graphite">{{ $p->suburb }}, {{ $p->city }}</p><p class="mt-2 font-semibold">R{{ number_format($p->price, 0) }}{{ $p->listing_type === 'rent' ? ' / month' : '' }}</p></a>@empty<p>No properties listed.</p>@endforelse</div><div class="mt-8">{{ $properties->links() }}</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Want to list or view a property?" text="Our property team can arrange viewings and answer your questions." label="Contact property team" url="/contact" label2="Our businesses" url2="/businesses" />
@endsection
