@extends('layouts.web')

@section('title', 'تکمیل اطلاعات و پرداخت')

@section('content')
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 w-full py-8">
        {{-- Breadcrumb --}}
        <div class="mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <nav class="flex text-sm text-gray-500 dark:text-gray-400 mb-4">
                <ol class="flex items-center space-x-2 space-x-reverse">
                    <li><a href="{{ url('/') }}" class="hover:text-gray-900 dark:hover:text-white transition-colors">خانه</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('market.public.index') }}" class="hover:text-indigo-600 transition-colors">فروشگاه</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('market.cart.index') }}" class="hover:text-indigo-600 transition-colors">سبد خرید</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="font-bold text-gray-900 dark:text-gray-100">پرداخت</li>
                </ol>
            </nav>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                تکمیل اطلاعات و پرداخت
            </h1>
        </div>

        @livewire('market::web.checkout-page')

    </div>
@endsection
