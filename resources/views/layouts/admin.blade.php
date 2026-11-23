@php
    $u = auth()->user();
    $super = $u->hasRole('Super Admin');
    $nav = [
        ['Dashboard', route('admin.dashboard'), null],
        ['Business Units', route('admin.resource.index', 'business-units'), 'businesses.view'],
        ['Customers', route('admin.customers'), 'customers.view'],
        ['Staff', route('admin.resource.index', 'staff'), 'staff.view'],
        ['Users', route('admin.users'), 'super'],
        ['Roles & Permissions', route('admin.roles'), 'super'],
        ['Services', route('admin.resource.index', 'services'), 'services.view'],
        ['Service Categories', route('admin.resource.index', 'service-categories'), 'services.view'],
        ['Products', route('admin.resource.index', 'products'), 'products.view'],
        ['Packages', route('admin.resource.index', 'service-packages'), 'services.view'],
        ['Portfolio', route('admin.resource.index', 'portfolio-items'), 'services.view'],
        ['Opening Hours', route('admin.resource.index', 'business-hours'), 'schedule.view'],
        ['Staff Availability', route('admin.resource.index', 'staff-availability'), 'schedule.view'],
        ['Staff Time Off', route('admin.resource.index', 'staff-time-off'), 'schedule.view'],
        ['Holidays & Closures', route('admin.resource.index', 'business-closures'), 'schedule.view'],
        ['Product Categories', route('admin.resource.index', 'product-categories'), 'products.view'],
        ['Inventory', route('admin.inventory.index'), 'inventory.view'],
        ['Stock Movements', route('admin.inventory.movements'), 'inventory.view'],
        ['Orders', route('admin.orders.index'), 'orders.view'],
        ['Bookings', route('admin.bookings.index'), 'bookings.view'],
        ['Enquiries', route('admin.resource.index', 'enquiries'), 'enquiries.view'],
        ['Events', route('admin.resource.index', 'events'), 'events.view'],
        ['Properties', route('admin.resource.index', 'properties'), 'properties.view'],
        ['CMS Pages', route('admin.resource.index', 'cms-pages'), 'cms.view'],
        ['Blog & News', route('admin.resource.index', 'blog-posts'), 'cms.view'],
        ['Testimonials', route('admin.resource.index', 'testimonials'), 'cms.view'],
        ['Hero Slides', route('admin.resource.index', 'hero-slides'), 'cms.view'],
        ['Promotions & Adverts', route('admin.resource.index', 'promotions'), 'cms.view'],
        ['Team & Management', route('admin.resource.index', 'team-members'), 'cms.view'],
        ['Subscribers', route('admin.resource.index', 'subscribers'), 'cms.view'],
        ['FAQs', route('admin.resource.index', 'faqs'), 'cms.view'],
        ['Navigation & Footer', route('admin.resource.index', 'menus'), 'cms.view'],
        ['Media Library', route('admin.media'), 'media.view'],
        ['Reports', route('admin.reports.index'), 'reports.view'],
        ['Notifications', route('admin.notifications'), null],
        ['Activity Logs', route('admin.logs'), 'logs.view'],
        ['Settings', route('admin.settings'), 'super'],
    ];
    $visible = collect($nav)->filter(fn ($i) => $i[2] === null || ($i[2] === 'super' ? $super : $u->can($i[2])));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? 'Admin' }} · Boom &amp; Wow Admin</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800|fredoka:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-offwhite" x-data="{ nav: false }">
    <div class="flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto bg-brand-charcoal text-brand-white transition-transform lg:translate-x-0" :class="{ 'translate-x-0': nav }" aria-label="Admin navigation">
            <div class="flex items-center justify-center border-b border-brand-graphite bg-brand-white p-4">
                <a href="{{ route('admin.dashboard') }}"><x-logo variant="light" class="h-14 w-auto" /></a>
            </div>
            <nav class="space-y-0.5 p-3 text-sm">
                @foreach ($visible as [$label, $url])
                    <a href="{{ $url }}" class="block rounded px-3 py-2 transition-colors hover:bg-brand-graphite {{ url()->current() === $url || str_starts_with(url()->current(), $url.'/') && $url !== route('admin.dashboard') ? 'bg-brand-orange text-brand-white hover:bg-brand-orange-dark' : 'text-gray-200' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </aside>
        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-30 flex items-center justify-between bg-brand-white px-4 py-3 shadow-sm">
                <button class="lg:hidden" @click="nav = !nav" aria-label="Toggle navigation"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg></button>
                <h1 class="font-display text-xl font-bold">{{ $title ?? 'Admin' }}</h1>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('home') }}" class="text-brand-graphite hover:text-brand-orange" target="_blank" rel="noopener">View site</a>
                    <span class="hidden text-brand-graphite sm:inline">{{ $u->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-brand-orange hover:underline">Logout</button></form>
                </div>
            </header>
            <main class="flex-1 p-4 sm:p-6">
                @if (session('status'))<div class="mb-4 rounded border-l-4 border-brand-orange bg-brand-white p-3 text-sm shadow-sm" role="status">{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="mb-4 rounded border-l-4 border-red-600 bg-brand-white p-3 text-sm text-red-700 shadow-sm" role="alert"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
