@extends('layouts.app', ['title' => 'FAQs'])
@section('content')
<x-page-header slug="faqs" title="Frequently asked questions" subtitle="Quick answers about bookings, orders and our businesses." />
<section class="section max-w-3xl">
<div class="mt-8 divide-y divide-gray-200">@foreach ($faqs as $f)<details class="py-4"><summary class="cursor-pointer font-semibold">{{ $f->question }}</summary><p class="mt-2 text-brand-graphite">{{ $f->answer }}</p></details>@endforeach</div></section>
<x-ad-slot placement="inner_banner" />
<x-cta-band heading="Still have questions?" text="Our friendly team is ready to help." label="Contact us" url="/contact" label2="WhatsApp & directions" url2="/contact" />
@endsection
