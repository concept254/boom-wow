@php($menu = \App\Models\Menu::where('location', 'header')->where('is_active', true)->orderBy('sort_order')->get())
<header class="sticky top-0 z-40 bg-brand-charcoal text-brand-white shadow" x-data="{ open: false }">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" aria-label="Boom &amp; Wow Holdings home"><x-logo variant="dark" :mark="true" class="h-12 w-auto sm:h-16" /></a>

        <nav class="hidden items-center gap-6 text-sm lg:flex" aria-label="Main">
            @foreach ($menu as $item)
                <a href="{{ $item->url }}" class="transition-colors hover:text-brand-orange">{{ $item->label }}</a>
            @endforeach
            <a href="{{ route('cart') }}" class="transition-colors hover:text-brand-orange">Cart ({{ app(\App\Services\CartService::class)->count() }})</a>
            @auth
                <a href="{{ auth()->user()->isBackOffice() ? route('admin.dashboard') : route('portal.dashboard') }}" class="btn-primary">{{ auth()->user()->isBackOffice() ? 'Admin' : 'Customer Portal' }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="hover:text-brand-orange">Logout</button></form>
            @else
                <a href="{{ route('login') }}" class="hover:text-brand-orange">Login</a>
                <a href="{{ route('register') }}" class="btn-primary">Register</a>
            @endauth
        </nav>

        <button class="lg:hidden" @click="open = !open" :aria-expanded="open" aria-label="Toggle menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>
    <nav x-cloak x-show="open" x-transition class="border-t border-brand-graphite px-4 pb-4 lg:hidden" aria-label="Mobile">
        <div class="flex flex-col gap-3 pt-3 text-sm">
            @foreach ($menu as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@endforeach
            <a href="{{ route('cart') }}">Cart</a>
            @auth
                <a href="{{ auth()->user()->isBackOffice() ? route('admin.dashboard') : route('portal.dashboard') }}">{{ auth()->user()->isBackOffice() ? 'Admin' : 'Customer Portal' }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
            @else
                <a href="{{ route('login') }}">Login</a><a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </nav>
</header>
