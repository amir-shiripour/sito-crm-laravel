@extends('layouts.user')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-2xl font-semibold leading-tight text-gray-800 mb-6">
                ایجاد دسته بندی جدید
            </h2>

            <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
                <form action="{{ route('admin.accounting.categories.store') }}" method="POST" class="p-6">
                    @csrf

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">عنوان دسته بندی <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('title') border-red-500 @enderror" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">نوع دسته بندی <span class="text-red-500">*</span></label>
                        <select name="type" id="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('type') border-red-500 @enderror" required>
                            <option value="">انتخاب کنید...</option>
                            <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>درآمد</option>
                            <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>هزینه</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="status" class="block font-medium text-sm text-gray-700">وضعیت</label>
                        <div class="mt-2" x-data="{ enabled: {{ old('status', true) ? 'true' : 'false' }} }">
                            <input type="hidden" name="status" :value="enabled ? 1 : 0">
                            <button @click="enabled = !enabled" type="button" :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" role="switch" :aria-checked="enabled.toString()">
                                <span aria-hidden="true" :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                            <span x-text="enabled ? 'فعال' : 'غیر فعال'" class="ml-3 text-sm font-medium text-gray-900"></span>
                        </div>
                        @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-end border-t border-gray-200 pt-4 mt-4">
                        <a href="{{ route('admin.accounting.categories.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ml-4">
                            انصراف
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            ذخیره دسته بندی
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
