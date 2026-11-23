@extends('layouts.admin', ['title' => 'Notifications'])
@section('content')
<div class="max-w-3xl space-y-3">
@forelse ($items as $n)
    <div class="rounded-lg bg-brand-white p-4 shadow-sm ring-1 ring-black/5"><p class="text-sm font-semibold">{{ $n->data['title'] ?? class_basename($n->type) }}</p><p class="text-sm text-brand-graphite">{{ $n->data['message'] ?? '' }}</p><p class="mt-1 text-xs text-brand-graphite">{{ $n->created_at->diffForHumans() }}</p></div>
@empty<p class="text-brand-graphite">No notifications.</p>@endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
