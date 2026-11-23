@extends('layouts.app', ['title' => 'Forgot password'])
@section('content')
<section class="mx-auto max-w-md px-4 py-16"><div class="card p-8"><h1 class="text-2xl font-bold">Reset your password</h1>
<form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">@csrf<div><label class="label" for="email">Email</label><input id="email" type="email" name="email" class="input" required value="{{ old('email') }}"></div><button class="btn-primary w-full">Email reset link</button></form></div></section>
@endsection
