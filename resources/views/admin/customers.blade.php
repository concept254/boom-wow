@extends('layouts.admin', ['title' => 'Customers'])
@section('content')
<form method="GET" class="mb-4 flex items-end gap-2" role="search"><div><label class="label" for="q">Email or surname</label><input id="q" name="q" value="{{ request('q') }}" class="input"></div><button class="btn-dark">Search</button></form>
<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
<table class="min-w-full text-sm"><thead class="bg-brand-charcoal text-left text-xs uppercase text-brand-white"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Phone</th><th class="px-4 py-3">Bookings</th><th class="px-4 py-3">Orders</th><th class="px-4 py-3">Joined</th></tr></thead><tbody class="divide-y divide-gray-100">
@forelse ($users as $u)<tr><td class="px-4 py-3">{{ $u->name }}</td><td class="px-4 py-3">{{ $u->email }}</td><td class="px-4 py-3">{{ $u->phone }}</td><td class="px-4 py-3">{{ $u->bookings_count }}</td><td class="px-4 py-3">{{ $u->orders_count }}</td><td class="px-4 py-3">{{ $u->created_at->format('d M Y') }}</td></tr>
@empty<tr><td colspan="6" class="px-4 py-10 text-center text-brand-graphite">No customers found.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
