@extends('layouts.app', ['title' => 'Booking '.$booking->reference])
@section('content')
@include('portal.nav')
<section class="section max-w-3xl">
    <p class="eyebrow">Booking reference</p><h1 class="font-mono text-3xl font-bold">{{ $booking->reference }}</h1>
    <div class="card mt-6 space-y-2 p-6 text-sm">
        <p><strong>Status:</strong> <x-status-badge :status="$booking->status" /></p>
        <p><strong>Service:</strong> {{ $booking->service?->name }} ({{ $booking->service?->duration_minutes }} min)</p>
        <p><strong>Business:</strong> {{ $booking->business?->name }}@if ($booking->business?->address) · {{ $booking->business->address }}@endif</p>
        <p><strong>When:</strong> {{ $booking->starts_at->format('l d F Y, H:i') }} – {{ $booking->ends_at->format('H:i') }}</p>
        @if ($booking->staff)<p><strong>With:</strong> {{ $booking->staff->display_name }}</p>@endif
        @if ($booking->notes)<p><strong>Your notes:</strong> {{ $booking->notes }}</p>@endif
    </div>
    @if (in_array($booking->status, ['pending', 'confirmed', 'reschedule_requested']))
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @if (in_array($booking->status, ['pending', 'confirmed']))
        <form method="POST" action="{{ route('portal.bookings.reschedule', $booking) }}" class="card space-y-3 p-5">@csrf<label class="label" for="rn">Request a different time</label><textarea id="rn" name="reschedule_note" rows="3" class="input" required maxlength="500" placeholder="Preferred dates and times"></textarea>@error('reschedule_note')<p class="text-xs text-red-600">{{ $message }}</p>@enderror<button class="btn-outline">Request reschedule</button></form>
        @endif
        <form method="POST" action="{{ route('portal.bookings.cancel', $booking) }}" class="card p-5" onsubmit="return confirm('Cancel this booking?')">@csrf<p class="label">Can't make it?</p><button class="btn-dark">Cancel booking</button></form>
    </div>
    @endif
    <h2 class="mt-8 text-xl font-bold">History</h2>
    <ul class="mt-2 space-y-1 text-sm text-brand-graphite">@foreach ($booking->histories as $h)<li>{{ $h->created_at->format('d M Y H:i') }} — {{ str_replace('_', ' ', $h->to_status) }}@if ($h->note) ({{ $h->note }})@endif</li>@endforeach</ul>
</section>
@endsection
