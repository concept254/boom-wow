@extends('layouts.app', ['title' => 'FAQs'])
@section('content')
<section class="section max-w-3xl"><h1 class="text-4xl font-bold">Frequently asked questions</h1>
<div class="mt-8 divide-y divide-gray-200">@foreach ($faqs as $f)<details class="py-4"><summary class="cursor-pointer font-semibold">{{ $f->question }}</summary><p class="mt-2 text-brand-graphite">{{ $f->answer }}</p></details>@endforeach</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Still have questions?" text="Our friendly team is ready to help." label="Contact us" url="/contact" label2="WhatsApp & directions" url2="/contact" />
@endsection
