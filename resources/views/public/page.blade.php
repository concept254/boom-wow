@extends('layouts.app', ['title' => $page->meta_title ?: $page->title, 'description' => $page->meta_description])
@section('content')
<section class="section max-w-3xl"><h1 class="text-4xl font-bold">{{ $page->title }}</h1>
<div class="mt-6 space-y-4 text-brand-graphite">@foreach (preg_split('/\n\s*\n/', (string) $page->content) as $p)@if(trim($p))<p>{{ $p }}</p>@endif @endforeach</div></section>
@endsection
