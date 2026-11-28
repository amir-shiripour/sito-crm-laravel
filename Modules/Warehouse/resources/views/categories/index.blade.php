@extends('layouts.user')

@section('title', 'مدیریت دسته‌بندی و زیردسته‌های کالا')

@section('content')
<div x-data="{
    showModal: false,
    editMode: false,
    modalTitle: 'افزودن دسته جدید',
    formAction: '{{ route('admin.warehouse.categories.store') }}',
    formMethod: 'POST',
    categoryId: null,
    categoryName: '',
    categoryCode: '',
    parentId: '',
    categoryOrder: 0,
    categoryDesc: '',

    openCreate(parent = '') {
        this.editMode = false;
        this.modalTitle = parent ? 'افزودن زیردسته جدید' : 'افزودن دسته اصلی جدید';
        this.formAction = '{{ route('admin.warehouse.categories.store') }}';
        this.formMethod = 'POST';
        this.categoryId = null;
        this.categoryName = '';
        this.categoryCode = '';
        this.parentId = parent;
        this.categoryOrder = 0;
        this.categoryDesc = '';
        this.showModal = true;
    },

    openEdit(cat) {
        this.editMode = true;
        this.modalTitle = 'ویرایش دسته «' + cat.name + '»';
        this.formAction = '{{ url('admin/warehouse/categories') }}/' + cat.id;
        this.formMethod = 'POST';
        this.categoryId = cat.id;
        this.categoryName = cat.name;
        this.categoryCode = cat.code || '';
        this.parentId = cat.parent_id || '';
        this.categoryOrder = cat.order || 0;
        this.categoryDesc = cat.description || '';
        this.showModal = true;
    }
}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Panel Standard Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-indigo-600 text-white shadow-sm">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                    دسته‌بندی و طبقه‌بندی اقلام انبار
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    مدیریت ساختار درختی دسته‌ها و زیردسته‌های کالاها و تجهیزات
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button"
                    @click="openCreate()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                افزودن دسته اصلی جدید
            </button>

            <a href="{{ route('admin.warehouse.items.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست کالاها
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Category Cards Tree List --}}
    @if($categories->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-12 text-center text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <p class="text-sm font-bold">هنوز هیچ دسته‌ای ثبت نشده است.</p>
            <p class="text-xs mt-1">با کلیک روی «افزودن دسته اصلی جدید» یا درون‌ریزی اکسل کلینیک، دسته‌ها اضافه می‌شوند.</p>
        </div>
    @else
        <div class="space-y-4 font-sans">
            @foreach($categories as $cat)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 overflow-hidden shadow-xs">
                    {{-- Master Category Row --}}
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700/60">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-bold shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $cat->name }}
                                    </h3>
                                    @if($cat->code)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 dir-ltr inline-block">({{ $cat->code }})</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">
                                    {{ $cat->children->count() }} زیردسته | {{ $cat->items_count }} کالای مستقیم · ثبت‌کننده: <strong class="text-gray-800 dark:text-gray-200">{{ $cat->creator_name }}</strong>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <button type="button"
                                    @click="openCreate('{{ $cat->id }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/50 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 text-xs font-bold transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                افزودن زیردسته
                            </button>

                            <button type="button"
                                    @click="openEdit({{ Js::from($cat) }})"
                                    class="p-2 rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-gray-700 transition-colors"
                                    title="ویرایش دسته">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                                </svg>
                            </button>

                            @if($cat->children->isEmpty() && $cat->items_count == 0)
                                <form action="{{ route('admin.warehouse.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('آیا از حذف این دسته مطمئن هستید؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-2 rounded-xl text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:text-gray-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/40 transition-colors"
                                            title="حذف دسته">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Subcategories List --}}
                    @if($cat->children->isNotEmpty())
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 bg-white dark:bg-gray-800">
                            @foreach($cat->children as $sub)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                    <div class="truncate pl-2">
                                        <div class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">
                                            {{ $sub->name }}
                                        </div>
                                        <div class="text-[10px] text-slate-600 dark:text-slate-300 mt-0.5 truncate">
                                            {{ $sub->sub_items_count ?? 0 }} کالا · ثبت: {{ $sub->creator_name }}
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button"
                                                @click="openEdit({{ Js::from($sub) }})"
                                                class="p-1 rounded-lg text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                                title="ویرایش زیردسته">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                                            </svg>
                                        </button>

                                        @if(($sub->sub_items_count ?? 0) == 0)
                                            <form action="{{ route('admin.warehouse.categories.destroy', $sub) }}" method="POST" onsubmit="return confirm('آیا از حذف این زیردسته مطمئن هستید؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1 rounded-lg text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                                                        title="حذف زیردسته">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Create / Edit Category Modal (Harmonized Dark/Light Modal) --}}
    <div x-show="showModal"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-cloak>
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = false"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="showModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="inline-block w-full max-w-lg p-6 my-8 text-right align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-200 dark:border-gray-700 relative z-10">

                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="modalTitle"></h3>
                    <button type="button" @click="showModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Name --}}
                    <div>
                        <label for="modal_cat_name" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                            نام دسته / زیردسته <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="modal_cat_name"
                               x-model="categoryName"
                               required
                               placeholder="مثال: جراحی فک و صورت یا فیکسچر"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                    </div>

                    {{-- Parent Category --}}
                    <div>
                        <label for="modal_parent_id" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                            دسته والد
                        </label>
                        <select name="parent_id"
                                id="modal_parent_id"
                                x-model="parentId"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                            <option value="">بدون والد (دسته اصلی)</option>
                            @foreach($categories as $rootCat)
                                <option value="{{ $rootCat->id }}" x-bind:disabled="editMode && categoryId == {{ $rootCat->id }}">
                                    {{ $rootCat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Code & Order --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="modal_code" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                                کد اختصاری
                            </label>
                            <input type="text"
                                   name="code"
                                   id="modal_code"
                                   x-model="categoryCode"
                                   placeholder="مثال: SURG"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
                        </div>

                        <div>
                            <label for="modal_order" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                                ترتیب نمایش
                            </label>
                            <input type="number"
                                   name="order"
                                   id="modal_order"
                                   x-model="categoryOrder"
                                   placeholder="0"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans dir-ltr text-left">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="modal_desc" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">
                            توضیحات
                        </label>
                        <textarea name="description"
                                  id="modal_desc"
                                  x-model="categoryDesc"
                                  rows="2"
                                  placeholder="توضیحات کوتاه..."
                                  class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                        <button type="button"
                                @click="showModal = false"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold transition-all">
                            انصراف
                        </button>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                            <span x-text="editMode ? 'ذخیره تغییرات' : 'ثبت دسته'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
