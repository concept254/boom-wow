@props(['status'])
@php
    $map = ['published' => 'bg-green-100 text-green-800', 'completed' => 'bg-green-100 text-green-800', 'paid' => 'bg-green-100 text-green-800', 'confirmed' => 'bg-green-100 text-green-800', 'resolved' => 'bg-green-100 text-green-800', 'available' => 'bg-green-100 text-green-800',
        'pending' => 'bg-amber-100 text-amber-800', 'new' => 'bg-amber-100 text-amber-800', 'draft' => 'bg-amber-100 text-amber-800', 'processing' => 'bg-amber-100 text-amber-800', 'in_progress' => 'bg-amber-100 text-amber-800', 'reschedule_requested' => 'bg-amber-100 text-amber-800', 'ready' => 'bg-orange-100 text-brand-orange-dark',
        'cancelled' => 'bg-red-100 text-red-800', 'failed' => 'bg-red-100 text-red-800', 'no_show' => 'bg-red-100 text-red-800', 'archived' => 'bg-gray-200 text-gray-700'];
@endphp
<span class="badge {{ $map[$status] ?? 'bg-gray-100 text-gray-700' }}">{{ str_replace('_', ' ', $status) }}</span>
