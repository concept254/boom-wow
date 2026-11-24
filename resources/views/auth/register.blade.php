@extends('layouts.app', ['title' => 'Register'])
@section('content')
<section class="mx-auto max-w-lg px-4 py-16"><div class="card p-8"><div class="mb-6 flex justify-center"><x-logo variant="light" class="h-20 w-auto" /></div>
<h1 class="text-2xl font-bold">Create your account</h1><p class="mt-1 text-sm text-brand-graphite">One account for all Boom &amp; Wow businesses.</p>
<form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">@csrf
<div class="grid gap-4 sm:grid-cols-2">@foreach ([['first_name', 'First name'], ['last_name', 'Last name']] as [$n, $l])<div><label class="label" for="{{ $n }}">{{ $l }}</label><input id="{{ $n }}" name="{{ $n }}" value="{{ old($n) }}" class="input" required>@error($n)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>@endforeach</div>
<div><label class="label" for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autocomplete="email">@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
<div><label class="label" for="phone">Phone number</label><input id="phone" type="tel" name="phone" value="{{ old('phone') }}" class="input" required>@error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
<div class="grid gap-4 sm:grid-cols-2"><div><label class="label" for="password">Password</label><input id="password" type="password" name="password" class="input" required autocomplete="new-password">@error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div><div><label class="label" for="pc">Confirm password</label><input id="pc" type="password" name="password_confirmation" class="input" required autocomplete="new-password"></div></div>
<label class="flex items-start gap-2 text-sm"><input type="checkbox" name="terms" value="1" class="mt-1" required> <span>I accept the <a class="text-brand-orange" href="{{ route('terms') }}" target="_blank">Terms</a> and <a class="text-brand-orange" href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>.</span></label>@error('terms')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
<button class="btn-primary w-full"><x-icon name="user-plus" class="h-4 w-4" />Register</button></form></div></section>
@endsection
