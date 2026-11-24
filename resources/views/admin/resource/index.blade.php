@php
    $only = $cfg['only'] ?? ['index', 'create', 'store', 'edit', 'update', 'destroy'];
    $hasStatus = in_array('status', array_column($cfg['fields'], 'name'));
    $dir = request('dir') === 'asc' ? 'desc' : 'asc';
    $val = function ($row, $key) {
        $v = data_get($row, $key);
        if (is_bool($v)) { return $v ? 'Yes' : 'No'; }
        if ($v instanceof \Carbon\CarbonInterface) { return $v->format('d M Y H:i'); }
        return $v;
    };
@endphp
@extends('layouts.admin', ['title' => $cfg['label']])
@section('content')
<div class="mb-4 flex flex-wrap items-end justify-between gap-3">
    <form method="GET" class="flex flex-wrap items-end gap-2" role="search">
        <div><label class="label" for="q">Search</label><input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Search…"></div>
        @if ($hasStatus)<div><label class="label" for="status">Status</label><select id="status" name="status" class="input"><option value="">All</option>@foreach (['draft', 'published', 'archived', 'new', 'in_progress', 'resolved'] as $s)@if ($loop->index < 3 || $resource === 'enquiries')<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endif @endforeach</select></div>@endif
        @if ($cfg['scoped'] ?? false)<div><label class="label" for="business">Business</label><select id="business" name="business" class="input"><option value="">All</option>@foreach ($businesses as $id => $name)<option value="{{ $id }}" @selected((string) request('business') === (string) $id)>{{ $name }}</option>@endforeach</select></div>@endif
        <button class="btn-dark"><x-icon name="search" class="h-4 w-4" />Filter</button>
    </form>
    @if (in_array('create', $only))<a href="{{ route('admin.resource.create', $resource) }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" />New {{ \Illuminate\Support\Str::singular($cfg['label']) }}</a>@endif
</div>

<div class="overflow-x-auto rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
    <table class="min-w-full text-sm">
        <thead class="bg-brand-charcoal text-left text-xs uppercase tracking-wider text-brand-white"><tr>
            @foreach ($cfg['columns'] as $key => $label)
                <th class="px-4 py-3">@if (str_contains($key, '.'))<span>{{ $label }}</span>@else<a href="{{ request()->fullUrlWithQuery(['sort' => $key, 'dir' => $dir]) }}" class="hover:text-brand-orange">{{ $label }}@if (request('sort') === $key) {{ request('dir') === 'asc' ? '↑' : '↓' }}@endif</a>@endif</th>
            @endforeach
            <th class="px-4 py-3 text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
        @forelse ($rows as $row)
            <tr class="hover:bg-brand-offwhite">
                @foreach ($cfg['columns'] as $key => $label)
                    <td class="px-4 py-3">@if ($key === 'status')<x-status-badge :status="(string) $row->status" />@else{{ \Illuminate\Support\Str::limit((string) $val($row, $key), 60) }}@endif</td>
                @endforeach
                <td class="whitespace-nowrap px-4 py-3 text-right">
                    @if (in_array('edit', $only))<a href="{{ route('admin.resource.edit', [$resource, $row->getKey()]) }}" class="inline-flex items-center gap-1 text-brand-orange hover:underline"><x-icon name="edit" class="h-4 w-4" />Edit</a>@endif
                    @if (in_array('destroy', $only))
                        <form method="POST" action="{{ route('admin.resource.destroy', [$resource, $row->getKey()]) }}" class="ml-3 inline" onsubmit="return confirm('Delete this record? This cannot be undone.')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-red-600 hover:underline"><x-icon name="trash" class="h-4 w-4" />Delete</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($cfg['columns']) + 1 }}" class="px-4 py-10 text-center text-brand-graphite"><x-icon name="folder" class="mx-auto mb-2 h-8 w-8 text-gray-300" />Nothing here yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $rows->links() }}</div>
@endsection
