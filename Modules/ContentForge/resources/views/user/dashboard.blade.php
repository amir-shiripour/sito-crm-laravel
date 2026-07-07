@extends('layouts.user')

@section('title', 'داشبورد مدیریت محتوا')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">داشبورد مدیریت محتوا</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت وبلاگ، برگه‌ها، سئو و تنظیمات سایت</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.content.posts.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/10 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                نوشته جدید
            </a>
            <a href="{{ route('user.content.pages.create') }}" class="px-5 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all flex items-center gap-2">
                برگه جدید
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="p-6 bg-gradient-to-br from-indigo-500/10 to-indigo-600/5 dark:from-indigo-500/20 dark:to-indigo-600/10 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">کل مقالات وبلاگ</span>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ \Modules\ContentForge\App\Models\ContentPost::posts()->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 4a2 2 0 00-2-2m-2 2h2m-2 4h2m-6 4h4"/></svg>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 dark:from-emerald-500/20 dark:to-emerald-600/10 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">برگه‌های ایستا</span>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ \Modules\ContentForge\App\Models\ContentPost::pages()->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-br from-amber-500/10 to-amber-600/5 dark:from-amber-500/20 dark:to-amber-600/10 border border-amber-100 dark:border-amber-900/50 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs text-amber-600 dark:text-amber-400 font-bold">دیدگاه‌های کاربران</span>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ \Modules\ContentForge\App\Models\ContentComment::count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-br from-purple-500/10 to-purple-600/5 dark:from-purple-500/20 dark:to-purple-600/10 border border-purple-100 dark:border-purple-900/50 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs text-purple-600 dark:text-purple-400 font-bold">کل بازدیدها</span>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ \Modules\ContentForge\App\Models\ContentPost::sum('view_count') }}</h3>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
        </div>
    </div>

    {{-- Main Layout grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Recent posts list --}}
        <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">آخرین نوشته‌های منتشر شده</h2>
                <a href="{{ route('user.content.posts.index') }}" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline">مشاهده همه</a>
            </div>
            <div class="space-y-4">
                @forelse(\Modules\ContentForge\App\Models\ContentPost::posts()->latest()->take(5)->get() as $post)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            @if($post->cover_image)
                                <img src="{{ asset('storage/' . $post->cover_image) }}" class="w-12 h-12 object-cover rounded-lg">
                            @else
                                <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white line-clamp-1">{{ $post->title }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-400 mt-1">
                                    <span>{{ $post->published_at?->format('Y-m-d') ?? $post->created_at->format('Y-m-d') }}</span>
                                    <span>•</span>
                                    <span>{{ $post->view_count }} بازدید</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('user.content.posts.edit', $post) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition-colors">ویرایش</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">هنوز هیچ مطلبی منتشر نشده است.</p>
                @endforelse
            </div>
        </div>

        {{-- Quick options / Settings side panel --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">دسترسی سریع</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('user.content.categories.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 transition-all flex flex-col items-center justify-center text-center gap-2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span class="text-sm font-bold">دسته‌بندی‌ها</span>
                </a>
                <a href="{{ route('user.content.tags.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 transition-all flex flex-col items-center justify-center text-center gap-2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-sm font-bold">برچسب‌ها</span>
                </a>
                <a href="{{ route('user.content.comments.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 transition-all flex flex-col items-center justify-center text-center gap-2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                    <span class="text-sm font-bold">نظرات</span>
                </a>
                <a href="{{ route('user.content.settings') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 transition-all flex flex-col items-center justify-center text-center gap-2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-sm font-bold">تنظیمات</span>
                </a>
            </div>

            <div class="border-t pt-5">
                <span class="text-xs text-gray-400 block mb-2">اطلاعات سرور هوش مصنوعی</span>
                @if(class_exists(\App\Services\GapGPTService::class) && !empty(\Modules\Settings\Entities\Setting::where('key', 'gapgpt_api_key')->value('value')))
                    <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 text-xs font-bold bg-emerald-50 dark:bg-emerald-950/30 p-2.5 rounded-xl">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        سرویس GapGPT هسته فعال و متصل است
                    </div>
                @else
                    <div class="flex items-center gap-2 text-gray-400 text-xs bg-gray-50 dark:bg-gray-900 p-2.5 rounded-xl">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        سرویس GapGPT غیرفعال است
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
