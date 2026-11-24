@extends('layouts.app', ['title' => 'Checkout'])
@section('content')
<x-page-header slug="checkout" title="Checkout" subtitle="Almost there - confirm your details and place your order." />
<section class="section max-w-3xl">
@error('cart')<div class="mt-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700" role="alert">{{ $message }}</div>@enderror
<ul class="mt-6 divide-y divide-gray-200 rounded-lg ring-1 ring-black/5">@foreach ($lines as $l)<li class="flex justify-between p-4 text-sm"><span>{{ $l['quantity'] }} × {{ $l['product']->name }} <span class="text-brand-orange">({{ $l['product']->business->name }})</span></span><span>R{{ number_format($l['line_total'], 2) }}</span></li>@endforeach<li class="flex justify-between bg-brand-offwhite p-4 font-bold"><span>Total</span><span>R{{ number_format($total, 2) }}</span></li></ul>
<form method="POST" action="{{ route('checkout.place') }}" class="mt-8 space-y-5" x-data="{ f: '{{ old('fulfilment', 'collection') }}' }">@csrf
<fieldset><legend class="label">Fulfilment</legend><label class="mr-6"><input type="radio" name="fulfilment" value="collection" x-model="f"> Collection</label><label><input type="radio" name="fulfilment" value="delivery" x-model="f"> Delivery</label></fieldset>
<div x-show="f === 'delivery'" x-cloak><label class="label" for="addr">Delivery address</label><input id="addr" class="input" name="delivery_address" value="{{ old('delivery_address') }}">@error('delivery_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
<div><label class="label" for="ph">Phone</label><input id="ph" class="input" name="phone" value="{{ old('phone', auth()->user()->phone) }}"></div>
<div><label class="label" for="nt">Notes</label><textarea id="nt" class="input" name="notes" rows="3">{{ old('notes') }}</textarea></div>
@if ($ageRestricted)<div class="rounded border border-brand-orange bg-brand-offwhite p-4 text-sm"><p>{{ \App\Services\SettingsService::get('liquor_notice') }}</p><label class="mt-3 flex items-start gap-2"><input type="checkbox" name="age_confirmed" value="1" class="mt-1"> <span>I confirm that I am {{ config('boomwow.liquor.min_age') }} years or older and will show valid ID on collection/delivery.</span></label></div>@endif
<p class="text-sm text-brand-graphite">Payment: {{ str_replace('_', ' ', config('boomwow.payment.driver')) }}. You will receive payment instructions after placing your order.</p>
<button class="btn-primary"><x-icon name="check-circle" class="h-4 w-4" />Place order</button></form></section>
@endsection
