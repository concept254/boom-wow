@extends('layouts.admin', ['title' => 'Dashboard'])
@section('content')
@php
    // Chart data is built here because Blade cannot parse inline arrow functions inside directive arguments.
    $salesChart = ['type' => 'bar', 'label' => 'Sales (R)', 'labels' => $sales->pluck('name')->values(), 'data' => $sales->pluck('total')->map(fn ($v) => (float) $v)->values()];
    $bookingsChart = ['type' => 'doughnut', 'labels' => $bookingsByBusiness->pluck('name')->values(), 'data' => $bookingsByBusiness->pluck('total')->values()];
    $statIcons = ['Businesses' => 'store', 'Customers' => 'users', 'Services' => 'scissors', 'Products' => 'package', 'Low stock' => 'alert', 'Out of stock' => 'alert',
        'Pending bookings' => 'clock', 'Upcoming appointments' => 'calendar-check', 'Total orders' => 'bag', 'Pending orders' => 'clock', 'Completed orders' => 'check-circle', 'New enquiries' => 'message'];
@endphp
<div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
    @foreach ($stats as $label => $value)
        <div class="rounded-lg bg-brand-white p-4 shadow-sm ring-1 ring-black/5">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-graphite">{{ $label }}</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-brand-orange/10 text-brand-orange"><x-icon :name="$statIcons[$label] ?? 'chart'" class="h-4 w-4" /></span>
            </div>
            <p class="mt-2 font-display text-3xl font-bold {{ in_array($label, ['Low stock', 'Out of stock', 'New enquiries', 'Pending bookings', 'Pending orders']) && (int) $value > 0 ? 'text-brand-orange' : 'text-brand-charcoal' }}">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section class="rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5" aria-labelledby="sales-h">
        <h2 id="sales-h" class="mb-3 flex items-center gap-2 text-lg font-bold"><x-icon name="chart" class="h-5 w-5 text-brand-orange" />Sales by business (R)</h2>
        @if ($sales->isEmpty())<p class="text-sm text-brand-graphite">No sales yet.</p>
        @else
            <canvas data-chart="{{ json_encode($salesChart) }}" height="160" aria-label="Sales by business chart" role="img"></canvas>
            <table class="mt-4 w-full text-sm"><thead class="text-left text-xs uppercase text-brand-graphite"><tr><th class="py-1">Business</th><th>Orders</th><th class="text-right">Sales</th></tr></thead>
            <tbody>@foreach ($sales as $s)<tr class="border-t"><td class="py-1.5">{{ $s->name }}</td><td>{{ $s->orders }}</td><td class="text-right">R{{ number_format($s->total, 2) }}</td></tr>@endforeach</tbody></table>
        @endif
    </section>
    <section class="rounded-lg bg-brand-white p-5 shadow-sm ring-1 ring-black/5" aria-labelledby="book-h">
        <h2 id="book-h" class="mb-3 flex items-center gap-2 text-lg font-bold"><x-icon name="calendar-check" class="h-5 w-5 text-brand-orange" />Bookings by business</h2>
        @if ($bookingsByBusiness->isEmpty())<p class="text-sm text-brand-graphite">No bookings yet.</p>
        @else
            <div class="mx-auto max-w-xs"><canvas data-chart="{{ json_encode($bookingsChart) }}" aria-label="Bookings by business chart" role="img"></canvas></div>
        @endif
    </section>
</div>
@endsection
