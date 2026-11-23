@extends('layouts.app', ['title' => 'Reset password'])
@section('content')
<section class="mx-auto max-w-md px-4 py-16"><div class="card p-8"><h1 class="text-2xl font-bold">Choose a new password</h1>
<form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">@csrf<input type="hidden" name="token" value="{{ $token }}">
<div><label class="label" for="email">Email</label><input id="email" type="email" name="email" class="input" required value="{{ old('email', $email) }}">@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
<div><label class="label" for="password">New password</label><input id="password" type="password" name="password" class="input" required>@error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
<div><label class="label" for="pc">Confirm password</label><input id="pc" type="password" name="password_confirmation" class="input" required></div>
<button class="btn-primary w-full">Reset password</button></form></div></section>
@endsection
