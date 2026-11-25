@extends('layouts.app', ['title' => 'Services'])
@section('content')
<x-page-header slug="services" title="Services" subtitle="Book beauty, IT, property and event services with trusted professionals." />
<section class="section">
<form class="mt-6 flex flex-wrap gap-3" method="GET"><input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Search services"><select class="input max-w-xs" name="business"><option value="">All businesses</option>@foreach ($businesses as $b)<option value="{{ $b->slug }}" @selected(request('business') === $b->slug)>{{ $b->name }}</option>@endforeach</select><button class="btn-dark"><x-icon name="search" class="h-4 w-4" />Filter</button></form>
<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($services as $svc)@include('public.partials.service-card', ['svc' => $svc])@endforeach</div>
<div class="mt-8">{{ $services->links() }}</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Can't find the service you need?" text="Send us an enquiry and the right team will get back to you." label="Send an enquiry" url="/contact" label2="Our businesses" url2="/businesses" />
@endsection
