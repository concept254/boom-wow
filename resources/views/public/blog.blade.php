@extends('layouts.app', ['title' => $filter ? $filter->name.' news' : 'News'])
@section('content')
<x-page-header slug="blog" :title="$filter ? $filter->name.' - news & articles' : 'News'" subtitle="Updates, offers and stories from our businesses." :crumbs="$filter ? [['News', route('blog')], [$filter->name, route('business', $filter)]] : []" />
<section class="section">
    <div class="flex flex-wrap gap-2 text-sm font-bold" aria-label="Filter by business">
        <a href="{{ route('blog') }}" class="rounded-full px-4 py-1.5 {{ $filter ? 'bg-brand-offwhite text-brand-graphite hover:text-brand-orange' : 'bg-brand-orange text-brand-white' }}">All</a>
        @foreach ($businesses as $b)<a href="{{ route('blog', ['business' => $b->slug]) }}" class="rounded-full px-4 py-1.5 {{ $filter?->id === $b->id ? 'bg-brand-orange text-brand-white' : 'bg-brand-offwhite text-brand-graphite hover:text-brand-orange' }}">{{ $b->name }}</a>@endforeach
    </div>
    @if ($posts->isEmpty())
        <p class="mt-10 text-brand-graphite">Nothing published here yet. Please check back soon.</p>
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($posts as $post)@include('public.partials.post-card', ['post' => $post])@endforeach</div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @endif
</section>
<x-ad-slot placement="inner_banner" />
<section class="bg-brand-orange text-brand-white"><div class="section !py-10"><div class="mx-auto max-w-3xl text-center"><h2 class="text-3xl !text-brand-white">Get our news and specials</h2><div class="text-left">@include('public.partials.subscribe-form')</div></div></div></section>
@endsection
