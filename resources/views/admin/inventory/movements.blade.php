@extends('layouts.admin', ['title' => 'Stock Movements'])
@section('content')
<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
<table class="min-w-full text-sm"><thead class="bg-brand-charcoal text-left text-xs uppercase text-brand-white"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Product</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Previous</th><th class="px-4 py-3">Change</th><th class="px-4 py-3">New</th><th class="px-4 py-3">Reason</th><th class="px-4 py-3">User</th></tr></thead><tbody class="divide-y divide-gray-100">
@forelse ($rows as $m)<tr><td class="px-4 py-3">{{ $m->created_at->format('d M Y H:i') }}</td><td class="px-4 py-3">{{ $m->product?->name }}</td><td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $m->type) }}</td><td class="px-4 py-3">{{ $m->previous_quantity }}</td><td class="px-4 py-3 font-semibold {{ $m->change < 0 ? 'text-red-600' : 'text-green-700' }}">{{ $m->change > 0 ? '+' : '' }}{{ $m->change }}</td><td class="px-4 py-3">{{ $m->new_quantity }}</td><td class="px-4 py-3">{{ $m->reason }}</td><td class="px-4 py-3">{{ $m->user?->name ?? 'System' }}</td></tr>
@empty<tr><td colspan="8" class="px-4 py-10 text-center text-brand-graphite">No movements recorded.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
