@extends('layouts.app', ['title' => $property->title])
@section('content')
<section class="section max-w-4xl"><a class="eyebrow" href="{{ route('business', $property->business) }}">{{ $property->business->name }}</a><h1 class="mt-2 text-4xl font-bold">{{ $property->title }}</h1>
<p class="mt-2 text-xl font-semibold">R{{ number_format($property->price, 0) }}{{ $property->listing_type === 'rent' ? ' / month' : '' }} <span class="badge ml-2 bg-brand-offwhite text-brand-graphite">{{ ucfirst($property->availability) }}</span></p>
<p class="mt-2 text-sm text-brand-graphite">{{ ucfirst($property->property_type) }} · {{ $property->bedrooms ?? '–' }} bed · {{ $property->bathrooms ?? '–' }} bath · {{ $property->floor_area ?? '–' }} m² · {{ $property->suburb }}, {{ $property->city }}</p>
<p class="mt-6 text-brand-graphite">{{ $property->description }}</p>
<h2 class="mt-12 text-2xl font-bold">Request a viewing</h2><div class="mt-4">@include('public.partials.enquiry-form', ['type' => 'viewing', 'business' => $property->business, 'subject' => ['property', $property->id]])</div></section>
@endsection
