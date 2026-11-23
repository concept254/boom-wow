@extends('layouts.app', ['title' => 'Verify your email'])
@section('content')
<section class="mx-auto max-w-md px-4 py-16"><div class="card p-8"><h1 class="text-2xl font-bold">Verify your email</h1><p class="mt-3 text-sm text-brand-graphite">We sent a verification link to {{ auth()->user()->email }}. Click it to activate your account. In local development with MAIL_MAILER=log, find the link in <code>storage/logs/laravel.log</code>.</p>
<form method="POST" action="{{ route('verification.send') }}" class="mt-6">@csrf<button class="btn-primary">Resend email</button></form></div></section>
@endsection
