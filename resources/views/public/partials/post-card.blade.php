{{-- Article card. Var: $post (BlogPost with business loaded, optional). --}}
<a href="{{ route('blog.post', $post->slug) }}" class="card group flex flex-col overflow-hidden">
    <span class="block aspect-[16/10] overflow-hidden bg-brand-offwhite">
        <img src="{{ $post->image_url ?: asset('images/placeholder.svg') }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
    </span>
    <span class="flex flex-1 flex-col p-5">
        <span class="flex flex-wrap items-center gap-x-3 text-xs font-semibold uppercase tracking-wide text-brand-orange">
            @if ($post->business){{ $post->business->name }}@else News @endif
            <span class="font-normal normal-case text-gray-500">{{ $post->published_at?->format('j M Y') }} &middot; {{ $post->read_minutes }} min read</span>
        </span>
        <span class="mt-2 text-lg font-semibold group-hover:text-brand-orange">{{ $post->title }}</span>
        @if ($post->excerpt)<span class="mt-2 text-sm text-brand-graphite">{{ \Illuminate\Support\Str::limit($post->excerpt, 140) }}</span>@endif
        <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-bold text-brand-orange">Read more<x-icon name="arrow-right" class="h-4 w-4" /></span>
    </span>
</a>
