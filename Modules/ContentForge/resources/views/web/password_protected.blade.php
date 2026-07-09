@extends('layouts.web')

@section('title', 'محتوا محافظت شده است')

@section('content')
<div class="max-w-md mx-auto px-6 w-full pt-44 pb-32 flex flex-col justify-center min-h-[70vh]">
    <div class="p-8 bg-white dark:bg-gray-800 border dark:border-gray-700/50 rounded-3xl shadow-xl shadow-gray-100/50 dark:shadow-none space-y-6">
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-amber-50 dark:bg-amber-950/30 text-amber-500 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="text-xl font-black text-gray-900 dark:text-white">این صفحه رمزگذاری شده است</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">برای مشاهده محتویات این صفحه، رمز عبور مربوطه را وارد کنید.</p>
        </div>

        <form method="POST" action="{{ url()->current() }}" class="space-y-4">
            @csrf
            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400">رمز عبور صفحه</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border dark:border-gray-700 rounded-xl text-center font-mono focus:outline-none focus:border-indigo-500">
                @error('password')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/10 transition-all">
                تایید و بازگشایی صفحه
            </button>
        </form>
    </div>
</div>
@endsection
