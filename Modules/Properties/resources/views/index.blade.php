@extends('layouts.guest')

@section('content')
    <header class="w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/50 dark:bg-gray-950/50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="font-bold text-xl tracking-tight">سیستم CRM</a>
            </div>
            <nav class="flex items-center gap-4">
                @auth
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">پنل مدیریت</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">ورود</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <h1 class="text-3xl font-bold mb-8">لیست املاک</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($properties as $property)
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-800 hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <h2 class="text-xl font-bold mb-2">{{ $property->title }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 line-clamp-2">{{ $property->description }}</p>

                        <div class="flex justify-between items-center mb-4">
                            <span class="font-bold text-indigo-600">{{ number_format($property->price) }} تومان</span>
                            @if($property->status)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $property->status->label }}</span>
                            @endif
                        </div>

                        <a href="{{ route('properties.show', $property->id) }}" class="block w-full text-center py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors font-medium">مشاهده جزئیات</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $properties->links() }}
        </div>
    </main>
@endsection
