@extends('layouts.guest')

@section('content')
    <header class="w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/50 dark:bg-gray-950/50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="font-bold text-xl tracking-tight">سیستم CRM</a>
            </div>
            <nav class="flex items-center gap-4">
                <a href="{{ route('properties.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">بازگشت به لیست</a>
            </nav>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-12">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="p-8">
                <div class="flex justify-between items-start mb-6">
                    <h1 class="text-3xl font-bold">{{ $property->title }}</h1>
                    @if($property->status)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">{{ $property->status->label }}</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 mb-1">قیمت</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ number_format($property->price) }} تومان</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 mb-1">آدرس</p>
                        <p class="text-lg">{{ $property->address }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <div class="text-center">
                        <p class="text-gray-500 text-sm">متراژ</p>
                        <p class="font-bold">{{ $property->area }} متر</p>
                    </div>
                    <div class="text-center border-x border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 text-sm">اتاق خواب</p>
                        <p class="font-bold">{{ $property->bedrooms }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-500 text-sm">سرویس بهداشتی</p>
                        <p class="font-bold">{{ $property->bathrooms }}</p>
                    </div>
                </div>

                <div class="prose dark:prose-invert max-w-none">
                    <h3 class="text-xl font-bold mb-4">توضیحات</h3>
                    <p class="whitespace-pre-line">{{ $property->description }}</p>
                </div>
            </div>
        </div>
    </main>
@endsection
