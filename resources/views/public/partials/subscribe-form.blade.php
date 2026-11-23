@php($compact = $compact ?? false)
<form method="POST" action="{{ route('subscribe') }}" class="{{ $compact ? 'flex flex-col gap-2 sm:flex-row' : 'mt-6 grid gap-3 sm:grid-cols-[1fr_1fr_auto]' }}">
    @csrf
    <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
    @unless ($compact)
        <div><label class="sr-only" for="sub-name">Your name</label><input id="sub-name" name="name" class="input" placeholder="Your name (optional)" autocomplete="name"></div>
    @endunless
    <div><label class="sr-only" for="sub-email{{ $compact ? '-f' : '' }}">Email address</label><input id="sub-email{{ $compact ? '-f' : '' }}" type="email" name="email" class="input" placeholder="Your email address" required autocomplete="email"></div>
    <button class="btn-dark">Subscribe</button>
</form>
@error('email')<p class="mt-2 text-sm font-semibold text-brand-white">{{ $message }}</p>@enderror
