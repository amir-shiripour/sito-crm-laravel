@extends('layouts.user')
@php($title = 'نمای ۳۶۰ درجه پرونده فروش')
@section('content')
    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.sales.pipeline') }}" class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">پرونده فروش</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مشاهده نمای ۳۶۰ درجه و تایملاین تعاملات پرونده</p>
                </div>
            </div>
        </div>

        {{-- Livewire Deal 360-View Component --}}
        @livewire('sales::deal-360-view', ['dealId' => $dealId])
    </div>
@endsection
