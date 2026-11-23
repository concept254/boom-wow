@extends('layouts.admin', ['title' => $title])
@section('content')
<form method="GET" class="mb-4 flex flex-wrap items-end gap-2">
    <div><label class="label" for="from">From</label><input id="from" type="date" name="from" value="{{ request('from') }}" class="input"></div>
    <div><label class="label" for="to">To</label><input id="to" type="date" name="to" value="{{ request('to') }}" class="input"></div>
    <button class="btn-dark">Apply</button>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn-primary">Export CSV</a>
    <a href="{{ route('admin.reports.index') }}" class="btn-outline">All reports</a>
</form>
<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
<table class="min-w-full text-sm"><thead class="bg-brand-charcoal text-left text-xs uppercase text-brand-white"><tr>@foreach ($headings as $h)<th class="px-4 py-3">{{ $h }}</th>@endforeach</tr></thead><tbody class="divide-y divide-gray-100">
@forelse ($rows as $row)<tr>@foreach ($row as $cell)<td class="px-4 py-2.5">{{ $cell }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headings) }}" class="px-4 py-10 text-center text-brand-graphite">No data for this period.</td></tr>@endforelse
</tbody></table></div>
@endsection
