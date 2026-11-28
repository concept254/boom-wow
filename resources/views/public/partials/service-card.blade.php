<div class="card group flex flex-col overflow-hidden">
    <a href="{{ route('service', $svc) }}" class="block aspect-[4/3] overflow-hidden bg-brand-offwhite"><img src="{{ $svc->image_url }}" alt="{{ $svc->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105"></a>
    <div class="flex flex-1 flex-col p-4">
        <p class="flex items-center gap-1.5 text-xs font-semibold uppercase text-brand-orange"><x-icon :name="$svc->business->icon_name" class="h-4 w-4" />{{ $svc->business->name }}</p>
        <a href="{{ route('service', $svc) }}" class="mt-1 text-lg font-semibold hover:text-brand-orange">{{ $svc->name }}</a>
        @if (($svc->rating_count ?? 0) > 0)<x-rating-stars :value="$svc->rating_avg" :count="'('.$svc->rating_count.')'" class="mt-1.5" />@endif
        <p class="mt-2 text-sm text-brand-graphite"><strong class="text-brand-charcoal">{{ $svc->price_text }}</strong> · {{ $svc->duration_minutes }} min</p>
        <div class="mt-auto flex flex-wrap gap-2 pt-4">
            @if ($svc->is_bookable && $svc->business->accepts_bookings)<a href="{{ route('portal.bookings.new', ['service' => $svc->id]) }}" class="btn-primary"><x-icon name="calendar-check" class="h-4 w-4" />Book now</a>@else<a href="{{ route('service', $svc) }}" class="btn-outline"><x-icon name="message" class="h-4 w-4" />Enquire</a>@endif
            <a href="{{ route('service', $svc) }}" class="btn-outline">Details</a>
        </div>
    </div>
</div>
