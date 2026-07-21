@extends('layouts.web')

@section('content')
<x-contentforge::seo :post="$post" />

<article class="max-w-4xl mx-auto px-6 w-full pt-32 pb-16 space-y-12">
    {{-- Header --}}
    <div class="space-y-6 text-center">
        @if($post->category)
            <a href="{{ $post->category->url }}" class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950 px-3.5 py-1.5 rounded-full">
                {{ $post->category->name }}
            </a>
        @endif

        <h1 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white leading-tight">
            {{ $post->title }}
        </h1>

        <div class="flex items-center justify-center gap-4 text-xs text-gray-400 font-semibold">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center font-bold text-[10px]">
                    {{ mb_substr($post->author->name ?? 'م', 0, 1) }}
                </div>
                <span class="text-gray-600 dark:text-gray-300">{{ $post->author->name ?? 'مدیر سیستم' }}</span>
            </div>
            <span>•</span>
            <span>انتشار: {{ $post->published_at?->format('Y-m-d') ?? $post->created_at->format('Y-m-d') }}</span>
            <span>•</span>
            <span>زمان مطالعه: {{ $post->reading_time ?? 5 }} دقیقه</span>
            <span>•</span>
            <span>{{ $post->view_count }} بازدید</span>
        </div>
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

    {{-- Tags --}}
    @if($post->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2 pt-6 border-t border-gray-100 dark:border-gray-700">
            @foreach($post->tags as $tag)
                <a href="{{ $tag->url }}" class="text-xs text-gray-500 bg-gray-50 dark:bg-gray-850 dark:text-gray-400 hover:text-indigo-600 px-3 py-1.5 rounded-lg border transition-colors">
                    #{{ $tag->name }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Comments Section --}}
    @if($post->allow_comments && \Modules\ContentForge\Entities\ContentSetting::getValue('general.enable_comments', 'true') === 'true')
        <div class="border-t pt-8">
            @livewire('contentforge::web.comment-form', ['post' => $post])
            @livewire('contentforge::web.comment-list', ['post' => $post])
        </div>
    @endif
</article>
@endsection
