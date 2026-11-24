@extends('layouts.app', ['title' => 'Account settings'])
@section('content')
@include('portal.nav')
<section class="section max-w-2xl"><h1 class="text-3xl font-bold">Account settings</h1>
<form method="POST" action="{{ route('portal.settings.update') }}" class="card mt-6 space-y-4 p-6">@csrf @method('PUT')
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="label" for="first_name">First name</label><input id="first_name" name="first_name" class="input" value="{{ old('first_name', $user->first_name) }}" required></div>
        <div><label class="label" for="last_name">Last name</label><input id="last_name" name="last_name" class="input" value="{{ old('last_name', $user->last_name) }}" required></div>
    </div>
    <div><label class="label" for="email">Email (contact support to change)</label><input id="email" class="input bg-brand-offwhite" value="{{ $user->email }}" disabled></div>
    <div><label class="label" for="phone">Phone</label><input id="phone" name="phone" class="input" value="{{ old('phone', $user->phone) }}" required></div>
    <div><label class="label" for="address">Address</label><input id="address" name="address" class="input" value="{{ old('address', $user->profile?->address) }}"></div>
    <div class="grid gap-4 sm:grid-cols-2"><div><label class="label" for="city">City</label><input id="city" name="city" class="input" value="{{ old('city', $user->profile?->city) }}"></div><div><label class="label" for="postal_code">Postal code</label><input id="postal_code" name="postal_code" class="input" value="{{ old('postal_code', $user->profile?->postal_code) }}"></div></div>
    <hr><p class="text-sm text-brand-graphite">Change password (leave blank to keep the current one)</p>
    <div class="grid gap-4 sm:grid-cols-2"><div><label class="label" for="password">New password</label><input id="password" type="password" name="password" class="input" autocomplete="new-password"></div><div><label class="label" for="password_confirmation">Confirm</label><input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password"></div></div>
    @if ($errors->any())<ul class="list-disc pl-5 text-sm text-red-600">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
    <button class="btn-primary"><x-icon name="check" class="h-4 w-4" />Save changes</button>
</form></section>
@endsection
