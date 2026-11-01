@extends('layouts.web')

@section('title', 'دسته‌بندی: ' . $category->name . ' - ' . ($entity->name ?? 'مستر ویلا لفور'))

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-8 w-full flex-grow flex flex-col pt-10 pb-16">
    {{-- Hero/Title section --}}
    <div class="text-center max-w-2xl mx-auto space-y-3 mb-12">
        <span class="inline-block text-xs font-bold text-[#007A5E] dark:text-emerald-400 bg-[#ECFDF5] dark:bg-emerald-950/40 px-3 py-1.5 rounded-full border border-emerald-200 dark:border-emerald-800">دسته‌بندی مطالب</span>
        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white leading-tight">
            {{ $category->name }}
        </h1>
        @if($category->description)
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-lg mx-auto">{{ $category->description }}</p>
        @endif
    </div>

    {{-- Grid listing --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
        @forelse($posts as $post)
            <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col group">
                {{-- Cover Image --}}
                <div class="relative overflow-hidden aspect-[16/10] bg-gray-100 dark:bg-zinc-800">
                    @if($post->cover_image)
                        <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-zinc-600">
                            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>

                {{-- Post Details --}}
                <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span>{{ $post->published_at?->format('Y/m/d') ?? $post->created_at->format('Y/m/d') }}</span>
                            <span>•</span>
                            <span>{{ $post->reading_time ?? 5 }} دقیقه مطالعه</span>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors text-base">
                            <a href="{{ $post->url }}">{{ $post->title }}</a>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 leading-relaxed">
                            {{ $post->excerpt }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <div class="w-6 h-6 bg-[#ECFDF5] dark:bg-emerald-950 text-[#007A5E] dark:text-emerald-400 rounded-full flex items-center justify-center font-bold text-[10px]">
                                {{ mb_substr($post->author->name ?? 'م', 0, 1) }}
                            </div>
                            <span>{{ $post->author->name ?? 'مستر ویلا لفور' }}</span>
                        </div>
                        <a href="{{ $post->url }}" class="text-xs font-bold text-[#007A5E] dark:text-emerald-400 hover:underline flex items-center gap-1 group">
                            <span>ادامه مطلب</span>
                            <svg class="w-3 h-3 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-gray-400">
                مقاله‌ای در این دسته‌بندی یافت نشد.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(method_exists($posts, 'links'))
        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
