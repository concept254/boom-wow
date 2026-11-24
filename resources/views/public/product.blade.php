@extends('layouts.app', ['title' => $product->name])
@section('content')
<x-page-header :title="$product->name" :crumbs="[['Products', route('products')], [$business->name, route('business', $business)]]" />
<section class="section grid gap-10 lg:grid-cols-2">
<img src="{{ $product->images->first() ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->images->first()->path) : 'https://picsum.photos/seed/'.$product->slug.'/800/600' }}" alt="{{ $product->name }}" class="w-full rounded-lg object-cover">
<div>
<a href="{{ route('business', $business) }}" class="eyebrow">{{ $business->name }}</a>
<p class="mt-1 text-sm text-gray-500">{{ $product->brand }} {{ $product->size }} · SKU {{ $product->sku }}</p>
<p class="mt-4 text-2xl font-semibold">@if($product->current_price != $product->price)<span class="text-base text-gray-400 line-through">R{{ number_format($product->price, 2) }}</span> @endif R{{ number_format($product->current_price, 2) }}</p>
<p class="mt-4 text-brand-graphite">{{ $product->description }}</p>
@if ($product->is_age_restricted || $business->age_restricted)<p class="mt-4 rounded border border-brand-orange bg-brand-offwhite p-3 text-sm">{{ \App\Services\SettingsService::get('liquor_notice', 'Enjoy responsibly. 18+ only.') }}</p>@endif
@if ($product->in_stock)
<form method="POST" action="{{ route('cart.add', $product) }}" class="mt-6 flex items-end gap-3">@csrf<div><label class="label" for="qty">Quantity</label><input id="qty" type="number" name="quantity" value="1" min="1" max="99" class="input w-24"></div><button class="btn-primary">Add to cart</button></form>
@else<p class="mt-6 font-semibold text-gray-500">Out of stock</p>@endif
</div></section>
@endsection
