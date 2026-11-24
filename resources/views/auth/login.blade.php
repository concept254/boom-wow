@extends('layouts.app', ['title' => 'Login'])
@section('content')
<section class="mx-auto max-w-md px-4 py-16"><div class="card p-8"><div class="mb-6 flex justify-center"><x-logo variant="light" class="h-20 w-auto" /></div>
<h1 class="text-2xl font-bold">Sign in</h1>
<form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">@csrf
<div><label class="label" for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autofocus autocomplete="username">@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
<div><label class="label" for="password">Password</label><input id="password" type="password" name="password" class="input" required autocomplete="current-password"></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
<button class="btn-primary w-full"><x-icon name="log-in" class="h-4 w-4" />Sign in</button></form>
<p class="mt-4 text-sm"><a class="text-brand-orange" href="{{ route('password.request') }}">Forgot password?</a> · <a class="text-brand-orange" href="{{ route('register') }}">Create account</a></p></div></section>
@endsection
