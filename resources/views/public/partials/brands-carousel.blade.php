@php
    // Auto-scrolling logo strip. Manage in Admin > Brands. $scope: 'home' (flagged brands, default) or 'all'.
    // Pass $business to show only that business's brands. Hidden when there is nothing to show.
    $query = \App\Models\Brand::published();
    if (! empty($business)) {
        $query->where('business_unit_id', $business->id);
    } elseif (($scope ?? 'home') === 'home') {
        $query->where('show_on_home', true);
    }
    $brands = $query->get();
    $reps = $brands->isEmpty() ? 0 : max(1, (int) ceil(8 / $brands->count()));
    $duration = max(20, $brands->count() * $reps * 4);
    $heading = $heading ?? 'Brands we stock';
    $intro = $intro ?? 'Trusted names from across all of our stores.';
@endphp
@if ($brands->isNotEmpty())
<section class="border-y border-gray-200 bg-brand-white" aria-label="{{ $heading }}">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="eyebrow">{{ $heading }}</p>
            <p class="mt-2 text-sm text-brand-graphite">{{ $intro }}</p>
        </div>
        <div class="brand-marquee mt-6" style="--marquee-duration: {{ $duration }}s">
            <ul class="brand-marquee__track">
                @foreach (range(1, 2) as $copy)
                    @foreach (range(1, $reps) as $r)
                        @foreach ($brands as $brand)
                            <li class="brand-marquee__item" @if ($copy === 2 || $r > 1) aria-hidden="true" @endif>
                                @if ($brand->website_url)<a href="{{ $brand->website_url }}" target="_blank" rel="noopener nofollow" tabindex="{{ $copy === 2 || $r > 1 ? '-1' : '0' }}" title="{{ $brand->name }}" class="brand-marquee__link">@else<span class="brand-marquee__link" title="{{ $brand->name }}">@endif
                                    @if ($brand->logo_url)<img src="{{ $brand->logo_url }}" alt="{{ $copy === 1 && $r === 1 ? $brand->name : '' }}" loading="lazy" class="h-12 w-auto max-w-[9rem] object-contain">@else<span class="font-display text-lg uppercase tracking-wide text-brand-graphite">{{ $brand->name }}</span>@endif
                                @if ($brand->website_url)</a>@else</span>@endif
                            </li>
                        @endforeach
                    @endforeach
                @endforeach
            </ul>
        </div>
    </div>
</section>
@endif
