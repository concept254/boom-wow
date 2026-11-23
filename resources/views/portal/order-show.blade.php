@extends('layouts.app', ['title' => 'Order '.$order->order_number])
@section('content')
@include('portal.nav')
<section class="section max-w-3xl">
    <p class="eyebrow">Order</p><h1 class="font-mono text-3xl font-bold">{{ $order->order_number }}</h1>
    <p class="mt-2 text-sm">Status: <x-status-badge :status="$order->status" /> · Payment: <x-status-badge :status="$order->payment_status" /> · <span class="capitalize">{{ $order->fulfilment }}</span></p>
    <div class="card mt-6 divide-y p-0 text-sm">
        @foreach ($order->items as $i)<div class="flex justify-between gap-4 p-4"><span>{{ $i->product_name }} × {{ $i->quantity }}<br><span class="text-xs text-brand-orange">{{ $i->business?->name }}</span></span><span>R{{ number_format($i->subtotal, 2) }}</span></div>@endforeach
        @if ($order->discount_total > 0)<div class="flex justify-between p-4"><span>Discounts</span><span>-R{{ number_format($order->discount_total, 2) }}</span></div>@endif
        <div class="flex justify-between p-4 text-base font-bold"><span>Total</span><span>R{{ number_format($order->total, 2) }}</span></div>
    </div>
    @if ($order->delivery_address)<p class="mt-4 whitespace-pre-line text-sm"><strong>Delivery address:</strong><br>{{ $order->delivery_address }}</p>@endif
    @if ($order->payment_status !== 'paid')<p class="mt-4 rounded bg-brand-offwhite p-4 text-sm">Payment is pending. We will contact you with payment or collection details.</p>@endif
</section>
@endsection
