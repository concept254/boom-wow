@extends('layouts.app', ['title' => 'Your cart'])
@section('content')
<x-page-header slug="cart" title="Your cart" subtitle="Review your items before checkout." />
<section class="section">
@if ($lines->isEmpty())<p class="mt-6">Your cart is empty. <a class="text-brand-orange" href="{{ route('products') }}">Browse products</a></p>
@else
<div class="mt-8 divide-y divide-gray-200 rounded-lg ring-1 ring-black/5">@foreach ($lines as $l)
<div class="flex flex-wrap items-center justify-between gap-4 p-4"><div><p class="font-semibold">{{ $l['product']->name }}</p><p class="text-xs text-brand-orange">{{ $l['product']->business->name }}</p><p class="text-sm text-brand-graphite">R{{ number_format($l['unit_price'], 2) }} each</p></div>
<div class="flex items-center gap-3"><form method="POST" action="{{ route('cart.update', $l['product']) }}" class="flex gap-2">@csrf @method('PATCH')<input type="number" name="quantity" value="{{ $l['quantity'] }}" min="0" max="99" class="input w-20" aria-label="Quantity"><button class="btn-outline"><x-icon name="repeat" class="h-4 w-4" />Update</button></form>
<form method="POST" action="{{ route('cart.remove', $l['product']) }}">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-sm text-red-600 hover:underline"><x-icon name="trash" class="h-4 w-4" />Remove</button></form></div>
<p class="w-28 text-right font-semibold">R{{ number_format($l['line_total'], 2) }}</p></div>@endforeach</div>
<p class="mt-6 text-right text-xl font-bold">Total: R{{ number_format($total, 2) }}</p>
<div class="mt-6 text-right"><a href="{{ route('checkout') }}" class="btn-primary"><x-icon name="credit-card" class="h-4 w-4" />Checkout</a></div>
@endif</section>
@endsection
