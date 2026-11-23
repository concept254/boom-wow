@extends('layouts.app', ['title' => $post->meta_title ?: $post->title, 'description' => $post->meta_description])
@section('content')
<article class="section max-w-3xl"><h1 class="text-4xl font-bold">{{ $post->title }}</h1><p class="mt-2 text-sm text-gray-500">{{ $post->published_at?->format('j F Y') }}</p><div class="mt-6 space-y-4 text-brand-graphite">@foreach (preg_split('/\n\s*\n/', (string) $post->body) as $p)@if(trim($p))<p>{{ $p }}</p>@endif @endforeach</div></article>
@endsection
