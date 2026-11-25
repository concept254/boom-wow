@php
    $u = auth()->user();
    $super = $u->hasRole('Super Admin');
    // [label, url, permission, icon, group]. Groups render as small headings in the sidebar.
    $nav = [
        ['Dashboard', route('admin.dashboard'), null, 'dashboard', 'Overview'],
        ['Notifications', route('admin.notifications'), null, 'bell', 'Overview'],
        ['Reports', route('admin.reports.index'), 'reports.view', 'chart', 'Overview'],

        ['Orders', route('admin.orders.index'), 'orders.view', 'bag', 'Sales'],
        ['Bookings', route('admin.bookings.index'), 'bookings.view', 'calendar-check', 'Sales'],
        ['Enquiries', route('admin.resource.index', 'enquiries'), 'enquiries.view', 'message', 'Sales'],
        ['Customer Reviews', route('admin.resource.index', 'reviews'), 'enquiries.view', 'star', 'Sales'],
        ['Customers', route('admin.customers'), 'customers.view', 'users', 'Sales'],

        ['Business Units', route('admin.resource.index', 'business-units'), 'businesses.view', 'store', 'Businesses'],
        ['Opening Hours', route('admin.resource.index', 'business-hours'), 'schedule.view', 'clock', 'Businesses'],
        ['Holidays & Closures', route('admin.resource.index', 'business-closures'), 'schedule.view', 'calendar-x', 'Businesses'],
        ['Services', route('admin.resource.index', 'services'), 'services.view', 'scissors', 'Businesses'],
        ['Service Categories', route('admin.resource.index', 'service-categories'), 'services.view', 'tag', 'Businesses'],
        ['Packages', route('admin.resource.index', 'service-packages'), 'services.view', 'gift', 'Businesses'],
        ['Portfolio', route('admin.resource.index', 'portfolio-items'), 'services.view', 'image', 'Businesses'],
        ['Events', route('admin.resource.index', 'events'), 'events.view', 'ticket', 'Businesses'],
        ['Properties', route('admin.resource.index', 'properties'), 'properties.view', 'home', 'Businesses'],

        ['Products', route('admin.resource.index', 'products'), 'products.view', 'package', 'Shop & Stock'],
        ['Product Categories', route('admin.resource.index', 'product-categories'), 'products.view', 'layers', 'Shop & Stock'],
        ['Inventory', route('admin.inventory.index'), 'inventory.view', 'clipboard', 'Shop & Stock'],
        ['Stock Movements', route('admin.inventory.movements'), 'inventory.view', 'repeat', 'Shop & Stock'],

        ['Staff', route('admin.resource.index', 'staff'), 'staff.view', 'user-check', 'Team'],
        ['Staff Availability', route('admin.resource.index', 'staff-availability'), 'schedule.view', 'calendar', 'Team'],
        ['Staff Time Off', route('admin.resource.index', 'staff-time-off'), 'schedule.view', 'calendar-x', 'Team'],
        ['Team & Management', route('admin.resource.index', 'team-members'), 'cms.view', 'users', 'Team'],

        ['Hero Slides', route('admin.resource.index', 'hero-slides'), 'cms.view', 'monitor', 'Website'],
        ['Promotions & Adverts', route('admin.resource.index', 'promotions'), 'cms.view', 'megaphone', 'Website'],
        ['Brands (logo carousel)', route('admin.resource.index', 'brands'), 'cms.view', 'award', 'Website'],
        ['Page Headers', route('admin.resource.index', 'page-headers'), 'cms.view', 'panel-top', 'Website'],
        ['CMS Pages', route('admin.resource.index', 'cms-pages'), 'cms.view', 'file-text', 'Website'],
        ['Blog & News', route('admin.resource.index', 'blog-posts'), 'cms.view', 'newspaper', 'Website'],
        ['Testimonials', route('admin.resource.index', 'testimonials'), 'cms.view', 'star', 'Website'],
        ['FAQs', route('admin.resource.index', 'faqs'), 'cms.view', 'help', 'Website'],
        ['Subscribers', route('admin.resource.index', 'subscribers'), 'cms.view', 'mail', 'Website'],
        ['Navigation & Footer', route('admin.resource.index', 'menus'), 'cms.view', 'list', 'Website'],
        ['Media Library', route('admin.media'), 'media.view', 'folder', 'Website'],

        ['Users', route('admin.users'), 'super', 'key', 'System'],
        ['Roles & Permissions', route('admin.roles'), 'super', 'shield', 'System'],
        ['Activity Logs', route('admin.logs'), 'logs.view', 'activity', 'System'],
        ['Settings', route('admin.settings'), 'super', 'settings', 'System'],
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
    <link href="https://fonts.bunny.net/css?family=sora:400,600,700|dm-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-offwhite" x-data="{ nav: false }">
    <div class="flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto bg-brand-charcoal text-brand-white transition-transform lg:translate-x-0" :class="{ 'translate-x-0': nav }" aria-label="Admin navigation">
            <div class="flex items-center justify-center border-b border-brand-graphite bg-brand-white p-4">
                <a href="{{ route('admin.dashboard') }}"><x-logo variant="light" class="h-14 w-auto" /></a>
            </div>
            <nav class="space-y-0.5 p-3 pb-8 text-sm">
                @foreach ($visible->groupBy(4) as $group => $items)
                    <p class="px-3 pb-1 pt-4 text-[11px] font-bold uppercase tracking-widest text-gray-500">{{ $group }}</p>
                    @foreach ($items as [$label, $url, $perm, $icon])
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded px-3 py-2 transition-colors hover:bg-brand-graphite {{ url()->current() === $url || str_starts_with(url()->current(), $url.'/') && $url !== route('admin.dashboard') ? 'bg-brand-orange text-brand-white hover:bg-brand-orange-dark' : 'text-gray-200' }}"><x-icon :name="$icon" class="h-[18px] w-[18px] shrink-0" />{{ $label }}</a>
                    @endforeach
                @endforeach
            </nav>
        </aside>
        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-30 flex items-center justify-between bg-brand-white px-4 py-3 shadow-sm">
                <button class="lg:hidden" @click="nav = !nav" aria-label="Toggle navigation"><x-icon name="menu" class="h-6 w-6" /></button>
                <h1 class="font-display text-xl font-bold">{{ $title ?? 'Admin' }}</h1>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('home') }}" class="text-brand-graphite hover:text-brand-orange" target="_blank" rel="noopener"><span class="inline-flex items-center gap-1.5"><x-icon name="external-link" class="h-4 w-4" />View site</span></a>
                    <span class="hidden items-center gap-1.5 text-brand-graphite sm:inline-flex"><x-icon name="user" class="h-4 w-4" />{{ $u->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="inline-flex items-center gap-1.5 text-brand-orange hover:underline"><x-icon name="log-out" class="h-4 w-4" />Logout</button></form>
                </div>
            </header>
            <main class="flex-1 p-4 sm:p-6">
                @if (session('status'))<div class="mb-4 flex items-center gap-2 rounded border-l-4 border-brand-orange bg-brand-white p-3 text-sm shadow-sm" role="status"><x-icon name="check-circle" class="h-5 w-5 text-brand-orange" />{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="mb-4 flex items-start gap-2 rounded border-l-4 border-red-600 bg-brand-white p-3 text-sm text-red-700 shadow-sm" role="alert"><x-icon name="alert" class="mt-0.5 h-5 w-5" /><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
