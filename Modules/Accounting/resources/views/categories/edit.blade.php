@extends('layouts.user')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        ویرایش دسته بندی: {{ $category->title }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('admin.accounting.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 sm:px-20 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Title -->
                            <div class="md:col-span-2">
                                <label for="title" class="block font-medium text-sm text-gray-700">عنوان دسته بندی <span class="text-red-500">*</span></label>
                                <input type="text" name="title" id="title" value="{{ old('title', $category->title) }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block font-medium text-sm text-gray-700">نوع دسته بندی <span class="text-red-500">*</span></label>
                                <select name="type" id="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="income" {{ old('type', $category->type) == 'income' ? 'selected' : '' }}>درآمد</option>
                                    <option value="expense" {{ old('type', $category->type) == 'expense' ? 'selected' : '' }}>هزینه</option>
                                </select>
                                @error('type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block font-medium text-sm text-gray-700">وضعیت</label>
                                <div class="mt-2" x-data="{ enabled: {{ old('status', $category->status) ? 'true' : 'false' }} }">
                                    <input type="hidden" name="status" :value="enabled ? 1 : 0">
                                    <button @click="enabled = !enabled" type="button" :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" role="switch" :aria-checked="enabled.toString()">
                                        <span aria-hidden="true" :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                    </button>
                                    <span x-text="enabled ? 'فعال' : 'غیر فعال'" class="ml-3 text-sm font-medium text-gray-900"></span>
                                </div>
                                @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-left sm:px-6">
                        <a href="{{ route('admin.accounting.categories.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ml-4">
                            انصراف
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                            به روز رسانی
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
