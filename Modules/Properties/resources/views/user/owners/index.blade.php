@extends('layouts.user')

@php
    $title = 'مدیریت مالکین';
@endphp

@section('content')
<div class="max-w-6xl mx-auto" x-data="ownersManager()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">مدیریت مالکین</h1>
        <button @click="openCreateModal" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            افزودن مالک جدید
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-right">نام و نام خانوادگی</th>
                        <th scope="col" class="px-6 py-3 text-right">شماره تماس</th>
                        <th scope="col" class="px-6 py-3 text-center">تعداد املاک</th>
                        <th scope="col" class="px-6 py-3 text-center">تاریخ ثبت</th>
                        <th scope="col" class="px-6 py-3 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="owner in ownersList" :key="owner.id">
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white text-right" x-text="owner.first_name + ' ' + owner.last_name"></td>
                            <td class="px-6 py-4 text-right" x-text="owner.phone"></td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300" x-text="owner.properties_count"></span>
                            </td>
                            <td class="px-6 py-4 text-center" x-text="new Date(owner.created_at).toLocaleDateString('fa-IR')"></td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <button @click="openEditModal(owner)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">ویرایش</button>
                                    <form :action="`{{ url('user/property-owners') }}/${owner.id}`" method="POST" onsubmit="return confirm('آیا از حذف این مالک اطمینان دارید؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="ownersList.length === 0">
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                هیچ مالکی یافت نشد.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $owners->links() }}
        </div>
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">افزودن مالک جدید</h3>
                    <div class="space-y-4">
                        <template x-if="errors.general">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline" x-text="errors.general[0]"></span>
                            </div>
                        </template>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام</label>
                                <input type="text" x-model="formData.first_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <template x-if="errors.first_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.first_name[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام خانوادگی</label>
                                <input type="text" x-model="formData.last_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <template x-if="errors.last_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.last_name[0]"></p>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شماره تماس</label>
                            <input type="text" x-model="formData.phone" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            <template x-if="errors.phone">
                                <p class="text-red-500 text-xs mt-1" x-text="errors.phone[0]"></p>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="submitCreate" :disabled="isSaving" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!isSaving">ذخیره</span>
                        <span x-show="isSaving">در حال ذخیره...</span>
                    </button>
                    <button type="button" @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">انصراف</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">ویرایش مالک</h3>
                    <div class="space-y-4">
                        <template x-if="errors.general">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline" x-text="errors.general[0]"></span>
                            </div>
                        </template>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام</label>
                                <input type="text" x-model="formData.first_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <template x-if="errors.first_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.first_name[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام خانوادگی</label>
                                <input type="text" x-model="formData.last_name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <template x-if="errors.last_name">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.last_name[0]"></p>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شماره تماس</label>
                            <input type="text" x-model="formData.phone" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            <template x-if="errors.phone">
                                <p class="text-red-500 text-xs mt-1" x-text="errors.phone[0]"></p>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="submitEdit" :disabled="isSaving" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!isSaving">ذخیره تغییرات</span>
                        <span x-show="isSaving">در حال ذخیره...</span>
                    </button>
                    <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">انصراف</button>
                </div>
            </div>
        </div>
    </div>
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
