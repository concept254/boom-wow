@php($mine = auth()->user()->hasRole('Super Admin') ? null : auth()->user()->businessIds())
@extends('layouts.admin', ['title' => 'Order '.$order->order_number])
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
<div class="space-y-6 lg:col-span-2">
    <section class="rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5"><h2 class="mb-3 text-lg font-bold">Items</h2>
    <table class="w-full text-sm"><thead class="text-left text-xs uppercase text-brand-graphite"><tr><th class="py-1">Product</th><th>Business</th><th>Qty</th><th class="text-right">Unit</th><th class="text-right">Line</th></tr></thead><tbody>
    @foreach ($order->items as $i)@if ($mine === null || in_array($i->business_unit_id, $mine))<tr class="border-t"><td class="py-2">{{ $i->product_name }}<br><span class="text-xs text-brand-graphite">{{ $i->sku }}</span></td><td>{{ $i->business?->name }}</td><td>{{ $i->quantity }}</td><td class="text-right">R{{ number_format($i->unit_price, 2) }}</td><td class="text-right">R{{ number_format($i->subtotal, 2) }}</td></tr>@endif @endforeach
    </tbody></table>
    @if ($mine === null)<p class="mt-4 text-right font-bold">Total: R{{ number_format($order->total, 2) }}</p>@endif</section>
    <section class="rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5"><h2 class="mb-3 text-lg font-bold">Payments</h2>
    @forelse ($order->payments as $p)<p class="text-sm">{{ ucfirst(str_replace('_', ' ', $p->driver)) }} · R{{ number_format($p->amount, 2) }} · <x-status-badge :status="$p->status" />@if ($p->paid_at) · {{ $p->paid_at->format('d M Y H:i') }}@endif</p>@empty<p class="text-sm text-brand-graphite">No payment record yet.</p>@endforelse
    @if ($order->payment_status !== 'paid')<form method="POST" action="{{ route('admin.orders.paid', $order->id) }}" class="mt-3" onsubmit="return confirm('Record this order as paid?')">@csrf<button class="btn-dark">Mark as paid</button></form>@endif</section>
</div>
<aside class="space-y-6">
    <section class="rounded-lg bg-brand-white p-5 text-sm shadow-sm ring-1 ring-black/5"><h2 class="mb-3 text-lg font-bold">Customer</h2><p>{{ $order->customer_name }}</p><p>{{ $order->customer_email }}</p><p>{{ $order->customer_phone }}</p>
    <p class="mt-3 capitalize"><strong>{{ $order->fulfilment }}</strong></p>@if ($order->delivery_address)<p class="whitespace-pre-line">{{ $order->delivery_address }}</p>@endif @if ($order->notes)<p class="mt-2 text-brand-graphite">Note: {{ $order->notes }}</p>@endif @if ($order->age_confirmed)<p class="mt-2 text-xs text-brand-graphite">Customer confirmed they meet the minimum age.</p>@endif</section>
    <section class="rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5"><h2 class="mb-3 text-lg font-bold">Status</h2><p class="mb-3"><x-status-badge :status="$order->status" /></p>
    <form method="POST" action="{{ route('admin.orders.update', $order->id) }}" class="flex gap-2" onsubmit="return this.status.value !== 'cancelled' || confirm('Cancelling returns stock to inventory. Continue?')">@csrf @method('PATCH')<select name="status" class="input" aria-label="Order status">@foreach (\App\Models\Order::STATUSES as $s)<option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="btn-primary">Update</button></form></section>
</aside></div>
@endsection
