@extends('layouts.app', ['title' => 'My orders'])
@section('content')
@include('portal.nav')
<section class="section"><h1 class="text-3xl font-bold">My orders</h1>
<div class="mt-6 divide-y divide-gray-200 rounded-lg ring-1 ring-black/5">
@forelse ($orders as $o)<a href="{{ route('portal.orders.show', $o) }}" class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-brand-offwhite"><span><strong class="font-mono">{{ $o->order_number }}</strong><br><span class="text-sm text-brand-graphite">{{ $o->created_at->format('d M Y') }} · {{ ucfirst($o->fulfilment) }}</span></span><span class="flex items-center gap-3"><span class="font-semibold">R{{ number_format($o->total, 2) }}</span><x-status-badge :status="$o->status" /></span></a>
@empty<p class="p-6 text-brand-graphite">No orders yet. <a class="text-brand-orange" href="{{ route('products') }}">Browse products</a>.</p>@endforelse
</div><div class="mt-4">{{ $orders->links() }}</div></section>
@endsection
