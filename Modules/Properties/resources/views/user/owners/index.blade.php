@extends('layouts.user')

@php
    $title = 'مدیریت مالکین';

    // استایل‌های مشترک
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";

    $canCreate = auth()->user()->can('properties.owners.create') || auth()->user()->can('properties.owners.manage');
    $canEdit = auth()->user()->can('properties.owners.edit') || auth()->user()->can('properties.owners.manage');
    $canDelete = auth()->user()->can('properties.owners.delete') || auth()->user()->can('properties.owners.manage');
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8" x-data="ownersManager()">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </span>
                    مدیریت مالکین
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">مدیریت لیست مالکین و اطلاعات تماس آن‌ها</p>
            </div>

            @if($canCreate)
                <button @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    افزودن مالک جدید
                </button>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">مالک</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">شماره تماس</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-center">تعداد املاک</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-center">ایجاد کننده</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-center">تاریخ ثبت</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-center">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <template x-for="owner in ownersList" :key="owner.id">
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900 dark:text-white" x-text="owner.first_name + ' ' + owner.last_name"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-600 dark:text-gray-400 dir-ltr" x-text="owner.phone"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800" x-text="owner.properties_count + ' ملک'"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-600 dark:text-gray-400 text-xs" x-text="owner.creator ? owner.creator.name : '-'"></span>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs" x-text="new Date(owner.created_at).toLocaleDateString('fa-IR')"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    @if($canEdit)
                                        <button @click="openEditModal(owner)" class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 transition-colors" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                    @endif

                                    @if($canDelete)
                                        <form :action="`{{ url('user/property-owners') }}/${owner.id}`" method="POST" onsubmit="return confirm('آیا از حذف این مالک اطمینان دارید؟');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors" title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="ownersList.length === 0">
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <p class="text-base font-medium">هیچ مالکی یافت نشد.</p>
                                    @if($canCreate)
                                        <p class="text-sm mt-1">با استفاده از دکمه بالا، مالک جدید اضافه کنید.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                {{ $owners->links() }}
            </div>
        </div>

        {{-- Create Modal --}}
        @if($canCreate)
            <div x-show="showCreateModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                    <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="showCreateModal = false">
                        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100 dark:border-gray-700">

                        <div class="bg-gray-50/50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                افزودن مالک جدید
                            </h3>
                            <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-5">
                            <template x-if="errors.general">
                                <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                                    <span x-text="errors.general[0]"></span>
                                </div>
                            </template>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="{{ $labelClass }}">نام</label>
                                    <input type="text" x-model="formData.first_name" class="{{ $inputClass }}">
                                    <template x-if="errors.first_name">
                                        <p class="text-red-500 text-xs mt-1" x-text="errors.first_name[0]"></p>
                                    </template>
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">نام خانوادگی</label>
                                    <input type="text" x-model="formData.last_name" class="{{ $inputClass }}">
                                    <template x-if="errors.last_name">
                                        <p class="text-red-500 text-xs mt-1" x-text="errors.last_name[0]"></p>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">شماره تماس</label>
                                <input type="text" x-model="formData.phone" class="{{ $inputClass }} dir-ltr text-left" placeholder="0912...">
                                <template x-if="errors.phone">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.phone[0]"></p>
                                </template>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/30 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="submitCreate" :disabled="isSaving"
                                    class="inline-flex w-full justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed">
                                <span x-show="!isSaving">ذخیره اطلاعات</span>
                                <span x-show="isSaving" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                در حال ذخیره...
                            </span>
                            </button>
                            <button type="button" @click="showCreateModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                انصراف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Edit Modal --}}
        @if($canEdit)
            <div x-show="showEditModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="showEditModal = false">
                        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100 dark:border-gray-700">

                        <div class="bg-gray-50/50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                ویرایش اطلاعات مالک
                            </h3>
                            <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-5">
                            <template x-if="errors.general">
                                <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                                    <span x-text="errors.general[0]"></span>
                                </div>
                            </template>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="{{ $labelClass }}">نام</label>
                                    <input type="text" x-model="formData.first_name" class="{{ $inputClass }}">
                                    <template x-if="errors.first_name">
                                        <p class="text-red-500 text-xs mt-1" x-text="errors.first_name[0]"></p>
                                    </template>
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">نام خانوادگی</label>
                                    <input type="text" x-model="formData.last_name" class="{{ $inputClass }}">
                                    <template x-if="errors.last_name">
                                        <p class="text-red-500 text-xs mt-1" x-text="errors.last_name[0]"></p>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">شماره تماس</label>
                                <input type="text" x-model="formData.phone" class="{{ $inputClass }} dir-ltr text-left">
                                <template x-if="errors.phone">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.phone[0]"></p>
                                </template>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/30 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="submitEdit" :disabled="isSaving"
                                    class="inline-flex w-full justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed">
                                <span x-show="!isSaving">ذخیره تغییرات</span>
                                <span x-show="isSaving" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                در حال ذخیره...
                            </span>
                            </button>
                            <button type="button" @click="showEditModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                انصراف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function ownersManager() {
            return {
                ownersList: @json($owners->items()),
                showCreateModal: false,
                showEditModal: false,
                formData: {
                    id: null,
                    first_name: '',
                    last_name: '',
                    phone: ''
                },
                errors: {},
                isSaving: false,

                openCreateModal() {
                    this.formData = { id: null, first_name: '', last_name: '', phone: '' };
                    this.errors = {};
                    this.showCreateModal = true;
                },

                openEditModal(owner) {
                    this.formData = { ...owner };
                    this.errors = {};
                    this.showEditModal = true;
                },

                async submitCreate() {
                    this.errors = {};
                    this.isSaving = true;

                    try {
                        const response = await fetch('{{ route("user.property-owners.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.ownersList.unshift(data.owner);
                            this.showCreateModal = false;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: data.message } }));
                        } else if (response.status === 422) {
                            this.errors = data.errors;
                        } else {
                            this.errors.general = [data.message || 'خطای ناشناخته'];
                        }
                    } catch (error) {
                        console.error(error);
                        this.errors.general = ['خطا در برقراری ارتباط'];
                    } finally {
                        this.isSaving = false;
                    }
                },

                async submitEdit() {
                    this.errors = {};
                    this.isSaving = true;

                    try {
                        const response = await fetch(`{{ url('user/property-owners') }}/${this.formData.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            const index = this.ownersList.findIndex(o => o.id === this.formData.id);
                            if (index !== -1) {
                                this.ownersList[index] = data.owner;
                            }
                            this.showEditModal = false;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', text: data.message } }));
                        } else if (response.status === 422) {
                            this.errors = data.errors;
                        } else {
                            this.errors.general = [data.message || 'خطای ناشناخته'];
                        }
                    } catch (error) {
                        console.error(error);
                        this.errors.general = ['خطا در برقراری ارتباط'];
                    } finally {
                        this.isSaving = false;
                    }
                }
            }
        }
    </script>
@endsection
