@extends('layouts.app', ['title' => 'Search'])
@section('content')
<x-page-header slug="search" title="Search" subtitle="Find businesses, services, products, events and properties." />
<section class="section">
<form class="mt-6 flex max-w-xl gap-3" method="GET" role="search"><input class="input" type="search" name="q" value="{{ $term }}" minlength="2" required aria-label="Search"><button class="btn-primary">Search</button></form>
@if (strlen($term) < 2)<p class="mt-8 text-brand-graphite">Enter at least two characters.</p>
@elseif (collect($results)->flatten()->isEmpty())<p class="mt-8">No results for “{{ $term }}”.</p>
@else
<div class="mt-10 space-y-10">
@foreach ([['businesses', 'Businesses'], ['services', 'Services'], ['products', 'Products'], ['events', 'Events'], ['properties', 'Properties']] as [$k, $label])
@if ($results[$k]->isNotEmpty())<div><h2 class="text-xl font-semibold">{{ $label }}</h2><ul class="mt-3 divide-y divide-gray-200 rounded-lg ring-1 ring-black/5">
@foreach ($results[$k] as $r)
@php
 $link = match($k){ 'businesses' => route('business', $r), 'products' => route('product', [$r->business, $r->slug]), 'events' => route('event', $r), 'properties' => route('property', $r), default => route('business', $r->business) };
 $name = $r->name ?? $r->title;
@endphp
<li class="flex items-center justify-between p-4"><a class="font-medium hover:text-brand-orange" href="{{ $link }}">{{ $name }}</a>@if ($k !== 'businesses')<span class="badge bg-brand-offwhite text-brand-graphite">{{ $r->business->name }}</span>@endif</li>
@endforeach</ul></div>@endif
@endforeach</div>@endif</section>
@endsection
