@extends('layouts.user')

@php
    $title = 'قیمت‌گذاری ملک';
    $currencyLabel = $currency == 'toman' ? 'تومان' : 'ریال';
@endphp

@section('content')
<div class="max-w-3xl mx-auto" x-data="priceFormatter()">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">قیمت‌گذاری ملک</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">مرحله ۲: تعیین قیمت برای "{{ $property->title }}" ({{ $property->listing_type == 'sale' ? 'فروش' : ($property->listing_type == 'rent' ? 'رهن و اجاره' : 'پیش‌فروش') }})</p>

        <form action="{{ route('user.properties.pricing.update', $property) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6">

                @if($property->listing_type == 'sale')
                    {{-- Sale Fields --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت اعلامی ({{ $currencyLabel }})</label>
                        <input type="text" name="price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('price', number_format($property->price)) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت کف ({{ $currencyLabel }})</label>
                        <input type="text" name="min_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('min_price', number_format($property->min_price)) }}">
                        <p class="text-xs text-gray-500 mt-1">کمترین قیمتی که مالک حاضر به فروش است (محرمانه).</p>
                    </div>

                @elseif($property->listing_type == 'rent')
                    {{-- Rent Fields --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مبلغ رهن ({{ $currencyLabel }})</label>
                        <input type="text" name="deposit_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('deposit_price', number_format($property->deposit_price)) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اجاره ماهیانه ({{ $currencyLabel }})</label>
                        <input type="text" name="rent_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('rent_price', number_format($property->rent_price)) }}">
                    </div>

                @elseif($property->listing_type == 'presale')
                    {{-- Presale Fields --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مبلغ پیش‌پرداخت ({{ $currencyLabel }})</label>
                        <input type="text" name="advance_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('advance_price', number_format($property->advance_price)) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت کل اعلامی ({{ $currencyLabel }})</label>
                        <input type="text" name="price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" required value="{{ old('price', number_format($property->price)) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت کف ({{ $currencyLabel }})</label>
                        <input type="text" name="min_price" @input="formatPrice" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('min_price', number_format($property->min_price)) }}">
                    </div>
                @endif

                {{-- Convertible Option --}}
                <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="is_convertible" value="1" x-model="isConvertible" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">قابل تبدیل است</span>
                    </label>

                    <div x-show="isConvertible" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شرایط تبدیل</label>
                        <input type="text" name="convertible_with" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="مثلا: قابل تبدیل به رهن کامل یا معاوضه با خودرو" value="{{ old('convertible_with', $property->convertible_with) }}">
                    </div>
                </div>

            </div>

            <div class="flex justify-between items-center mt-8">
                <a href="{{ route('user.properties.edit', $property) }}" class="text-sm text-gray-500 hover:text-gray-700">بازگشت به مرحله قبل</a>
                <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                    ذخیره نهایی
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function priceFormatter() {
        return {
            isConvertible: {{ old('is_convertible', $property->is_convertible) ? 'true' : 'false' }},
            formatPrice(event) {
                let value = event.target.value.replace(/,/g, '');
                if (!isNaN(value) && value !== '') {
                    event.target.value = parseInt(value).toLocaleString('en-US');
                }
            }
        }
    }
</script>
@endsection
