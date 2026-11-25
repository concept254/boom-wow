{{-- Reviews block. Vars: $item (Product|Service), $kind ('product'|'service'), $stats, $reviews, $userReview, $verified --}}
<div id="reviews" class="scroll-mt-24">
    <div class="grid gap-8 lg:grid-cols-[18rem_1fr]">
        <div>
            <h3 class="text-xl">Customer reviews</h3>
            @if ($stats['count'] > 0)
                <div class="mt-3 flex items-center gap-3">
                    <span class="font-display text-5xl font-bold text-brand-charcoal">{{ number_format($stats['avg'], 1) }}</span>
                    <div><x-rating-stars :value="$stats['avg']" size="h-5 w-5" /><p class="mt-1 text-sm text-brand-graphite">{{ $stats['count'] }} {{ \Illuminate\Support\Str::plural('review', $stats['count']) }}</p></div>
                </div>
                <ul class="mt-5 space-y-2 text-sm">
                    @foreach ($stats['dist'] as $star => $n)
                        <li class="flex items-center gap-2"><span class="w-10 shrink-0 font-semibold">{{ $star }} star</span>
                            <span class="h-2.5 flex-1 overflow-hidden rounded-full bg-gray-200"><span class="block h-full rounded-full bg-brand-orange" style="width: {{ $stats['count'] ? round($n / $stats['count'] * 100) : 0 }}%"></span></span>
                            <span class="w-6 text-right text-brand-graphite">{{ $n }}</span></li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 text-sm text-brand-graphite">No reviews yet. Be the first to share your experience.</p>
            @endif
        </div>

        <div>
            @forelse ($reviews as $r)
                <article class="border-b border-gray-200 py-5 first:pt-0 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-orange/10 font-bold text-brand-orange" aria-hidden="true">{{ mb_strtoupper(mb_substr($r->name, 0, 1)) }}</span>
                        <div><p class="text-sm font-bold">{{ $r->name }}</p><p class="text-xs text-gray-500">{{ $r->created_at->format('j M Y') }}</p></div>
                        @if ($r->is_verified)<span class="badge ml-auto bg-green-100 text-green-800"><x-icon name="check-circle" class="mr-1 h-3.5 w-3.5" />Verified {{ $kind === 'product' ? 'purchase' : 'booking' }}</span>@endif
                    </div>
                    <div class="mt-3 flex items-center gap-2"><x-rating-stars :value="$r->rating" />@if ($r->title)<span class="font-bold">{{ $r->title }}</span>@endif</div>
                    <p class="mt-2 whitespace-pre-line text-sm text-brand-graphite">{{ $r->body }}</p>
                    @if ($r->reply)<div class="mt-3 rounded bg-brand-offwhite p-3 text-sm"><p class="flex items-center gap-1.5 font-bold"><x-icon name="message" class="h-4 w-4 text-brand-orange" />Reply from {{ $item->business->name }}</p><p class="mt-1 whitespace-pre-line text-brand-graphite">{{ $r->reply }}</p></div>@endif
                </article>
            @empty
            @endforelse

            <div class="mt-6 rounded bg-brand-offwhite p-5">
                @auth
                    @if ($userReview)
                        <p class="flex items-center gap-2 text-sm font-semibold"><x-icon name="check-circle" class="h-5 w-5 text-brand-orange" />
                            @if ($userReview->status === 'published') You reviewed this - thank you!
                            @elseif ($userReview->status === 'rejected') Your review was not approved.
                            @else Thanks! Your review is waiting for approval. @endif
                        </p>
                    @else
                        <h4 class="text-lg">Write a review</h4>
                        <p class="text-xs text-brand-graphite">@if ($verified) You will get a "Verified {{ $kind === 'product' ? 'purchase' : 'booking' }}" badge. @else Share your honest experience to help other customers. @endif</p>
                        <form method="POST" action="{{ route('reviews.store') }}" class="mt-4 space-y-4" x-data="{ r: {{ (int) old('rating', 0) }}, h: 0 }">
                            @csrf
                            <input type="hidden" name="type" value="{{ $kind }}"><input type="hidden" name="id" value="{{ $item->id }}"><input type="hidden" name="rating" x-bind:value="r">
                            <div>
                                <p class="label">Your rating</p>
                                <div class="flex gap-1" role="radiogroup" aria-label="Rating" x-on:mouseleave="h = 0">
                                    @foreach (range(1, 5) as $n)
                                        <button type="button" x-on:click="r = {{ $n }}" x-on:mouseenter="h = {{ $n }}" aria-label="{{ $n }} {{ $n === 1 ? 'star' : 'stars' }}" role="radio" x-bind:aria-checked="r === {{ $n }}" class="text-gray-300" x-bind:class="(h || r) >= {{ $n }} ? '!text-brand-orange' : ''"><x-icon name="star" class="h-8 w-8 fill-current" /></button>
                                    @endforeach
                                </div>
                                @error('rating')<p class="mt-1 text-xs text-red-600">Please choose a star rating.</p>@enderror
                            </div>
                            <div><label class="label" for="rv-title">Headline (optional)</label><input id="rv-title" name="title" maxlength="150" value="{{ old('title') }}" class="input" placeholder="Sum it up in a few words"></div>
                            <div><label class="label" for="rv-body">Your review</label><textarea id="rv-body" name="body" rows="4" minlength="10" maxlength="2000" required class="input" placeholder="What did you like or dislike?">{{ old('body') }}</textarea>@error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                            <button class="btn-primary"><x-icon name="send" class="h-4 w-4" />Submit review</button>
                            <p class="text-xs text-gray-500">Reviews are published after a quick check by our team.</p>
                        </form>
                    @endif
                @else
                    <p class="flex flex-wrap items-center gap-3 text-sm"><x-icon name="star" class="h-5 w-5 text-brand-orange" /><span class="font-semibold">Bought or booked this?</span> <a href="{{ route('login') }}" class="btn-outline !py-2">Sign in to write a review</a></p>
                @endauth
            </div>
        </div>
    </div>
</div>
