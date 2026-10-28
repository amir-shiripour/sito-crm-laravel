@extends('layouts.web')

@section('content')
<x-contentforge::seo :post="$post" />

<div class="max-w-4xl mx-auto px-6 w-full pt-32 pb-16 space-y-12">
    {{-- Header --}}
    <div class="space-y-4 text-center">
        <h1 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white leading-tight">
            {{ $post->title }}
        </h1>
    </div>

    {{-- Cover image --}}
    @if($post->cover_image)
        <div class="rounded-3xl overflow-hidden aspect-[21/9] border dark:border-gray-700/50 shadow-md">
            <img src="{{ asset('storage/' . $post->cover_image) }}" class="w-full h-full object-cover">
        </div>
    @endif

    {{-- Content Body --}}
    <div class="prose prose-indigo dark:prose-invert max-w-none text-right leading-relaxed text-gray-800 dark:text-gray-200">
        {!! $post->body_html !!}
    </div>

    {{-- Gallery --}}
    @if(!empty($post->gallery))
        <div class="border-t pt-8 space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-white">گالری تصاویر</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($post->gallery as $img)
                    <a href="{{ asset('storage/' . $img) }}" target="_blank" class="rounded-2xl overflow-hidden aspect-video border dark:border-gray-700/50 block hover:opacity-90 transition-opacity">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
