@extends('layouts.app', ['title' => 'Events'])
@section('content')
<x-page-header slug="events" title="Events" subtitle="What is coming up and how to get involved." />
<section class="section"><div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@forelse ($events as $ev)<a href="{{ route('event', $ev) }}" class="card p-5"><p class="text-xs font-semibold uppercase text-brand-orange">{{ $ev->starts_at->format('D j M Y, H:i') }}</p><h2 class="mt-1 text-lg font-semibold">{{ $ev->title }}</h2><p class="text-sm text-brand-graphite">{{ $ev->venue }} · {{ $ev->business->name }}</p></a>@empty<p>No upcoming events.</p>@endforelse</div><div class="mt-8">{{ $events->links() }}</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Planning your own event?" text="Talk to our events team about packages, venues and pricing." label="Plan my event" url="/contact" label2="Our businesses" url2="/businesses" />
@endsection
