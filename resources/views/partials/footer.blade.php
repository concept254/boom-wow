@php($footer = \App\Models\Menu::where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get())
@php($s = fn ($k, $d = null) => \App\Services\SettingsService::get($k, $d))
<footer class="mt-8 bg-brand-charcoal text-brand-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
            <x-logo variant="dark" class="h-24 w-auto" />
            <p class="mt-4 max-w-xs text-sm text-gray-300">One destination, endless possibilities.</p>
        </div>
        <div>
            <h2 class="font-sans text-sm font-bold uppercase tracking-widest text-brand-orange">Explore</h2>
            <ul class="mt-4 space-y-2 text-sm text-gray-300">
                @foreach ($footer as $i)<li><a class="hover:text-brand-white" href="{{ $i->url }}">{{ $i->label }}</a></li>@endforeach
                <li><a class="hover:text-brand-white" href="{{ route('businesses') }}">Our Businesses</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-sans text-sm font-bold uppercase tracking-widest text-brand-orange">Contact</h2>
            <ul class="mt-4 space-y-2 text-sm text-gray-300">
                <li>{{ $s('contact_address') }}</li><li>{{ $s('contact_phone') }}</li><li>{{ $s('contact_email') }}</li>
                @foreach (['facebook', 'instagram', 'linkedin', 'x'] as $n)
                    @if ($u = $s("social_$n"))<li><a class="hover:text-brand-white" rel="noopener" href="{{ $u }}">{{ ucfirst($n) }}</a></li>@endif
                @endforeach
            </ul>
        </div>
    </div>
    <div class="border-t border-brand-graphite"><div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
        <div><h2 class="font-display text-lg font-semibold !text-brand-white">Get specials &amp; news</h2><p class="text-sm text-gray-300">Offers from all our businesses, straight to your inbox.</p></div>
        <div class="w-full max-w-md text-brand-charcoal">@include('public.partials.subscribe-form', ['compact' => true])</div>
    </div></div>
    <div class="border-t border-brand-graphite py-5 text-center text-xs text-gray-400">{{ $s('footer_text', '© Boom & Wow Holdings') }}</div>
</footer>
