@php
    $editing = $row->exists;
@endphp
@extends('layouts.admin', ['title' => ($editing ? 'Edit ' : 'New ').\Illuminate\Support\Str::singular($cfg['label'])])
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.resource.update', [$resource, $row->getKey()]) : route('admin.resource.store', $resource) }}" class="max-w-3xl space-y-5 rounded-lg bg-brand-white p-6 shadow-sm ring-1 ring-black/5">
    @csrf
    @if ($editing) @method('PUT') @endif
    @if ($editing && ! empty($cfg['show_fields']))
        <dl class="grid gap-3 rounded bg-brand-offwhite p-4 text-sm sm:grid-cols-2">@foreach ($cfg['show_fields'] as $sf)<div @class(['sm:col-span-2' => $sf === 'message'])><dt class="text-xs font-semibold uppercase tracking-wider text-brand-graphite">{{ str_replace('_', ' ', $sf) }}</dt><dd class="whitespace-pre-line">{{ $row->{$sf} }}</dd></div>@endforeach</dl>
    @endif
    @foreach ($cfg['fields'] as $f)
        @php
            $name = $f['name'];
            $current = old($name, $row->{$name} ?? null);
            $id = 'f-'.$name;
        @endphp
        <div>
            @if ($f['type'] === 'checkbox')
                <label class="flex items-center gap-2 text-sm"><input type="hidden" name="{{ $name }}" value="0"><input type="checkbox" name="{{ $name }}" value="1" @checked((bool) $current) class="rounded border-gray-300 text-brand-orange focus:ring-brand-orange"> {{ $f['label'] }}</label>
            @else
                <label class="label" for="{{ $id }}">{{ $f['label'] }}</label>
                @if ($f['type'] === 'lines')
                    <textarea id="{{ $id }}" name="{{ $name }}" rows="5" class="input">{{ is_array($current) ? implode("\n", $current) : $current }}</textarea>
                @elseif ($f['type'] === 'textarea')
                    <textarea id="{{ $id }}" name="{{ $name }}" rows="5" class="input">{{ $current }}</textarea>
                @elseif ($f['type'] === 'business')
                    <select id="{{ $id }}" name="{{ $name }}" class="input" required>@foreach ($options['business'] as $bid => $bname)<option value="{{ $bid }}" @selected((string) $current === (string) $bid)>{{ $bname }}</option>@endforeach</select>
                @elseif ($f['type'] === 'select')
                    @php $choices = $f['options'] ?? ($options[$name] ?? []); @endphp
                    <select id="{{ $id }}" name="{{ $name }}" class="input">@if (! isset($f['options']))<option value="">—</option>@endif @foreach ($choices as $k => $v)<option value="{{ $k }}" @selected((string) $current === (string) $k)>{{ $v }}</option>@endforeach</select>
                @elseif ($f['type'] === 'image')
                    @if ($current)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($current) }}" alt="" class="mb-2 h-24 w-auto rounded">@endif
                    @if ($current)<label class="mb-2 inline-flex items-center gap-2 text-sm font-semibold text-red-700"><input type="checkbox" name="remove_{{ $name }}" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500"> Remove current image</label>@endif
                    @if (! $current)<p class="mb-2 text-xs text-brand-graphite">No image set - this is optional.</p>@endif
                    <input id="{{ $id }}" type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
                @else
                    @php $htmlType = match ($f['type']) { 'number' => 'number', 'decimal' => 'number', 'date' => 'date', 'datetime' => 'datetime-local', 'time' => 'time', 'email' => 'email', 'tel' => 'tel', default => 'text' }; @endphp
                    @php $shown = $current instanceof \Carbon\CarbonInterface ? $current->format($f['type'] === 'datetime' ? 'Y-m-d\TH:i' : 'Y-m-d') : $current; @endphp
                    <input id="{{ $id }}" type="{{ $htmlType }}" name="{{ $name }}" value="{{ $shown }}" class="input" @if ($f['type'] === 'decimal') step="0.01" @endif>
                @endif
            @endif
            @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    @endforeach
    @if (! empty($cfg['gallery']))
        @include('admin.resource.partials.gallery')
    @endif
    <div class="flex gap-3"><button class="btn-primary"><x-icon name="check" class="h-4 w-4" />Save</button><a href="{{ route('admin.resource.index', $resource) }}" class="btn-outline">Cancel</a></div>
</form>
@endsection
