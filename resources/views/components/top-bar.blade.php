@php($bar = \App\Models\Promotion::forPlacement('top_bar')->first())
@if ($bar)
    <div class="bg-brand-orange text-brand-white" x-data="{ show: true }" x-show="show">
        <div class="mx-auto flex max-w-7xl items-center justify-center gap-3 px-4 py-2 text-center text-sm font-bold sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2"><x-icon name="megaphone" class="h-4 w-4" /><span>{{ $bar->title }}@if ($bar->description) <span class="hidden font-semibold opacity-90 sm:inline">- {{ $bar->description }}</span>@endif</span></span>
            @if ($bar->link_url)<a href="{{ \App\Support\Link::href($bar->link_url) }}" class="underline underline-offset-2 hover:no-underline">{{ $bar->cta_label ?: 'Find out more' }}</a>@endif
            <button type="button" class="ml-1 text-lg leading-none opacity-80 hover:opacity-100" @click="show = false" aria-label="Dismiss"><x-icon name="x" class="h-4 w-4" /></button>
        </div>
    </div>
@endif
