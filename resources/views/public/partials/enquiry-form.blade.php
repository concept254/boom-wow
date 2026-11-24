@php($type = $type ?? 'general')
<form method="POST" action="{{ route('enquiries.store') }}" class="space-y-4" novalidate>
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    @if (!empty($business))<input type="hidden" name="business_unit_id" value="{{ $business->id }}">@endif
    @if (!empty($subject))<input type="hidden" name="subject_type" value="{{ $subject[0] }}"><input type="hidden" name="subject_id" value="{{ $subject[1] }}">@endif
    <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="label" for="name-{{ $type }}">Name</label><input id="name-{{ $type }}" class="input" name="name" value="{{ old('name', auth()->user()?->name) }}" required>@error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
        <div><label class="label" for="email-{{ $type }}">Email</label><input id="email-{{ $type }}" type="email" class="input" name="email" value="{{ old('email', auth()->user()?->email) }}" required>@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    </div>
    <div><label class="label" for="phone-{{ $type }}">Phone (optional)</label><input id="phone-{{ $type }}" class="input" name="phone" value="{{ old('phone', auth()->user()?->phone) }}"></div>
    <div><label class="label" for="msg-{{ $type }}">Message</label><textarea id="msg-{{ $type }}" class="input" rows="4" name="message" required>{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    <button class="btn-primary"><x-icon name="send" class="h-4 w-4" />Send enquiry</button>
</form>
