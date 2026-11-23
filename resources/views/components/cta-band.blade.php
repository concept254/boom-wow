@props(['heading', 'text' => null, 'label' => null, 'url' => null, 'label2' => null, 'url2' => null, 'tone' => 'dark'])
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="{{ $tone === 'orange' ? 'bg-brand-orange text-brand-white' : 'bg-brand-charcoal text-brand-white' }} flex flex-col items-start gap-6 rounded-3xl px-6 py-9 sm:px-10 md:flex-row md:items-center md:justify-between">
        <div class="max-w-2xl">
            <h2 class="text-2xl !text-brand-white sm:text-3xl">{{ $heading }}</h2>
            @if ($text)<p class="mt-2 text-sm opacity-90 sm:text-base">{{ $text }}</p>@endif
        </div>
        <div class="flex flex-wrap gap-3">
            @if ($label)<a href="{{ \App\Support\Link::href($url) }}" class="{{ $tone === 'orange' ? 'btn-light' : 'btn-primary' }}">{{ $label }}</a>@endif
            @if ($label2)<a href="{{ \App\Support\Link::href($url2) }}" class="btn-outline-light">{{ $label2 }}</a>@endif
        </div>
    </div>
</section>
