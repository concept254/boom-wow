@php
    $payload = $businesses->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'services' => $b->services->map(fn ($s) => [
        'id' => $s->id, 'name' => $s->name, 'duration' => $s->duration_minutes, 'price' => $s->price,
        'staff' => $s->staff->where('is_active', true)->where('is_bookable', true)->map(fn ($p) => ['id' => $p->id, 'name' => $p->display_name])->values(),
    ])->values()])->values();
    $preset = ['businessId' => $service?->business_unit_id ?? (int) request('business', 0) ?: '', 'serviceId' => $service?->id ?? ''];
@endphp
@extends('layouts.app', ['title' => 'Book an appointment'])
@section('content')
@include('portal.nav')
<section class="section max-w-3xl" x-data="bookingForm(@js($payload), @js(route('portal.bookings.slots')), @js($preset))">
    <h1 class="text-3xl font-bold">Book an appointment</h1>
    <form method="POST" action="{{ route('portal.bookings.store') }}" class="card mt-6 space-y-5 p-6">@csrf
        <div><label class="label" for="biz">1. Business</label>
            <select id="biz" class="input" x-model="businessId" @change="resetFrom(1)"><option value="">Select a business…</option><template x-for="b in businesses" :key="b.id"><option :value="b.id" x-text="b.name"></option></template></select></div>
        <div x-show="businessId" x-cloak><label class="label" for="svc">2. Service</label>
            <select id="svc" name="service_id" class="input" x-model="serviceId" @change="resetFrom(2)" required><option value="">Select a service…</option><template x-for="s in services" :key="s.id"><option :value="s.id" x-text="`${s.name} — ${s.duration} min${s.price ? ' — R' + Number(s.price).toFixed(2) : ''}`"></option></template></select></div>
        <div x-show="serviceId && staff.length" x-cloak><label class="label" for="stf">3. Staff member (optional)</label>
            <select id="stf" name="staff_profile_id" class="input" x-model="staffId" @change="date=''; time=''; slots=[]"><option value="">Any available</option><template x-for="p in staff" :key="p.id"><option :value="p.id" x-text="p.name"></option></template></select></div>
        <div x-show="serviceId" x-cloak><label class="label" for="dt">4. Date</label><input id="dt" type="date" name="date" class="input" x-model="date" @change="loadSlots()" min="{{ now()->toDateString() }}" max="{{ now()->addDays((int) config('boomwow.booking.max_days_ahead', 60))->toDateString() }}" required></div>
        <div x-show="date" x-cloak><p class="label">5. Time</p>
            <p x-show="loading" class="text-sm text-brand-graphite">Checking availability…</p>
            <p x-show="message" x-text="message" class="text-sm text-brand-orange-dark"></p>
            <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Available times"><template x-for="s in slots" :key="s"><label class="cursor-pointer"><input type="radio" name="time" :value="s" x-model="time" class="peer sr-only" required><span class="block rounded border border-gray-300 px-3 py-1.5 text-sm peer-checked:border-brand-orange peer-checked:bg-brand-orange peer-checked:text-brand-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-orange" x-text="s"></span></label></template></div>
            @error('time')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div x-show="time" x-cloak><label class="label" for="nt">Notes (optional)</label><textarea id="nt" name="notes" rows="3" maxlength="1000" class="input">{{ old('notes') }}</textarea></div>
        <button class="btn-primary" :disabled="!time" :class="{ 'opacity-50': !time }"><x-icon name="check-circle" class="h-4 w-4" />Confirm booking</button>
    </form>
</section>
@endsection
