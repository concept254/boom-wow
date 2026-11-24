@extends('layouts.app', ['title' => 'Contact Us'])
@section('content')
@php($s = fn ($k, $d = null) => \App\Services\SettingsService::get($k, $d))
@php($days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'])
<x-page-header slug="contact" title="Contact us" subtitle="Visit us, call us, or send a message - the right team will get back to you." />

{{-- Contact details --}}
<section class="section !pb-6">
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-6"><p class="eyebrow">Visit</p><p class="mt-2 font-bold">{{ $s('contact_address', 'Address coming soon') }}</p><a href="{{ $directionsUrl }}" rel="noopener" target="_blank" class="mt-3 inline-block text-sm font-bold text-brand-orange hover:text-brand-orange-dark">Get directions &rarr;</a></div>
        <div class="card p-6"><p class="eyebrow">Call</p><p class="mt-2 font-bold">@if ($s('contact_phone'))<a href="tel:{{ preg_replace('/[^+\d]/', '', $s('contact_phone')) }}" class="hover:text-brand-orange">{{ $s('contact_phone') }}</a>@else - @endif</p>@if ($s('trading_note'))<p class="mt-1 text-xs text-gray-500">{{ $s('trading_note') }}</p>@endif</div>
        <div class="card p-6"><p class="eyebrow">Email</p><p class="mt-2 break-all font-bold">@if ($s('contact_email'))<a href="mailto:{{ $s('contact_email') }}" class="hover:text-brand-orange">{{ $s('contact_email') }}</a>@else - @endif</p></div>
        <div class="card p-6"><p class="eyebrow">WhatsApp</p>@if ($s('whatsapp_number'))<p class="mt-2 font-bold">Chat with us</p><a href="https://wa.me/{{ preg_replace('/\D/', '', $s('whatsapp_number')) }}" rel="noopener" target="_blank" class="btn-primary mt-3 !py-2">Message on WhatsApp</a>@else<p class="mt-2 font-bold">-</p>@endif</div>
    </div>
</section>

{{-- Map + form --}}
<section class="section !pt-6">
    <div class="grid gap-8 lg:grid-cols-2">
        <div class="overflow-hidden rounded-3xl bg-brand-offwhite shadow-sm ring-1 ring-black/5">
            <iframe title="Map showing our location" src="{{ $mapSrc }}" class="h-80 w-full border-0 lg:h-full lg:min-h-[28rem]" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="card p-6 sm:p-8 hover:shadow-sm">
            <h2 class="text-2xl">Send us a message</h2>
            <form method="POST" action="{{ route('enquiries.store') }}" class="mt-5 space-y-4">@csrf<input type="hidden" name="type" value="general">
                <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
                <div><label class="label" for="b">Which business?</label><select id="b" class="input" name="business_unit_id"><option value="">General enquiry</option>@foreach ($businesses as $b)<option value="{{ $b->id }}" @selected(old('business_unit_id') == $b->id)>{{ $b->name }}</option>@endforeach</select></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label" for="n">Name</label><input id="n" class="input" name="name" value="{{ old('name', auth()->user()?->name) }}" required>@error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label class="label" for="e">Email</label><input id="e" type="email" class="input" name="email" value="{{ old('email', auth()->user()?->email) }}" required>@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label" for="p">Phone (optional)</label><input id="p" class="input" name="phone" value="{{ old('phone') }}"></div>
                    <div><label class="label" for="s">Subject</label><input id="s" class="input" name="subject_line" value="{{ old('subject_line') }}"></div>
                </div>
                <div><label class="label" for="m">Message</label><textarea id="m" class="input" rows="5" name="message" required>{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <button class="btn-primary">Send message</button>
            </form>
        </div>
    </div>
</section>

{{-- Each business: address, phone, hours --}}
<section class="bg-brand-offwhite"><div class="section">
    <p class="eyebrow">Our stores</p><h2 class="mt-2 text-3xl sm:text-4xl">Find the right team</h2>
    <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($businesses as $b)
            @php($open = $b->isOpenNow())
            <article class="card p-6">
                <div class="flex items-start justify-between gap-3"><h3 class="text-lg"><a href="{{ route('business', $b) }}" class="hover:text-brand-orange">{{ $b->name }}</a></h3>@if ($open !== null)<span class="badge {{ $open ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-brand-graphite' }}">{{ $open ? 'Open now' : 'Closed' }}</span>@endif</div>
                <ul class="mt-3 space-y-1 text-sm text-brand-graphite">
                    @if ($b->address)<li>{{ $b->address }}@if ($b->city), {{ $b->city }}@endif</li>@endif
                    @if ($b->phone)<li><a href="tel:{{ preg_replace('/[^+\d]/', '', $b->phone) }}" class="hover:text-brand-orange">{{ $b->phone }}</a></li>@endif
                    @if ($b->email)<li><a href="mailto:{{ $b->email }}" class="break-all hover:text-brand-orange">{{ $b->email }}</a></li>@endif
                </ul>
                @if ($b->hours->isNotEmpty())
                    <table class="mt-4 w-full text-xs"><caption class="sr-only">Opening hours for {{ $b->name }}</caption>
                        @foreach ($b->hours as $h)<tr class="{{ $h->day_of_week === now()->dayOfWeek ? 'font-bold text-brand-orange' : 'text-brand-graphite' }}"><th scope="row" class="py-0.5 text-left">{{ $days[$h->day_of_week] }}</th><td class="py-0.5 text-right">{{ $h->is_closed ? 'Closed' : substr($h->opens_at, 0, 5).' - '.substr($h->closes_at, 0, 5) }}</td></tr>@endforeach
                    </table>
                @endif
                <a href="{{ route('business', $b) }}" class="btn-outline mt-5 !py-2">Visit store page</a>
            </article>
        @endforeach
    </div>
</div></section>

@if ($faqs->isNotEmpty())
<section class="section"><div class="grid gap-10 lg:grid-cols-[1fr_2fr]">
    <div><p class="eyebrow">FAQs</p><h2 class="mt-2 text-3xl sm:text-4xl">Before you write</h2><p class="mt-3 text-brand-graphite">Your answer may already be here.</p><a href="{{ route('faqs') }}" class="btn-outline mt-5">All FAQs</a></div>
    <div>@include('public.partials.faq-list', ['faqs' => $faqs])</div>
</div></section>
@endif
<x-ad-slot placement="inner_banner" />
@endsection
