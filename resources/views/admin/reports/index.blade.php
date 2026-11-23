@extends('layouts.admin', ['title' => 'Reports'])
@section('content')
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@foreach ($reports as $key => $label)
    <a href="{{ route('admin.reports.show', $key) }}" class="rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5 transition-shadow hover:shadow-md"><h2 class="text-lg font-bold">{{ $label }}</h2><p class="mt-1 text-sm text-brand-graphite">View table · export CSV</p></a>
@endforeach
</div>
@endsection
