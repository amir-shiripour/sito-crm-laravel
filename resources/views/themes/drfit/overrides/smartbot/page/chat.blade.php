@extends('layouts.web')

@section('title', 'دستیار هوشمند دکتر فیت')

@push('styles')
    <style>
        main { padding-bottom: 0 !important; }
        :root {
            --bot-primary: #3F7D20 !important;
            --bot-primary-rgb: 63, 125, 32 !important;
        }
    </style>
@endpush

@section('content')
    <div dir="rtl" class="relative flex flex-col flex-grow min-h-[calc(100vh-5.5rem)] bg-gradient-to-br from-[#f8faf9] via-[#f0f5f1] to-[#e8f0eb] dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 overflow-hidden">

        {{-- المان‌های نوری پس‌زمینه (Glassmorphism Glows) مطابق با برند دکتر فیت --}}
        <div class="absolute top-[10%] right-[10%] w-[60vw] max-w-[400px] h-[60vw] max-h-[400px] bg-[#3F7D20]/15 dark:bg-[#3F7D20]/20 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="absolute bottom-[10%] left-[10%] w-[50vw] max-w-[300px] h-[50vw] max-h-[300px] bg-[#c3f25c]/25 dark:bg-[#3F7D20]/15 rounded-full blur-[70px] pointer-events-none"></div>

        {{-- گرید نقطه‌ای ملایم پس‌زمینه --}}
        <div class="absolute inset-0 bg-[radial-gradient(#3F7D20_1.2px,transparent_1.2px)] dark:bg-[radial-gradient(#5cba2f_1.2px,transparent_1.2px)] [background-size:24px_24px] opacity-10 dark:opacity-15 pointer-events-none"></div>

        {{-- ویجت اصلی چت هوشمند به صورت تمام‌عرض (بدون باکس محدودکننده) --}}
        <div class="relative z-10 flex-grow flex flex-col w-full">
            <livewire:smartbot.widget.chat-widget :isStandalone="true" />
        </div>
    </div>

    @php
        $isMarketModuleActive = false;
        try {
            if (class_exists(\App\Models\Module::class)) {
                $isMarketModuleActive = \App\Models\Module::where('slug', 'market')
                    ->where('installed', true)
                    ->where('active', true)
                    ->exists();
            }
        } catch (\Throwable $e) {}
    @endphp

    @if($isMarketModuleActive)
        @livewire('market::web.cart-manager')
    @endif
@endsection
