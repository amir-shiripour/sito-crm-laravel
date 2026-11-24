@extends('layouts.user')

@section('title', 'ویرایش انبار «' . $warehouse->name . '»')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Panel Standard Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-indigo-600 text-white shadow-sm">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                </svg>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                    ویرایش انبار «{{ $warehouse->name }}»
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    مشخصات، مکان فیزیکی و مسئول انبار
                </p>
            </div>
        </div>

        <div>
            <a href="{{ $warehouse->type === 'inventory' ? route('admin.warehouse.inventory.index') : route('admin.warehouse.assets.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    <form action="{{ route('admin.warehouse.warehouses.update', $warehouse) }}" method="POST">
        @method('PUT')
        @include('warehouse::warehouses._form')
    </form>

</div>
@endsection
