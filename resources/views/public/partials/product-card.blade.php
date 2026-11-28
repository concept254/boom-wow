<div class="card group flex flex-col overflow-hidden">
    <a href="{{ route('product', [$product->business, $product->slug]) }}" class="relative block aspect-[4/3] overflow-hidden bg-brand-offwhite">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 {{ $product->hover_image_url ? 'group-hover:opacity-0' : 'group-hover:scale-105' }}">
        @if ($product->hover_image_url)<img src="{{ $product->hover_image_url }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-0 transition duration-300 group-hover:opacity-100">@endif
        @if ($product->discount_percent > 0)<span class="badge absolute left-3 top-3 bg-brand-orange text-brand-white">-{{ $product->discount_percent }}%</span>@endif
        @if ($product->price_on_request)<span class="badge absolute right-3 top-3 bg-brand-charcoal text-brand-white">Exclusive</span>@elseif (! $product->in_stock)<span class="badge absolute right-3 top-3 bg-brand-charcoal text-brand-white">Sold out</span>@endif
    </a>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-orange">{{ $product->business->name }}</p>
        <a href="{{ route('product', [$product->business, $product->slug]) }}" class="mt-1 font-semibold hover:text-brand-orange">{{ $product->name }}</a>
        @if (($product->rating_count ?? 0) > 0)<x-rating-stars :value="$product->rating_avg" :count="'('.$product->rating_count.')'" class="mt-1.5" />@endif
        @if ($product->price_on_request)
            <p class="mt-2 text-sm font-bold text-brand-graphite">Price on request</p>
            <a href="{{ $product->quote_url }}" class="btn-outline mt-auto w-full !mt-4"><x-icon name="message" class="h-4 w-4" />Request a quote</a>
        @else
        <p class="mt-2 text-sm">
            <span class="text-base font-bold">R{{ number_format($product->current_price, 2) }}</span>
            @if ($product->current_price != $product->price)<span class="ml-1 text-gray-400 line-through">R{{ number_format($product->price, 2) }}</span> @endif
        </p>
        @if ($product->in_stock)
            <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-auto pt-4">@csrf<button class="btn-primary w-full"><x-icon name="cart" class="h-4 w-4" />Add to cart</button></form>
        @else
            <p class="mt-auto flex items-center gap-1.5 pt-4 text-sm text-gray-500"><x-icon name="alert" class="h-4 w-4" />Out of stock</p>
        @endif
        @endif
    </div>
</div>
