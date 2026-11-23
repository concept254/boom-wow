@extends('layouts.admin', ['title' => 'Bookings'])
@section('content')
<form method="GET" class="mb-4 flex flex-wrap items-end gap-2" role="search">
    <div><label class="label" for="q">Reference</label><input id="q" name="q" value="{{ request('q') }}" class="input"></div>
    <div><label class="label" for="status">Status</label><select id="status" name="status" class="input"><option value="">All</option>@foreach (\App\Models\Booking::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach</select></div>
    <div><label class="label" for="date">Date</label><input id="date" type="date" name="date" value="{{ request('date') }}" class="input"></div>
    <button class="btn-dark">Filter</button>
</form>
<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
<table class="min-w-full text-sm"><thead class="bg-brand-charcoal text-left text-xs uppercase text-brand-white"><tr><th class="px-4 py-3">Ref</th><th class="px-4 py-3">When</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Service</th><th class="px-4 py-3">Business</th><th class="px-4 py-3">Staff</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Update</th></tr></thead>
<tbody class="divide-y divide-gray-100">
@forelse ($bookings as $b)
<tr class="align-top"><td class="px-4 py-3 font-mono text-xs">{{ $b->reference }}</td><td class="px-4 py-3">{{ $b->starts_at->format('d M Y H:i') }}</td><td class="px-4 py-3">{{ $b->user?->name }}<br><span class="text-xs text-brand-graphite">{{ $b->user?->phone }}</span></td><td class="px-4 py-3">{{ $b->service?->name }}</td><td class="px-4 py-3">{{ $b->business?->name }}</td><td class="px-4 py-3">{{ $b->staff?->display_name ?? '—' }}</td><td class="px-4 py-3"><x-status-badge :status="$b->status" />@if ($b->reschedule_note)<p class="mt-1 max-w-[14rem] text-xs text-brand-graphite">“{{ $b->reschedule_note }}”</p>@endif</td>
<td class="px-4 py-3"><form method="POST" action="{{ route('admin.bookings.update', $b->id) }}" class="flex gap-1">@csrf @method('PATCH')<select name="status" class="input py-1" aria-label="New status">@foreach (\App\Models\Booking::STATUSES as $s)<option value="{{ $s }}" @selected($b->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach</select><button class="btn-dark px-3 py-1">Save</button></form></td></tr>
@empty<tr><td colspan="8" class="px-4 py-10 text-center text-brand-graphite">No bookings found.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
