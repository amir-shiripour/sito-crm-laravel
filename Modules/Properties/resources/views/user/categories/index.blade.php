@extends('layouts.user')

@php
    $title = 'مدیریت دسته‌بندی‌ها';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                    </span>
                    مدیریت دسته‌بندی‌های شخصی
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">دسته‌بندی‌های خود را برای سازماندهی بهتر املاک ایجاد و مدیریت کنید.</p>
            </div>

            <button onclick="openCreateModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                افزودن دسته‌بندی
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            @if($categories->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">نام دسته‌بندی</th>
                                <th class="px-6 py-4 font-bold">رنگ</th>
                                <th class="px-6 py-4 font-bold">تعداد املاک</th>
                                <th class="px-6 py-4 font-bold text-left">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($categories as $category)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $category->name }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600" style="background-color: {{ $category->color }}"></span>
                                            <span class="text-xs text-gray-500 dir-ltr">{{ $category->color }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $category->properties()->count() }} ملک</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onclick="openEditModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->color }}')" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg dark:text-indigo-400 dark:hover:bg-indigo-900/30 transition-colors" title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <form action="{{ route('user.properties.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('آیا از حذف این دسته‌بندی اطمینان دارید؟ املاک متصل به این دسته‌بندی بدون دسته خواهند شد.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="حذف">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $categories->links() }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">هنوز دسته‌بندی ایجاد نکرده‌اید</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">با ایجاد دسته‌بندی، املاک خود را بهتر مدیریت کنید.</p>
                    <button onclick="openCreateModal()" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                        ایجاد اولین دسته‌بندی
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal --}}
    <div id="categoryModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700 z-50">
                <form id="categoryForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right w-full">
                                <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white mb-4" id="modal-title">
                                    افزودن / ویرایش دسته‌بندی
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">نام دسته‌بندی</label>
                                        <input type="text" name="name" id="categoryName" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">رنگ</label>
                                        <div class="flex items-center gap-4">
                                            <input type="color" name="color" id="categoryColor" class="h-10 w-20 rounded cursor-pointer" value="#6366f1">
                                            <span class="text-xs text-gray-500">برای تمایز بهتر، یک رنگ انتخاب کنید.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            ذخیره
                        </button>
                        <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('categoryForm').action = "{{ route('user.properties.categories.store') }}";
            document.getElementById('formMethod').value = "POST";
            document.getElementById('categoryName').value = "";
            document.getElementById('categoryColor').value = "#6366f1";
            document.getElementById('modal-title').innerText = "افزودن دسته‌بندی جدید";
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function openEditModal(id, name, color) {
            let url = "{{ route('user.properties.categories.update', ':id') }}";
            url = url.replace(':id', id);

            document.getElementById('categoryForm').action = url;
            document.getElementById('formMethod').value = "PUT";
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryColor').value = color;
            document.getElementById('modal-title').innerText = "ویرایش دسته‌بندی";
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }
    </script>
@endsection
