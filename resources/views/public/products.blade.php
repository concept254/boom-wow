@extends('layouts.app', ['title' => 'Products'])
@section('content')
<section class="section"><h1 class="text-4xl font-bold">Products</h1>
<form class="mt-6 flex flex-wrap gap-3" method="GET"><input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Search products"><select class="input max-w-xs" name="business"><option value="">All businesses</option>@foreach ($businesses as $b)<option value="{{ $b->slug }}" @selected(request('business') === $b->slug)>{{ $b->name }}</option>@endforeach</select><button class="btn-dark">Filter</button></form>
<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">@forelse ($products as $p)@include('public.partials.product-card', ['product' => $p])@empty<p>No products found.</p>@endforelse</div>
<div class="mt-8">{{ $products->links() }}</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Looking for something specific?" text="Ask our team - we'll check stock and help you find it." label="Contact us" url="/contact" label2="Our businesses" url2="/businesses" />
@endsection
