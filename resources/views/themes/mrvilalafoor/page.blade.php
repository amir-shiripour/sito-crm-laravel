@extends('layouts.web')

@section('content')
<x-contentforge::seo :post="$post" />

<article class="max-w-4xl mx-auto px-4 sm:px-6 w-full pt-10 pb-16 space-y-8">
    <div class="space-y-4 text-center">
        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white leading-tight">
            {{ $post->title }}
        </h1>
        <div class="w-12 h-1 bg-[#007A5E] dark:bg-emerald-500 mx-auto rounded-full"></div>
    </div>

    @if($post->cover_image)
        <div class="rounded-3xl overflow-hidden aspect-[21/9] border border-gray-100 dark:border-zinc-800 shadow-md">
            <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="prose prose-emerald dark:prose-invert max-w-none text-right leading-relaxed text-gray-800 dark:text-gray-200 font-sans">
        {!! $post->body_html !!}
    </div>
</article>
@endsection
