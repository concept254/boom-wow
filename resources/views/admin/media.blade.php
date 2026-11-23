@extends('layouts.admin', ['title' => 'Media Library'])
@section('content')
<form method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data" class="mb-6 rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5">@csrf
    <label class="label" for="files">Upload images (JPG, PNG, WEBP · max 5 MB each · up to 10 at a time)</label>
    <input id="files" type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm">
    @if (auth()->user()->hasRole('Super Admin'))<input type="hidden" name="as_hero" value="0"><label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" name="as_hero" value="1" class="rounded border-gray-300 text-brand-orange focus:ring-brand-orange"> Also add to the homepage hero rotation</label>@endif
    <button class="btn-primary mt-4">Upload</button>
</form>
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
@forelse ($items as $m)
    <figure class="overflow-hidden rounded-lg bg-brand-white shadow-sm ring-1 ring-black/5">
        <img src="{{ \Illuminate\Support\Facades\Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->alt ?? $m->original_name }}" class="aspect-square w-full object-cover" loading="lazy">
        <figcaption class="p-2 text-xs"><p class="truncate" title="{{ $m->original_name }}">{{ $m->original_name }}</p><p class="text-brand-graphite">{{ $m->width }}×{{ $m->height }} · {{ number_format($m->size / 1024) }} KB</p>
        <input readonly value="{{ \Illuminate\Support\Facades\Storage::disk($m->disk)->url($m->path) }}" class="input mt-1 py-0.5 text-[10px]" onclick="this.select()" aria-label="Image URL">
        <form method="POST" action="{{ route('admin.media.delete', $m) }}" onsubmit="return confirm('Delete this image?')" class="mt-1">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form></figcaption>
    </figure>
@empty<p class="col-span-full text-brand-graphite">No media uploaded yet.</p>@endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
