@extends('layouts.app', ['title' => $post->meta_title ?: $post->title, 'description' => $post->meta_description])
@section('content')
<x-page-header :title="$post->title" :subtitle="$post->published_at?->format('j F Y')" :crumbs="[['News', route('blog')]]" />
<article class="section max-w-3xl"><div class="mt-6 space-y-4 text-brand-graphite">@foreach (preg_split('/\n\s*\n/', (string) $post->body) as $p)@if(trim($p))<p>{{ $p }}</p>@endif @endforeach</div></article>
@endsection
