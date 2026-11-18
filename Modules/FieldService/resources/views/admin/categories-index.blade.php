@extends('layouts.user')

@section('title', 'مدیریت بخش‌ها، دسته‌ها و خدمات فنی')

@section('content')
@php
    $activeTab = request()->query('tab', 'services');
    if (! in_array($activeTab, ['sections', 'groups', 'services'], true)) {
        $activeTab = 'services';
    }
    $tabs = [
        'sections' => ['label' => 'بخش‌ها / تایپ‌ها', 'icon' => '🧩'],
        'groups'   => ['label' => 'دسته‌های خدمات', 'icon' => '📂'],
        'services' => ['label' => 'خدمات فنی', 'icon' => '🛠️'],
    ];
@endphp

<div class="space-y-5 font-sans">
    {{-- 3-Level Catalog Navigation Tabs --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-2 shadow-sm flex flex-wrap items-center gap-2">
        @foreach($tabs as $key => $tab)
            @php $isActive = $activeTab === $key; @endphp
            <a href="{{ route('user.fieldservice.categories.index', ['tab' => $key]) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $isActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25 dark:bg-indigo-500/20 dark:text-indigo-300 dark:border dark:border-indigo-500/40' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50' }}">
                <span>{{ $tab['icon'] }}</span>
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Active Tab Content --}}
    @if($activeTab === 'sections')
        @livewire('fieldservice::sections-manager')
    @elseif($activeTab === 'groups')
        @livewire('fieldservice::service-groups-manager')
    @else
        @livewire('fieldservice::categories-manager')
    @endif
</div>
@endsection
