@extends('layouts.web')

@section('title', 'آرشیو مقالات - ' . $entity->name)

@section('content')
<div class="max-w-7xl mx-auto px-6 w-full flex-grow flex flex-col pt-32 pb-16">
    <div class="text-center max-w-2xl mx-auto space-y-4 mb-16">
        <h1 class="text-4xl font-black text-gray-900 dark:text-white leading-tight">
            مجموعه مقالات و نوشته‌های <span class="text-indigo-600 dark:text-indigo-400">{{ $entity->name }}</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">آخرین نوشته‌ها، آموزش‌ها و اخبار رسمی ما را دنبال کنید</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @forelse($posts as $post)
            <div class="bg-white dark:bg-gray-800 border dark:border-gray-700/50 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col group">
                <div class="relative overflow-hidden aspect-[16/10] bg-gray-100 dark:bg-gray-700">
                    @if($post->cover_image)
                        <img src="{{ asset('storage/' . $post->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
                            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    @if($post->category)
                        <span class="absolute top-4 right-4 text-xs font-bold bg-white/90 dark:bg-gray-900/90 text-gray-900 dark:text-white px-3 py-1.5 rounded-full shadow-sm backdrop-blur-sm">
                            {{ $post->category->name }}
                        </span>
                    @endif
                </div>

                <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span>{{ $post->published_at?->format('Y-m-d') ?? $post->created_at->format('Y-m-d') }}</span>
                            <span>•</span>
                            <span>{{ $post->reading_time ?? 5 }} دقیقه مطالعه</span>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">
                            <a href="{{ $post->url }}">{{ $post->title }}</a>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 leading-relaxed">
                            {{ $post->excerpt }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-gray-700/50">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <div class="w-6 h-6 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center font-bold text-[10px]">
                                {{ mb_substr($post->author->name ?? 'م', 0, 1) }}
                            </div>
                            <span>{{ $post->author->name ?? 'مدیر سیستم' }}</span>
                        </div>
                        <a href="{{ $post->url }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 group">
                            <span>ادامه مطلب</span>
                            <svg class="w-3 h-3 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center text-gray-400">
                هنوز هیچ مطلبی برای این موجودیت منتشر نشده است.
            </div>
        @endforelse
    </div>

    @if($posts->hasPages())
        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
