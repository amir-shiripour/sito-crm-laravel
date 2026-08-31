@extends('layouts.user')

@section('title', 'افزودن حساب خزانه‌داری')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";

    $catTypePriority = ['asset' => 1, 'liability' => 2, 'equity' => 3, 'income' => 4, 'expense' => 5];
    $categoriesList = $assetCategories->sort(function($a, $b) use ($catTypePriority) {
        $pA = $catTypePriority[$a->type] ?? 99;
        $pB = $catTypePriority[$b->type] ?? 99;
        if ($pA !== $pB) return $pA <=> $pB;
        $codeA = (string) ($a->account_code ?? '');
        $codeB = (string) ($b->account_code ?? '');
        if ($codeA !== $codeB) return strcmp($codeA, $codeB);
        return strcmp($a->title ?? '', $b->title ?? '');
    })->map(function($cat) {
        $typeLabels = [
            'asset' => 'دارایی',
            'liability' => 'بدهی',
            'equity' => 'سرمایه',
            'income' => 'درآمد',
            'expense' => 'هزینه',
        ];
        $typeBadges = [
            'asset' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
            'liability' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
            'equity' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
            'income' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
            'expense' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
        ];
        return [
            'id' => (string) $cat->id,
            'title' => $cat->title,
            'account_code' => $cat->account_code ?? '',
            'type' => $cat->type,
            'type_label' => $typeLabels[$cat->type] ?? $cat->type,
            'type_badge' => $typeBadges[$cat->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        ];
    })->values()->all();
@endphp

@section('content')
    <form action="{{ route('admin.accounting.fund-accounts.store') }}" method="POST" x-data="fundAccountFormHandlers()">
        @csrf
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </span>
                        افزودن حساب خزانه‌داری جدید
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                        اطلاعات حساب خزانه‌داری جدید (بانک، صندوق یا درگاه) را در این فرم وارد کنید.
                    </p>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="{{ $labelClass }}">نام حساب <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="{{ $inputClass }}"
                               required>
                        @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Type --}}
                    <div>
                        <label for="type" class="{{ $labelClass }}">نوع حساب <span class="text-red-500">*</span></label>
                        <select name="type" id="type" x-model="selectedType" class="{{ $inputClass }}" required>
                            <option value="">انتخاب کنید...</option>
                            @foreach($types as $value => $label)
                                <option
                                    value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">اتصال به سرفصل حسابداری <span
                                class="text-red-500">*</span></label>
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ old('category_id') }}',
                            options: @js($categoriesList),
                            get filteredOptions() {
                                if (!this.search.trim()) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o =>
                                    o.title.toLowerCase().includes(q) ||
                                    (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                    (o.type_label && o.type_label.toLowerCase().includes(q))
                                );
                            },
                            select(opt) {
                                this.selectedId = opt ? String(opt.id) : '';
                                this.open = false;
                                this.search = '';
                            },
                            getSelectedTitle() {
                                let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                if (!found) return 'انتخاب سرفصل...';
                                return found.title + (found.type_label ? ' (' + found.type_label + ')' : '');
                            },
                            formatFa(str) {
                                if (!str) return '';
                                const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                return String(str).replace(/[0-9]/g, w => farsi[+w]);
                            }
                        }" class="relative" :class="{ 'z-50': open }">
                            <input type="hidden" name="category_id" :value="selectedId" required>

                            <button type="button" @click="open = !open"
                                    class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                                <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-cloak
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search" placeholder="جستجو سرفصل دارایی..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                </div>

                                <template x-for="opt in filteredOptions" :key="opt.id">
                                    <div @click="select(opt)"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                         :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedId) === String(opt.id) }">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span x-show="opt.account_code" class="text-[10px] text-gray-400"
                                                  x-text="formatFa(opt.account_code)"></span>
                                            <span x-text="opt.title" class="truncate"></span>
                                        </div>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                              :class="opt.type_badge"
                                              x-text="opt.type_label"></span>
                                    </div>
                                </template>

                                <div x-show="filteredOptions.length === 0"
                                     class="p-3 text-xs text-gray-400 text-center">
                                    هیچ سرفصلی پیدا نشد
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">این حساب خزانه به کدام سرفصل از دارایی‌های شما در دفتر کل
                            متصل است؟ (معمولاً موجودی نقد و بانک)</p>
                        @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Bank Specific Fields --}}
                    <template x-if="selectedType === 'bank'">
                        <div
                            class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
                            <div>
                                <label for="bank_name" class="{{ $labelClass }}">نام بانک</label>
                                <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}"
                                       class="{{ $inputClass }}">
                                @error('bank_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="branch_name" class="{{ $labelClass }}">نام شعبه</label>
                                <input type="text" name="branch_name" id="branch_name" value="{{ old('branch_name') }}"
                                       class="{{ $inputClass }}">
                                @error('branch_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="account_holder_name" class="{{ $labelClass }}">نام صاحب حساب</label>
                                <input type="text" name="account_holder_name" id="account_holder_name"
                                       value="{{ old('account_holder_name') }}" class="{{ $inputClass }}">
                                @error('account_holder_name')<p
                                    class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="account_number" class="{{ $labelClass }}">شماره حساب</label>
                                <input type="text" name="account_number" id="account_number"
                                       value="{{ old('account_number') }}" dir="ltr" style="direction: ltr;"
                                       class="{{ $inputClass }} text-left" @input="sanitizeNumber($el)">
                                @error('account_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="card_number" class="{{ $labelClass }}">شماره کارت</label>
                                <input type="text" name="card_number" id="card_number" value="{{ old('card_number') }}"
                                       maxlength="16" dir="ltr" style="direction: ltr;"
                                       class="{{ $inputClass }} text-left" @input="sanitizeNumber($el)">
                                @error('card_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="iban" class="{{ $labelClass }}">شماره شبا (IBAN)</label>
                                <input type="text" name="iban" id="iban" value="{{ old('iban') }}" maxlength="26"
                                       dir="ltr" style="direction: ltr;" class="{{ $inputClass }} text-left"
                                       placeholder="IR123456789012345678901234">
                                @error('iban')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </template>

                    {{-- Gateway Specific Fields --}}
                    <template x-if="selectedType === 'gateway'">
                        <div
                            class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
                            <div>
                                <label for="core_gateway_id" class="{{ $labelClass }}">شناسه درگاه اصلی (Core Gateway
                                    ID)</label>
                                <input type="number" name="core_gateway_id" id="core_gateway_id"
                                       value="{{ old('core_gateway_id') }}" class="{{ $inputClass }}">
                                @error('core_gateway_id')<p
                                    class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </template>

                    {{-- Common Fields --}}
                    <div class="md:col-span-2">
                        <label for="notes" class="{{ $labelClass }}">توضیحات</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div x-data="{ status: {{ old('status', 1) ? 'true' : 'false' }} }" class="md:col-span-2">
                        <label class="{{ $labelClass }}">وضعیت حساب خزانه‌داری</label>
                        <input type="hidden" name="status" :value="status ? 1 : 0">
                        <button type="button"
                                @click="status = !status"
                                class="inline-flex items-center gap-4 px-5 py-3 rounded-2xl border transition-all duration-300 select-none cursor-pointer focus:outline-none shadow-sm active:scale-95"
                                :class="status ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-50 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400'">

                            <div class="w-12 h-6.5 rounded-full p-1 transition-colors duration-300 flex items-center"
                                 :class="status ? 'bg-emerald-500 justify-end' : 'bg-gray-300 dark:bg-gray-600 justify-start'">
                                <span class="w-4.5 h-4.5 rounded-full bg-white shadow-md transition-all"></span>
                            </div>

                            <div class="flex items-center gap-2 font-bold text-sm">
                                <template x-if="status">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        فعال (آماده ثبت تراکنش و دریافت و پرداخت)
                                    </span>
                                </template>
                                <template x-if="!status">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                                        غیرفعال (غیرقابل انتخاب در تراکنش‌ها)
                                    </span>
                                </template>
                            </div>
                        </button>
                        @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Sticky Footer --}}
            <div class="sticky bottom-4 z-40 max-w-screen-2xl mx-auto">
                <div
                    class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <a href="{{ route('admin.accounting.fund-accounts.index') }}"
                       class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        انصراف
                    </a>
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره حساب خزانه‌داری
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('fundAccountFormHandlers', () => ({
                    selectedType: '{{ old('type', 'bank') }}', // Default to bank
                    sanitizeNumber(el) {
                        el.value = el.value.replace(/[^0-9]/g, '');
                    },
                }));
            });
        </script>
    @endpush
@endsection
