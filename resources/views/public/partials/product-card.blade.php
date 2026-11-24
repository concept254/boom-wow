<div class="card flex flex-col overflow-hidden">
    <a href="{{ route('product', [$product->business, $product->slug]) }}">
        <img src="{{ $product->images->first() ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->images->first()->path) : 'https://picsum.photos/seed/'.$product->slug.'/600/400' }}" alt="{{ $product->name }}" loading="lazy" class="h-40 w-full object-cover">
    </a>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-orange">{{ $product->business->name }}</p>
        <a href="{{ route('product', [$product->business, $product->slug]) }}" class="mt-1 font-semibold hover:text-brand-orange">{{ $product->name }}</a>
        <p class="mt-2 text-sm">
            @if ($product->current_price != $product->price)<span class="text-gray-400 line-through">R{{ number_format($product->price, 2) }}</span> @endif
            <span class="font-semibold">R{{ number_format($product->current_price, 2) }}</span>
        </p>
        @if ($product->in_stock)
            <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-auto pt-4">@csrf<button class="btn-primary w-full"><x-icon name="cart" class="h-4 w-4" />Add to cart</button></form>
        @else
            <p class="mt-auto flex items-center gap-1.5 pt-4 text-sm text-gray-500"><x-icon name="alert" class="h-4 w-4" />Out of stock</p>
        @endif
    </div>
</div>
