@extends('layouts.web')

@section('content')
<x-contentforge::seo :post="$post" />

<article class="max-w-4xl mx-auto px-4 sm:px-6 w-full pt-10 pb-16 space-y-10">
    {{-- Header --}}
    <div class="space-y-4 text-center">
        @if($post->category)
            <a href="{{ $post->category->url }}" class="inline-block text-xs font-bold text-[#007A5E] dark:text-emerald-400 bg-[#ECFDF5] dark:bg-emerald-950/40 px-3.5 py-1.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                {{ $post->category->name }}
            </a>
        @endif

        <h1 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white leading-tight">
            {{ $post->title }}
        </h1>

        <div class="flex items-center justify-center gap-3 sm:gap-4 text-xs text-gray-400 font-medium flex-wrap">
            <div class="flex items-center gap-1.5">
                <div class="w-6 h-6 bg-[#ECFDF5] dark:bg-emerald-950 text-[#007A5E] dark:text-emerald-400 rounded-full flex items-center justify-center font-bold text-[10px]">
                    {{ mb_substr($post->author->name ?? 'م', 0, 1) }}
                </div>
                <span class="text-gray-600 dark:text-gray-300 font-bold">{{ $post->author->name ?? 'مستر ویلا لفور' }}</span>
            </div>
            <span>•</span>
            <span>{{ $post->published_at?->format('Y/m/d') ?? $post->created_at->format('Y/m/d') }}</span>
            <span>•</span>
            <span>{{ $post->reading_time ?? 5 }} دقیقه مطالعه</span>
            <span>•</span>
            <span>{{ $post->view_count }} بازدید</span>
        </div>
    </div>

    {{-- Cover image --}}
    @if($post->cover_image)
        <div class="rounded-3xl overflow-hidden aspect-[16/9] border border-gray-100 dark:border-zinc-800 shadow-md">
            <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        </div>
    @endif

    {{-- Content Body --}}
    <div class="prose prose-emerald dark:prose-invert max-w-none text-right leading-relaxed text-gray-800 dark:text-gray-200 font-sans">
        {!! $post->body_html !!}
    </div>

    {{-- Gallery --}}
    @if(!empty($post->gallery))
        <div class="border-t border-gray-100 dark:border-zinc-800 pt-8 space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-white text-sm">تصاویر اقامتگاه و منطقه</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($post->gallery as $img)
                    <a href="{{ asset('storage/' . $img) }}" target="_blank" class="rounded-2xl overflow-hidden aspect-video border border-gray-100 dark:border-zinc-800 block hover:opacity-90 transition-opacity">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tags --}}
    @if($post->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2 pt-6 border-t border-gray-100 dark:border-zinc-800">
            @foreach($post->tags as $tag)
                <a href="{{ $tag->url }}" class="text-xs text-gray-500 bg-gray-50 dark:bg-zinc-900 dark:text-gray-400 hover:text-[#007A5E] dark:hover:text-emerald-400 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-zinc-800 transition-colors">
                    #{{ $tag->name }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Comments Section --}}
    @if($post->allow_comments && \Modules\ContentForge\Entities\ContentSetting::getValue('general.enable_comments', 'true') === 'true')
        <div class="border-t border-gray-100 dark:border-zinc-800 pt-8">
            @livewire('contentforge::web.comment-form', ['post' => $post])
            @livewire('contentforge::web.comment-list', ['post' => $post])
        </div>
    @endif
</article>
@endsection
