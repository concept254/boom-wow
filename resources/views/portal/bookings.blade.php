@extends('layouts.app', ['title' => 'My bookings'])
@section('content')
@include('portal.nav')
<section class="section"><h1 class="text-3xl font-bold">My bookings</h1>
<div class="mt-6 divide-y divide-gray-200 rounded-lg ring-1 ring-black/5">
@forelse ($bookings as $b)<a href="{{ route('portal.bookings.show', $b) }}" class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-brand-offwhite"><span><strong>{{ $b->service?->name }}</strong> · {{ $b->business?->name }}<br><span class="text-sm text-brand-graphite">{{ $b->reference }} · {{ $b->starts_at->format('D d M Y, H:i') }}</span></span><x-status-badge :status="$b->status" /></a>
@empty<p class="p-6 text-brand-graphite">No bookings yet. <a class="text-brand-orange" href="{{ route('portal.bookings.new') }}">Make your first booking</a>.</p>@endforelse
</div><div class="mt-4">{{ $bookings->links() }}</div></section>
@endsection
