@extends('layouts.user')

@php
    $title = 'امکانات ملک';
@endphp

@section('content')
<div class="max-w-3xl mx-auto" x-data="featuresForm()">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">امکانات رفاهی</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">مرحله ۴: امکانات موجود در ملک را انتخاب کنید.</p>

        <form action="{{ route('user.properties.features.update', $property) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Pre-defined Features --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($propertyAttributes as $attr)
                        @php
                            $hasValue = $property->attributeValues->where('attribute_id', $attr->id)->isNotEmpty();
                        @endphp
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                            <input type="checkbox" name="attributes[]" value="{{ $attr->id }}" {{ $hasValue ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $attr->name }}</span>
                        </label>
                    @endforeach

                    @if($propertyAttributes->isEmpty())
                        <div class="col-span-3 text-center py-8 text-gray-500">
                            هیچ امکاناتی تعریف نشده است.
                        </div>
                    @endif
                </div>

                {{-- Custom Features Repeater --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">امکانات سفارشی</h3>
                    <div class="space-y-3">
                        <template x-for="(field, index) in customFields" :key="index">
                            <div class="flex items-center gap-3">
                                <input type="text" :name="`meta[${index}][value]`" x-model="field.value" placeholder="عنوان امکانات" class="flex-1 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                <button type="button" @click="removeField(index)" class="p-2 text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addField" class="mt-4 text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        افزودن امکانات جدید
                    </button>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('user.properties.details', $property) }}" class="text-sm text-gray-500 hover:text-gray-700">بازگشت به اطلاعات تکمیلی</a>
                <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                    ذخیره و پایان
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function featuresForm() {
        return {
            customFields: @json($customFeatures),
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
