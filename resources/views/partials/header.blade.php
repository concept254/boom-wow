@php($menu = \App\Models\Menu::where('location', 'header')->where('is_active', true)->orderBy('sort_order')->get())
<header class="sticky top-0 z-40 bg-brand-charcoal text-brand-white shadow" x-data="{ open: false }">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" aria-label="Boom &amp; Wow Holdings home"><x-logo variant="dark" :mark="true" class="h-12 w-auto sm:h-16" /></a>

        <nav class="hidden items-center gap-6 text-sm lg:flex" aria-label="Main">
            @foreach ($menu as $item)
                <a href="{{ $item->url }}" class="transition-colors hover:text-brand-orange">{{ $item->label }}</a>
            @endforeach
            <a href="{{ route('cart') }}" class="inline-flex items-center gap-1.5 transition-colors hover:text-brand-orange"><x-icon name="cart" class="h-5 w-5" />Cart ({{ app(\App\Services\CartService::class)->count() }})</a>
            @auth
                <a href="{{ auth()->user()->isBackOffice() ? route('admin.dashboard') : route('portal.dashboard') }}" class="btn-primary"><x-icon name="user" class="h-4 w-4" />{{ auth()->user()->isBackOffice() ? 'Admin' : 'Customer Portal' }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="inline-flex items-center gap-1.5 hover:text-brand-orange"><x-icon name="log-out" class="h-4 w-4" />Logout</button></form>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 hover:text-brand-orange"><x-icon name="log-in" class="h-4 w-4" />Login</a>
                <a href="{{ route('register') }}" class="btn-primary"><x-icon name="user-plus" class="h-4 w-4" />Register</a>
            @endauth
        </nav>

        <button class="lg:hidden" @click="open = !open" :aria-expanded="open" aria-label="Toggle menu">
            <x-icon name="menu" class="h-6 w-6" x-show="! open" /><x-icon name="x" class="h-6 w-6" x-cloak x-show="open" />
        </button>
    </div>
    <nav x-cloak x-show="open" x-transition class="border-t border-brand-graphite px-4 pb-4 lg:hidden" aria-label="Mobile">
        <div class="flex flex-col gap-3 pt-3 text-sm">
            @foreach ($menu as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@endforeach
            <a href="{{ route('cart') }}" class="flex items-center gap-2"><x-icon name="cart" class="h-4 w-4" />Cart</a>
            @auth
                <a href="{{ auth()->user()->isBackOffice() ? route('admin.dashboard') : route('portal.dashboard') }}">{{ auth()->user()->isBackOffice() ? 'Admin' : 'Customer Portal' }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex items-center gap-2"><x-icon name="log-out" class="h-4 w-4" />Logout</button></form>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-2"><x-icon name="log-in" class="h-4 w-4" />Login</a><a href="{{ route('register') }}" class="flex items-center gap-2"><x-icon name="user-plus" class="h-4 w-4" />Register</a>
            @endauth
        </div>
    </nav>
</header>
