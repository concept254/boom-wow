@extends('layouts.admin', ['title' => 'Orders'])
@section('content')
<form method="GET" class="mb-4 flex flex-wrap items-end gap-2" role="search">
    <div><label class="label" for="q">Order # or customer</label><input id="q" name="q" value="{{ request('q') }}" class="input"></div>
    <div><label class="label" for="status">Status</label><select id="status" name="status" class="input"><option value="">All</option>@foreach (\App\Models\Order::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    <button class="btn-dark">Filter</button>
</form>
<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
<table class="min-w-full text-sm"><thead class="bg-brand-charcoal text-left text-xs uppercase text-brand-white"><tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Fulfilment</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3 text-right">Total</th><th></th></tr></thead><tbody class="divide-y divide-gray-100">
@forelse ($orders as $o)<tr><td class="px-4 py-3 font-mono text-xs">{{ $o->order_number }}</td><td class="px-4 py-3">{{ $o->created_at->format('d M Y H:i') }}</td><td class="px-4 py-3">{{ $o->customer_name }}</td><td class="px-4 py-3 capitalize">{{ $o->fulfilment }}</td><td class="px-4 py-3"><x-status-badge :status="$o->status" /></td><td class="px-4 py-3"><x-status-badge :status="$o->payment_status" /></td><td class="px-4 py-3 text-right">R{{ number_format($o->total, 2) }}</td><td class="px-4 py-3 text-right"><a class="text-brand-orange hover:underline" href="{{ route('admin.orders.show', $o->id) }}">View</a></td></tr>
@empty<tr><td colspan="8" class="px-4 py-10 text-center text-brand-graphite">No orders found.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
