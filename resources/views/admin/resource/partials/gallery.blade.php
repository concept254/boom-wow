@php
    $galleryImages = $row->exists ? $row->images : collect();
    $isService = $resource === 'services';
@endphp
<fieldset class="rounded border border-gray-200 bg-brand-offwhite p-4 sm:p-5">
    <legend class="flex items-center gap-2 px-2 text-sm font-bold"><x-icon name="image" class="h-4 w-4 text-brand-orange" />Photo gallery</legend>
    <p class="text-xs text-brand-graphite">
        Add several photos at once (JPG, PNG or WebP, up to 5 MB each, 12 per save). Large photos are resized automatically.
        The <strong>main photo</strong> is shown first and on product cards; the others appear as thumbnails on the page.
        Use the caption for the option shown (for example "Black", "Size M" or "Gift box").
        @if ($isService) The cover image above is always shown first. @endif
    </p>

    @if ($galleryImages->isNotEmpty())
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($galleryImages as $img)
                <div class="rounded bg-brand-white p-3 ring-1 ring-black/5">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img->path) }}" alt="{{ $img->alt }}" class="h-32 w-full rounded object-cover">
                    <label class="mt-3 flex items-center gap-2 text-xs font-semibold"><input type="radio" name="primary_image" value="{{ $img->id }}" @checked($img->is_primary) class="text-brand-orange focus:ring-brand-orange"> Main photo</label>
                    <input type="text" name="image_alt[{{ $img->id }}]" value="{{ $img->alt }}" maxlength="150" class="input mt-2 !py-1.5 text-xs" placeholder="Caption / option, e.g. Black" aria-label="Caption for this photo">
                    <div class="mt-2 flex items-center justify-between gap-2">
                        <label class="flex items-center gap-1 text-xs">Order <input type="number" min="0" max="999" name="image_order[{{ $img->id }}]" value="{{ $img->sort_order }}" class="input !w-16 !py-1 text-xs"></label>
                        <label class="flex items-center gap-1 text-xs font-semibold text-red-700"><input type="checkbox" name="remove_images[]" value="{{ $img->id }}" class="rounded border-gray-300 text-red-600 focus:ring-red-500"> Remove</label>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-3 text-xs font-semibold text-brand-graphite">No gallery photos yet.</p>
    @endif

    <div class="mt-4">
        <label class="label" for="gallery">Add photos</label>
        <input id="gallery" type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
        @foreach ($errors->get('gallery*') as $messages)@foreach ((array) $messages as $message)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@endforeach @endforeach
    </div>
</fieldset>
