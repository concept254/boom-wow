@extends('layouts.admin', ['title' => 'Activity Logs'])
@section('content')
<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
<table class="min-w-full text-sm"><thead class="bg-brand-charcoal text-left text-xs uppercase text-brand-white"><tr><th class="px-4 py-3">When</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Description</th><th class="px-4 py-3">IP</th></tr></thead><tbody class="divide-y divide-gray-100">
@forelse ($logs as $l)<tr><td class="whitespace-nowrap px-4 py-2.5">{{ $l->created_at->format('d M Y H:i') }}</td><td class="px-4 py-2.5">{{ $l->user?->name ?? 'System' }}</td><td class="px-4 py-2.5">{{ $l->action }}</td><td class="px-4 py-2.5">{{ $l->description }}</td><td class="px-4 py-2.5">{{ $l->ip_address }}</td></tr>
@empty<tr><td colspan="5" class="px-4 py-10 text-center text-brand-graphite">No activity recorded.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
