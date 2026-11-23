@extends('layouts.admin', ['title' => 'Roles & Permissions'])
@section('content')
<p class="mb-4 max-w-3xl text-sm text-brand-graphite">Permissions are enforced on the server for every request. Super Admin always has every permission and cannot be edited. Business Managers and Staff are additionally limited to the business units they are assigned to, regardless of permissions.</p>
<div class="space-y-6">
@foreach ($roles as $role)
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5">@csrf @method('PATCH')
        <div class="mb-3 flex items-center justify-between"><h2 class="text-lg font-bold">{{ $role->name }}</h2>@unless (in_array($role->name, ['Super Admin']))<button class="btn-dark px-3 py-1">Save</button>@endunless</div>
        @if ($role->name === 'Super Admin')<p class="text-sm text-brand-graphite">Complete access to everything.</p>
        @elseif ($role->name === 'Customer')<p class="text-sm text-brand-graphite">Customers have no admin permissions. They manage only their own profile, bookings, orders and enquiries through the portal.</p>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($permissions as $group => $perms)
                <fieldset><legend class="text-xs font-semibold uppercase tracking-wider text-brand-orange">{{ $group }}</legend>
                @foreach ($perms as $p)<label class="mr-3 inline-flex items-center gap-1 text-sm"><input type="checkbox" name="permissions[]" value="{{ $p->name }}" @checked($role->permissions->contains('name', $p->name)) class="rounded border-gray-300 text-brand-orange focus:ring-brand-orange"> {{ \Illuminate\Support\Str::after($p->name, '.') }}</label>@endforeach</fieldset>
            @endforeach
            </div>
        @endif
    </form>
@endforeach
</div>
@endsection
