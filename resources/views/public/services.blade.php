@extends('layouts.app', ['title' => 'Services'])
@section('content')
<x-page-header slug="services" title="Services" subtitle="Book beauty, IT, property and event services with trusted professionals." />
<section class="section">
<form class="mt-6 flex flex-wrap gap-3" method="GET"><input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Search services"><select class="input max-w-xs" name="business"><option value="">All businesses</option>@foreach ($businesses as $b)<option value="{{ $b->slug }}" @selected(request('business') === $b->slug)>{{ $b->name }}</option>@endforeach</select><button class="btn-dark"><x-icon name="search" class="h-4 w-4" />Filter</button></form>
<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($services as $svc)<div class="card p-5"><p class="flex items-center gap-1.5 text-xs font-semibold uppercase text-brand-orange"><x-icon :name="$svc->business->icon_name" class="h-4 w-4" />{{ $svc->business->name }}</p><h2 class="mt-1 text-lg font-semibold">{{ $svc->name }}</h2><p class="mt-2 text-sm text-brand-graphite">{{ $svc->price_label }} R{{ number_format($svc->price, 2) }} · {{ $svc->duration_minutes }} min</p>@if($svc->is_bookable && $svc->business->accepts_bookings)<a href="{{ route('portal.bookings.new', ['service' => $svc->id]) }}" class="btn-primary mt-4"><x-icon name="calendar-check" class="h-4 w-4" />Book now</a>@else<a href="{{ route('business', $svc->business) }}" class="btn-outline mt-4"><x-icon name="message" class="h-4 w-4" />Enquire</a>@endif</div>@endforeach</div>
<div class="mt-8">{{ $services->links() }}</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Can't find the service you need?" text="Send us an enquiry and the right team will get back to you." label="Send an enquiry" url="/contact" label2="Our businesses" url2="/businesses" />
@endsection
