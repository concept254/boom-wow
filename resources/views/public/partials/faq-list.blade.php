@if ($faqs->isNotEmpty())
<div class="space-y-3" x-data="{ open: null }">
    @foreach ($faqs as $faq)
        <div class="rounded-2xl bg-brand-white ring-1 ring-black/5">
            <h3>
                <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-base font-semibold" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="open === {{ $faq->id }}">
                    <span>{{ $faq->question }}</span>
                    <span class="text-xl leading-none text-brand-orange" x-text="open === {{ $faq->id }} ? '−' : '+'" aria-hidden="true">+</span>
                </button>
            </h3>
            <div x-cloak x-show="open === {{ $faq->id }}" x-transition class="px-5 pb-5 text-sm text-brand-graphite">{{ $faq->answer }}</div>
        </div>
    @endforeach
</div>
@endif
