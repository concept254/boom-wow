@extends('layouts.app', ['title' => $event->title])
@section('content')
<x-page-header :title="$event->title" :subtitle="$event->business->name" :crumbs="[['Events', route('events')]]" />
<section class="section max-w-4xl">
<p class="mt-2 text-brand-graphite">{{ $event->starts_at->format('l, j F Y \a\t H:i') }} · {{ $event->venue }} @if($event->capacity)· Capacity {{ $event->capacity }}@endif @if($event->price)· from R{{ number_format($event->price, 2) }}@endif</p>
<p class="mt-6 text-brand-graphite">{{ $event->description }}</p>
@if ($event->packages)<div class="mt-8 grid gap-4 sm:grid-cols-2">@foreach ($event->packages as $pk)<div class="card p-5"><h2 class="font-semibold">{{ $pk['name'] }}</h2><p class="text-brand-orange">R{{ number_format($pk['price'], 0) }}</p><p class="text-sm text-brand-graphite">{{ $pk['includes'] ?? '' }}</p></div>@endforeach</div>@endif
<h2 class="mt-12 text-2xl font-bold">Enquire about this event</h2><div class="mt-4">@include('public.partials.enquiry-form', ['type' => 'event', 'business' => $event->business, 'subject' => ['event', $event->id]])</div></section>
@endsection
