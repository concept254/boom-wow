@extends('layouts.admin', ['title' => 'Users'])
@section('content')
<details class="mb-6 rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5" @if ($errors->any()) open @endif>
    <summary class="cursor-pointer font-semibold text-brand-orange">+ New back-office user</summary>
    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">@csrf
        <div><label class="label" for="first_name">First name</label><input id="first_name" name="first_name" class="input" required value="{{ old('first_name') }}"></div>
        <div><label class="label" for="last_name">Last name</label><input id="last_name" name="last_name" class="input" required value="{{ old('last_name') }}"></div>
        <div><label class="label" for="email">Email</label><input id="email" type="email" name="email" class="input" required value="{{ old('email') }}"></div>
        <div><label class="label" for="phone">Phone</label><input id="phone" name="phone" class="input" value="{{ old('phone') }}"></div>
        <div><label class="label" for="password">Password (min 8, letters + numbers)</label><input id="password" type="password" name="password" class="input" required autocomplete="new-password"></div>
        <div><label class="label" for="role">Role</label><select id="role" name="role" class="input">@foreach ($roles as $r)<option>{{ $r }}</option>@endforeach</select></div>
        <fieldset class="sm:col-span-2"><legend class="label">Businesses (Business Manager / Staff)</legend><div class="flex flex-wrap gap-4">@foreach ($businesses as $b)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="businesses[]" value="{{ $b->id }}" class="rounded border-gray-300 text-brand-orange focus:ring-brand-orange"> {{ $b->name }}</label>@endforeach</div></fieldset>
        <div class="sm:col-span-2"><button class="btn-primary"><x-icon name="user-plus" class="h-4 w-4" />Create user</button></div>
    </form>
</details>
<form method="GET" class="mb-4 flex items-end gap-2" role="search"><div><label class="label" for="q">Email</label><input id="q" name="q" value="{{ request('q') }}" class="input"></div><button class="btn-dark">Search</button></form>
<div class="space-y-3">
@forelse ($users as $u)
    <form method="POST" action="{{ route('admin.users.update', $u) }}" class="rounded-lg bg-brand-white p-4 shadow-sm ring-1 ring-black/5">@csrf @method('PATCH')
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><p class="font-semibold">{{ $u->name }}</p><p class="text-sm text-brand-graphite">{{ $u->email }}</p></div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <select name="role" class="input w-44 py-1" aria-label="Role">@foreach ($roles as $r)<option @selected($u->hasRole($r))>{{ $r }}</option>@endforeach</select>
                <input type="hidden" name="is_active" value="0"><label class="flex items-center gap-1"><input type="checkbox" name="is_active" value="1" @checked($u->is_active) class="rounded border-gray-300 text-brand-orange focus:ring-brand-orange"> Active</label>
                <button class="btn-dark px-3 py-1">Save</button>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-4 text-sm">@foreach ($businesses as $b)<label class="flex items-center gap-2"><input type="checkbox" name="businesses[]" value="{{ $b->id }}" @checked($u->businesses->contains($b->id)) class="rounded border-gray-300 text-brand-orange focus:ring-brand-orange"> {{ $b->name }}</label>@endforeach</div>
    </form>
@empty<p class="text-brand-graphite">No back-office users.</p>@endforelse
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
