@extends('layouts.user')

@php
    $title = 'امکانات ملک';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8" x-data="featuresForm()">

        {{-- هدر صفحه --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                </span>
                    امکانات رفاهی
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">مرحله ۴: امکانات موجود در ملک را انتخاب کنید.</p>
            </div>

            {{-- نوار پیشرفت --}}
            <div class="hidden sm:flex items-center gap-1">
                <span class="h-1 w-8 rounded-full bg-emerald-500"></span>
                <span class="h-1 w-8 rounded-full bg-emerald-500"></span>
                <span class="h-1 w-8 rounded-full bg-emerald-500"></span>
                <span class="h-1 w-8 rounded-full bg-indigo-500"></span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6 sm:p-8">
            <form action="{{ route('user.properties.features.update', $property) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="space-y-8">
                    {{-- ویژگی‌های پیش‌فرض --}}
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            امکانات عمومی
                        </h3>

                        @if($propertyAttributes->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($propertyAttributes as $attr)
                                    @php
                                        $value = $property->attributeValues->where('attribute_id', $attr->id)->first()->value ?? '';
                                        $hasValue = $property->attributeValues->where('attribute_id', $attr->id)->isNotEmpty();
                                    @endphp

                                    @if($attr->type === 'checkbox')
                                        <label class="group flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-white hover:border-indigo-200 cursor-pointer transition-all dark:bg-gray-700/30 dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:border-indigo-700">
                                            <input type="checkbox" name="attributes[{{ $attr->id }}]" value="1" {{ $value == '1' ? 'checked' : '' }}
                                            class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                                            <span class="text-xs font-bold text-gray-700 group-hover:text-indigo-700 dark:text-gray-300 dark:group-hover:text-indigo-300 transition-colors">{{ $attr->name }}</span>
                                        </label>
                                    @elseif($attr->type === 'text')
                                        <div class="col-span-1 sm:col-span-2 md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attr->name }}</label>
                                            <input type="text" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="{{ $inputClass }}">
                                        </div>
                                    @elseif($attr->type === 'number')
                                        <div class="col-span-1 sm:col-span-2 md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attr->name }}</label>
                                            <input type="number" name="attributes[{{ $attr->id }}]" value="{{ $value }}" class="{{ $inputClass }}">
                                        </div>
                                    @elseif($attr->type === 'select')
                                        <div class="col-span-1 sm:col-span-2 md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attr->name }}</label>
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
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">هیچ امکاناتی تعریف نشده است.</p>
                            </div>
                        @endif
                    </div>

                    {{-- امکانات سفارشی --}}
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                امکانات سفارشی (سایر موارد)
                            </h3>
                            <button type="button" @click="addField" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 border border-indigo-100 dark:border-indigo-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                افزودن مورد جدید
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(field, index) in customFields" :key="index">
                                <div class="flex items-center gap-3 animate-in fade-in slide-in-from-right-2">
                                    <div class="flex-1">
                                        <input type="text" :name="`meta[${index}][value]`" x-model="field.value" placeholder="عنوان امکانات (مثلاً: روف گاردن)" class="{{ $inputClass }}">
                                    </div>
                                    <button type="button" @click="removeField(index)" class="p-2.5 text-red-500 bg-red-50 hover:bg-red-100 rounded-xl transition-colors dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 shadow-sm" title="حذف">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>

                            <div x-show="customFields.length === 0" class="text-center py-6 text-xs text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/20">
                                مواردی که در لیست بالا نیست را اینجا اضافه کنید.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('user.properties.details', $property) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 hover:text-gray-900 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        مرحله قبل
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 hover:shadow-emerald-500/50 hover:-translate-y-0.5 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ذخیره و پایان
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function featuresForm() {
            return {
                customFields: @json($customFeatures ?? []), // دریافت مقادیر قبلی یا آرایه خالی

                addField() {
                    this.customFields.push({ value: '' });
                },

                removeField(index) {
                    this.customFields.splice(index, 1);
                }
            }
        }
    </script>
@endsection
