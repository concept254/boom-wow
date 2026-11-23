@extends('layouts.app', ['title' => 'My account'])
@section('content')
@include('portal.nav')
<section class="section">
    <h1 class="text-3xl font-bold">Welcome, {{ auth()->user()->first_name }}</h1>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="card p-6"><h2 class="text-xl font-bold">Upcoming appointments</h2>
            @forelse ($upcoming as $b)<a href="{{ route('portal.bookings.show', $b) }}" class="mt-3 flex items-center justify-between border-t pt-3 text-sm"><span><strong>{{ $b->service?->name }}</strong> · {{ $b->business?->name }}<br>{{ $b->starts_at->format('D d M Y, H:i') }}</span><x-status-badge :status="$b->status" /></a>@empty<p class="mt-3 text-sm text-brand-graphite">No upcoming appointments. <a class="text-brand-orange" href="{{ route('portal.bookings.new') }}">Book one</a>.</p>@endforelse</div>
        <div class="card p-6"><h2 class="text-xl font-bold">Recent orders</h2>
            @forelse ($orders as $o)<a href="{{ route('portal.orders.show', $o) }}" class="mt-3 flex items-center justify-between border-t pt-3 text-sm"><span><strong>{{ $o->order_number }}</strong><br>{{ $o->created_at->format('d M Y') }} · R{{ number_format($o->total, 2) }}</span><x-status-badge :status="$o->status" /></a>@empty<p class="mt-3 text-sm text-brand-graphite">No orders yet. <a class="text-brand-orange" href="{{ route('products') }}">Browse products</a>.</p>@endforelse</div>
        <div class="card p-6"><h2 class="text-xl font-bold">Recent bookings</h2>
            @forelse ($recentBookings as $b)<p class="mt-3 flex items-center justify-between border-t pt-3 text-sm"><span>{{ $b->reference }} · {{ $b->service?->name }} · {{ $b->starts_at->format('d M Y') }}</span><x-status-badge :status="$b->status" /></p>@empty<p class="mt-3 text-sm text-brand-graphite">No bookings yet.</p>@endforelse</div>
        <div class="card p-6"><h2 class="text-xl font-bold">My enquiries</h2>
            @forelse ($enquiries as $e)<p class="mt-3 flex items-center justify-between border-t pt-3 text-sm"><span>{{ $e->subject_line ?? ucfirst($e->type) }} · {{ $e->created_at->format('d M Y') }}</span><x-status-badge :status="$e->status" /></p>@empty<p class="mt-3 text-sm text-brand-graphite">No enquiries.</p>@endforelse</div>
        <div class="card p-6 lg:col-span-2"><div class="flex items-center justify-between"><h2 class="text-xl font-bold">Notifications</h2>@if ($notifications->whereNull('read_at')->count())<form method="POST" action="{{ route('portal.notifications.read') }}">@csrf<button class="text-sm text-brand-orange hover:underline">Mark all read</button></form>@endif</div>
            @forelse ($notifications as $n)<p class="mt-3 border-t pt-3 text-sm {{ $n->read_at ? 'text-brand-graphite' : 'font-medium' }}">{{ $n->data['title'] ?? 'Notification' }} — {{ $n->data['message'] ?? '' }} <span class="text-xs text-brand-graphite">{{ $n->created_at->diffForHumans() }}</span></p>@empty<p class="mt-3 text-sm text-brand-graphite">You're all caught up.</p>@endforelse</div>
    </div>
</section>
@endsection
