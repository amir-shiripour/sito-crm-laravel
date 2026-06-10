@php
    $title = 'خروجی مشتریان';
@endphp

<div class="w-full max-w-6xl mx-auto">

    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center justify-between mb-8 border-b border-gray-100 dark:border-gray-700 pb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        خروجی مشتریان به CSV
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        ستون‌هایی که می‌خواهید در فایل خروجی وجود داشته باشند را انتخاب کرده و ترتیب قرارگیری آن‌ها را مشخص کنید.
                    </p>
                </div>
            </div>

            <form wire:submit.prevent="export">
                @if (session()->has('message'))
                    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                        {{ session('message') }}
                    </div>
                @endif
                
                @error('selectedFields')
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Selected Fields (Left Side conceptually, but using flex row-reverse or RTL) --}}
                    <div class="flex flex-col bg-gray-50 dark:bg-gray-900/30 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex justify-between items-center">
                            <h4 class="text-md font-bold text-indigo-700 dark:text-indigo-400 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                ستون‌های خروجی
                            </h4>
                            <button type="button" wire:click="removeAll" class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">
                                حذف همه
                            </button>
                        </div>
                        <div class="p-4 flex-1 overflow-y-auto max-h-[500px]">
                            <div class="space-y-3">
                                @foreach($selectedFields as $index => $id)
                                    @if(isset($formFields[$id]))
                                        <div wire:key="selected-{{ $id }}" class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border-2 border-indigo-100 dark:border-indigo-900/50 rounded-xl shadow-sm hover:shadow-md transition-shadow relative group">
                                            <div class="flex items-center gap-4">
                                                <div class="flex flex-col gap-1 items-center justify-center text-gray-400">
                                                    <button type="button" wire:click="moveUp({{ $index }})" @if($loop->first) disabled class="opacity-30 cursor-not-allowed" @else class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" @endif>
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                                    </button>
                                                    <button type="button" wire:click="moveDown({{ $index }})" @if($loop->last) disabled class="opacity-30 cursor-not-allowed" @else class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" @endif>
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                    </button>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-bold text-indigo-500 dark:text-indigo-400 mb-0.5">ستون {{ $index + 1 }}</span>
                                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $formFields[$id] }}</span>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="removeField('{{ $id }}')" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/20 transition-colors">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                                
                                @if(empty($selectedFields))
                                    <div class="p-10 flex flex-col items-center justify-center text-center border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl bg-white dark:bg-gray-800/50">
                                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                        </div>
                                        <p class="text-sm font-bold text-gray-600 dark:text-gray-400">هیچ ستونی انتخاب نشده است.</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">از لیست سمت راست فیلدهای مورد نظر را اضافه کنید.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Available Fields --}}
                    <div class="flex flex-col bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                            <h4 class="text-md font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                فیلدهای در دسترس
                            </h4>
                            <button type="button" wire:click="addAll" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 transition-colors">
                                افزودن همه
                            </button>
                        </div>
                        <div class="p-4 flex-1 overflow-y-auto max-h-[500px]">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($formFields as $id => $label)
                                    @if(!in_array($id, $selectedFields))
                                        <button wire:key="available-{{ $id }}" type="button" wire:click="addField('{{ $id }}')" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 dark:hover:border-indigo-700 transition-all text-right group">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">{{ $label }}</span>
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        </button>
                                    @endif
                                @endforeach
                                
                                @if(count(array_diff(array_keys($formFields), $selectedFields)) === 0)
                                    <div class="col-span-1 sm:col-span-2 py-8 text-center text-gray-400 text-sm">
                                        تمامی فیلدها به لیست خروجی اضافه شده‌اند.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" wire:model="hasHeaders" id="hasHeaders" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">فایل خروجی شامل ردیف هدر (عناوین ستون‌ها) باشد</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">در صورت فعال بودن، ردیف اول فایل CSV صادر شده شامل نام ستون‌ها (مثلا: نام و نام خانوادگی، موبایل و...) خواهد بود.</span>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 text-base font-bold text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        دریافت فایل خروجی (CSV)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
