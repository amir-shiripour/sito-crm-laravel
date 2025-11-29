{{-- clients::user.clients.quick-widget --}}

@php
    $labelSingular = config('clients.labels.singular', 'مشتری');
    $quickFields   = collect($schema['fields'] ?? [])->where('quick_create', true)->values();
@endphp

<div class="relative inline-block">
    {{-- دکمه ایجاد سریع --}}
    <button x-data @click="$dispatch('client-quick-open')"
            type="button"
            class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-200 text-sm font-medium active:scale-95">
        <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span>ایجاد سریع {{ $labelSingular }}</span>
    </button>

    {{-- مودال --}}
    <div x-data="{ open: false }"
         x-on:client-quick-open.window="open = true"
         x-on:keydown.escape.window="open = false"
         x-show="open"
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">

        {{-- پس‌زمینه تاریک --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
             @click="open = false"></div>

        <div class="flex min-h-dvh items-center justify-center p-4 text-center sm:p-0">
            {{-- پنل مودال --}}
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg dark:bg-gray-800 border border-gray-100 dark:border-gray-700">

                {{-- هدر مودال --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        ایجاد سریع {{ $labelSingular }}
                    </h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- بدنه فرم --}}
                <div class="px-5 py-6 space-y-4 max-h-[60vh] overflow-y-auto">
                    @if($quickFields->isEmpty())
                        <div class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                            هیچ فیلدی برای ایجاد سریع تنظیم نشده است.
                        </div>
                    @else
                        @foreach($quickFields as $i => $field)
                            @php($fid = $field['id'] ?? "qf{$i}")
                            <div wire:key="qc-{{ $fid }}">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    {{ $field['label'] ?? $fid }}
                                    @if(($field['required'] ?? false))
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                @include('clients::user.clients._quick-field', ['field' => $field, 'fid' => $fid])
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- فوتر مودال --}}
                <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-4 flex flex-row-reverse gap-2">
                    <button type="button"
                            wire:click="saveQuick"
                            @if($quickFields->isNotEmpty()) @click="open = false" @else disabled @endif
                            class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed transition-colors items-center gap-2">
                        <span wire:loading.remove target="saveQuick">ذخیره سریع</span>
                        <span wire:loading target="saveQuick" class="flex items-center gap-1">
                           <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                               <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                               <path class="opacity-75" fill="currentColor"
                                     d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042
                                        1.135 5.824 3 7.938ل3-2.647z">
                               </path>
                           </svg>
                        </span>
                    </button>

                    <button type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600"
                            @click="open = false">
                        انصراف
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
