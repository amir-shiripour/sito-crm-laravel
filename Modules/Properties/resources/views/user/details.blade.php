@extends('layouts.user')

@php
    $title = 'اطلاعات تکمیلی ملک';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8" x-data="detailsForm()">

        {{-- هدر صفحه --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                </span>
                    اطلاعات تکمیلی
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">مرحله ۳: مشخصات فنی و جزئیات بیشتر ملک را وارد کنید.</p>
            </div>

            {{-- نوار پیشرفت ساده --}}
            <div class="hidden sm:flex items-center gap-1">
                <span class="h-1 w-8 rounded-full bg-emerald-500"></span>
                <span class="h-1 w-8 rounded-full bg-emerald-500"></span>
                <span class="h-1 w-8 rounded-full bg-indigo-500"></span>
                <span class="h-1 w-8 rounded-full bg-gray-200 dark:bg-gray-700"></span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6 sm:p-8">
            <form action="{{ route('user.properties.details.update', $property) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="space-y-8">

                    {{-- ویژگی‌های پیش‌فرض (System Attributes) --}}
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            ویژگی‌های پایه
                        </h3>

                        @if($propertyAttributes->isNotEmpty())
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($propertyAttributes as $attr)
                                    <div>
                                        <label class="{{ $labelClass }}">{{ $attr->name }}</label>

                                        @php
                                            // دریافت مقدار ذخیره شده قبلی
                                            $value = $property->attributeValues->where('attribute_id', $attr->id)->first()->value ?? '';
                                        @endphp

                                        @if($attr->type === 'text')
                                            <input type="text" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="{{ $inputClass }}">
                                        @elseif($attr->type === 'number')
                                            <input type="number" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="{{ $inputClass }}">
                                        @elseif($attr->type === 'select')
                                            <div class="relative">
                                                <select name="attributes[{{ $attr->id }}]" class="{{ $selectClass }}">
                                                    <option value="">انتخاب کنید...</option>
                                                    @foreach($attr->options ?? [] as $option)
                                                        <option value="{{ $option }}" {{ $value == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">هیچ فیلد اطلاعات تکمیلی تعریف نشده است.</p>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                ویژگی‌های سفارشی (دلخواه)
                            </h3>
                            <button type="button" @click="addField" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 border border-indigo-100 dark:border-indigo-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                افزودن مورد جدید
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(field, index) in customFields" :key="index">
                                <div class="flex items-start gap-3 bg-gray-50 dark:bg-gray-900/30 p-3 rounded-xl border border-gray-100 dark:border-gray-700/50 animate-in fade-in slide-in-from-right-2">
                                    <div class="w-1/3">
                                        <input type="text" :name="`meta[${index}][key]`" x-model="field.key" placeholder="عنوان (مثلاً: ویو ابدی)" class="{{ $inputClass }} bg-white dark:bg-gray-800 !py-2">
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" :name="`meta[${index}][value]`" x-model="field.value" placeholder="مقدار (مثلاً: دارد)" class="{{ $inputClass }} bg-white dark:bg-gray-800 !py-2">
                                    </div>
                                    <button type="button" @click="removeField(index)" class="mt-1 p-2 text-red-500 bg-white dark:bg-gray-800 hover:bg-red-50 hover:text-red-700 border border-gray-200 dark:border-gray-600 rounded-lg transition-colors dark:hover:bg-red-900/20 dark:hover:text-red-300 dark:hover:border-red-900/30 shadow-sm" title="حذف">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>

                            <div x-show="customFields.length === 0" class="text-center py-6 text-xs text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/20">
                                می‌توانید ویژگی‌های خاصی که در لیست بالا نیست را اینجا اضافه کنید.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('user.properties.pricing', $property) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 hover:text-gray-900 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        بازگشت به قیمت‌گذاری
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all active:scale-95">
                        ذخیره و ادامه (امکانات)
                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function detailsForm() {
            return {
                // دریافت داده‌های قبلی در صورت وجود (برای ویرایش)
                customFields: @json($customDetails ?? []),

                addField() {
                    this.customFields.push({ key: '', value: '' });
                },

                removeField(index) {
                    this.customFields.splice(index, 1);
                }
            }
        }
    </script>
@endsection
