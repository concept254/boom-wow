@extends('layouts.admin', ['title' => 'Inventory'])
@section('content')
<form method="GET" class="mb-4 flex flex-wrap items-end gap-2" role="search">
    <div><label class="label" for="q">Product or SKU</label><input id="q" name="q" value="{{ request('q') }}" class="input"></div>
    <div><label class="label" for="filter">Show</label><select id="filter" name="filter" class="input"><option value="">All</option><option value="low" @selected(request('filter') === 'low')>Low stock</option><option value="out" @selected(request('filter') === 'out')>Out of stock</option></select></div>
    <button class="btn-dark"><x-icon name="search" class="h-4 w-4" />Filter</button>
</form>
<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
<table class="min-w-full text-sm"><thead class="bg-brand-charcoal text-left text-xs uppercase text-brand-white"><tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">Business</th><th class="px-4 py-3">Qty</th><th class="px-4 py-3">Reorder</th><th class="px-4 py-3">Value (cost)</th><th class="px-4 py-3">Adjust stock</th></tr></thead><tbody class="divide-y divide-gray-100">
@forelse ($rows as $r)@php($p = $r->product)
<tr class="align-top"><td class="px-4 py-3">{{ $p?->name }}<br><span class="text-xs text-brand-graphite">{{ $p?->sku }}</span></td><td class="px-4 py-3">{{ $p?->business?->name }}</td>
<td class="px-4 py-3 font-semibold {{ $r->quantity === 0 ? 'text-red-600' : ($r->quantity <= $r->reorder_level ? 'text-brand-orange' : '') }}">{{ $r->quantity }}@if ($r->quantity === 0) <span class="badge bg-red-100 text-red-800">out</span>@elseif ($r->quantity <= $r->reorder_level) <span class="badge bg-orange-100 text-brand-orange-dark">low</span>@endif</td>
<td class="px-4 py-3">{{ $r->reorder_level }}</td><td class="px-4 py-3">R{{ number_format($r->quantity * ($p?->cost_price ?? 0), 2) }}</td>
<td class="px-4 py-3">@if ($p)<form method="POST" action="{{ route('admin.inventory.adjust', $p->id) }}" class="flex flex-wrap gap-1">@csrf
<select name="action" class="input w-28 py-1" aria-label="Action"><option value="receive">Receive</option><option value="deduct">Deduct</option><option value="adjust">Set to</option></select>
<input type="number" name="quantity" min="0" required class="input w-20 py-1" placeholder="Qty" aria-label="Quantity"><input name="reason" required maxlength="255" class="input w-40 py-1" placeholder="Reason" aria-label="Reason"><button class="btn-dark px-3 py-1">Apply</button></form>@endif</td></tr>
@empty<tr><td colspan="6" class="px-4 py-10 text-center text-brand-graphite">No inventory records.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
