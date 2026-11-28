@extends('layouts.app', ['title' => $post->meta_title ?: $post->title, 'description' => $post->meta_description ?: $post->excerpt])
@php
    $url = route('blog.post', $post->slug);
    $ld = array_filter([
        '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $post->title, 'url' => $url,
        'datePublished' => $post->published_at?->toIso8601String(), 'image' => $post->image_url,
        'publisher' => ['@type' => 'Organization', 'name' => 'Boom & Wow Holdings'],
    ]);
@endphp
@push('head')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:url" content="{{ $url }}">
    @if ($post->image_url)<meta property="og:image" content="{{ $post->image_url }}">@endif
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
<x-page-header :title="$post->title" :subtitle="$post->published_at?->format('j F Y').' · '.$post->read_minutes.' min read'" :crumbs="$post->business ? [['News', route('blog')], [$post->business->name, route('business', $post->business)]] : [['News', route('blog')]]" />
<article class="section max-w-3xl">
    @if ($post->image_url)<img src="{{ $post->image_url }}" alt="" class="mb-8 w-full rounded-lg object-cover">@endif
    @if ($post->excerpt)<p class="text-lg font-semibold text-brand-charcoal">{{ $post->excerpt }}</p>@endif
    <div class="mt-6 space-y-4 text-brand-graphite">@foreach (preg_split('/\n\s*\n/', (string) $post->body) as $para)@if (trim($para))<p>{!! nl2br(e(trim($para))) !!}</p>@endif @endforeach</div>
    <div class="mt-10 border-t border-gray-200 pt-6">@include('public.partials.share', ['url' => $url, 'title' => $post->title])</div>
</article>
@if ($more->isNotEmpty())
<section class="bg-brand-offwhite"><div class="section">
    <h2 class="text-2xl sm:text-3xl">{{ $post->business ? 'More from '.$post->business->name : 'More news' }}</h2>
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($more as $m)@include('public.partials.post-card', ['post' => $m])@endforeach</div>
</div></section>
@endif
@endsection
