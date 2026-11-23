@php($links = ['portal.dashboard' => 'Overview', 'portal.bookings' => 'Bookings', 'portal.orders' => 'Orders', 'portal.settings' => 'Account settings'])
<div class="border-b border-gray-200 bg-brand-offwhite">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap gap-1 text-sm" aria-label="Portal">
            @foreach ($links as $route => $label)
                <a href="{{ route($route) }}" class="rounded px-3 py-1.5 {{ request()->routeIs($route) ? 'bg-brand-charcoal text-brand-white' : 'text-brand-graphite hover:bg-brand-white' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <a href="{{ route('portal.bookings.new') }}" class="btn-primary">Book an appointment</a>
    </div>
</div>
