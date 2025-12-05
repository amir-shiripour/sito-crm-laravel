{{-- modules/Clients/resources/views/widgets/client-quick-form.blade.php --}}
{{-- فرم inline برای ویجت ایجاد سریع کلاینت --}}

@php
    // مثل quick-widget: فیلتر فیلدهای quick_create
    $fields = collect($quickFields ?? ($schema['fields'] ?? []))
        ->where('quick_create', true)
        ->values();
@endphp

<div class="mt-4">
    @if($fields->isEmpty())
        <div class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
            هیچ فیلدی برای ایجاد سریع تنظیم نشده است.
        </div>
    @else
        <form wire:submit.prevent="saveQuick" class="space-y-4">
            {{-- گرید فیلدها، هماهنگ با استایل کلی فرم‌ها --}}
            <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                @foreach($fields as $i => $field)
                    @php($fid = $field['id'] ?? "qf{$i}")

                    <div wire:key="widget-qc-{{ $fid }}" class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $field['label'] ?? $fid }}
                            @if(($field['required'] ?? false))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        {{-- استفاده مجدد از partial اصلی برای یکپارچگی کامل --}}
                        @include('clients::user.clients._quick-field', [
                            'field' => $field,
                            'fid'   => $fid,
                        ])
                    </div>
                @endforeach
            </div>

            {{-- فوتر فرم ویجت: دکمه ذخیره سریع --}}
            <div class="flex justify-end pt-2">
                <button
                    type="submit"
                    wire:click="saveQuick"
                    wire:loading.attr="disabled"
                    @if($fields->isEmpty()) disabled @endif
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span wire:loading.remove wire:target="saveQuick">
                        ذخیره سریع
                    </span>

                    <span wire:loading wire:target="saveQuick" class="flex items-center gap-1">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>
