@extends('layouts.app', ['title' => 'News'])
@section('content')
<section class="section"><h1 class="text-4xl font-bold">News</h1><div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($posts as $p)<a class="card p-5" href="{{ route('blog.post', $p->slug) }}"><h2 class="text-lg font-semibold">{{ $p->title }}</h2><p class="mt-2 text-sm text-brand-graphite">{{ $p->excerpt }}</p></a>@endforeach</div><div class="mt-8">{{ $posts->links() }}</div></section>
<x-ad-slot placement="inner_banner" />
<section class="bg-brand-orange text-brand-white"><div class="section !py-10"><div class="mx-auto max-w-3xl text-center"><h2 class="text-3xl !text-brand-white">Get our news and specials</h2><div class="text-left">@include('public.partials.subscribe-form')</div></div></div></section>
@endsection
