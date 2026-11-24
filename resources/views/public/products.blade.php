@extends('layouts.app', ['title' => 'Products'])
@section('content')
<x-page-header slug="products" title="Products" subtitle="Shop products from across all of our stores." />
<section class="section">
<form class="mt-6 flex flex-wrap gap-3" method="GET"><input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Search products"><select class="input max-w-xs" name="business"><option value="">All businesses</option>@foreach ($businesses as $b)<option value="{{ $b->slug }}" @selected(request('business') === $b->slug)>{{ $b->name }}</option>@endforeach</select><button class="btn-dark"><x-icon name="search" class="h-4 w-4" />Filter</button></form>
<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">@forelse ($products as $p)@include('public.partials.product-card', ['product' => $p])@empty<p>No products found.</p>@endforelse</div>
<div class="mt-8">{{ $products->links() }}</div></section>
@include('public.partials.brands-carousel', ['scope' => 'all'])
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Looking for something specific?" text="Ask our team - we'll check stock and help you find it." label="Contact us" url="/contact" label2="Our businesses" url2="/businesses" />
@endsection
