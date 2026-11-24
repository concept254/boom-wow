<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? \App\Services\SettingsService::get('seo_title', 'Boom & Wow Holdings') }}</title>
    <meta name="description" content="{{ $description ?? \App\Services\SettingsService::get('seo_description', 'Exceptional services, products and experiences under one roof.') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,600,700|dm-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-white">
    <x-top-bar />
    @include('partials.header')
    @if (session('status'))
        <div class="bg-brand-offwhite"><div class="mx-auto flex max-w-7xl items-center gap-2 px-4 py-3 text-sm text-brand-charcoal sm:px-6 lg:px-8" role="status"><x-icon name="check-circle" class="h-5 w-5 text-brand-orange" />{{ session('status') }}</div></div>
    @endif
    <main>@yield('content')</main>
    @include('partials.footer')
    @php($wa = preg_replace('/\D/', '', (string) \App\Services\SettingsService::get('whatsapp_number', '')))
    @if ($wa)
        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"
           class="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-green-500 text-brand-white shadow-lg transition hover:scale-105 hover:bg-green-600">
            <x-icon name="whatsapp" class="h-7 w-7" />
        </a>
    @endif
</body>
</html>
