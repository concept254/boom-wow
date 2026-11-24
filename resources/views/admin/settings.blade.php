@extends('layouts.admin', ['title' => 'Settings'])
@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-3xl space-y-4 rounded-lg bg-brand-white p-6 shadow-sm ring-1 ring-black/5">@csrf @method('PUT')
@foreach ($fields as $key => $label)
    <div><label class="label" for="{{ $key }}">{{ $label }}</label>
    @if (in_array($key, ['hero_subtitle', 'footer_text', 'seo_description', 'liquor_notice']))<textarea id="{{ $key }}" name="{{ $key }}" rows="3" class="input">{{ old($key, \App\Services\SettingsService::get($key)) }}</textarea>
    @else<input id="{{ $key }}" name="{{ $key }}" value="{{ old($key, \App\Services\SettingsService::get($key)) }}" class="input">@endif
    @error($key)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
@endforeach
<button class="btn-primary"><x-icon name="check" class="h-4 w-4" />Save settings</button>
</form>
@endsection
