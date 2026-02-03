{{-- clients::user.settings.csv-importer --}}
@php
    $title = 'ایمپورت مشتریان از CSV';
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

<div class="flex justify-center">
    <div class="w-full max-w-4xl">

        {{-- هدر --}}
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ایمپورت مشتریان</h1>
            <p class="text-sm text-gray-500 mt-2">مشتریان خود را از طریق فایل CSV به صورت گروهی وارد کنید.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">

            {{-- نوار اعلان موفقیت --}}
            @if(session('success'))
                <div class="bg-emerald-50 border-b border-emerald-100 px-4 py-3 flex items-center gap-3 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-b border-red-100 px-4 py-3 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-6 sm:p-8 space-y-6">

                @if(!$filePath)
                    {{-- مرحله 1: آپلود فایل (فرم استاندارد) --}}
                    <form action="{{ route('user.settings.clients.import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">برای آپلود کلیک کنید</span> یا فایل را اینجا رها کنید</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">CSV, TXT (MAX. 10MB)</p>
                                </div>
                                <input id="dropzone-file" type="file" name="csv_file" class="hidden" accept=".csv,.txt" onchange="this.form.submit()" />
                            </label>
                        </div>
                        <div class="text-center text-xs text-gray-500">
                            پس از انتخاب فایل، آپلود به صورت خودکار انجام می‌شود.
                        </div>
                    </form>
                @else
                    {{-- مرحله 2: مپ کردن ستون‌ها --}}
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">تطبیق ستون‌ها</h3>
                            <button wire:click="resetImport" class="text-sm text-red-600 hover:text-red-800">لغو و بازگشت</button>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <input type="checkbox" wire:model.live="hasHeaders" id="hasHeaders" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="hasHeaders" class="text-sm text-gray-700 dark:text-gray-300">فایل دارای هدر (عنوان ستون) است</label>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-right">ستون فایل CSV</th>
                                        <th scope="col" class="px-6 py-3 text-right">فیلد متناظر در سیستم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($csvHeaders as $index => $header)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                                {{ $header }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <select wire:model="fieldMapping.{{ $index }}" class="{{ $inputClass }}">
                                                    <option value="">-- نادیده گرفتن --</option>
                                                    @foreach($formFields as $fieldId => $fieldLabel)
                                                        <option value="{{ $fieldId }}">{{ $fieldLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                            <button wire:click="processImport" wire:loading.attr="disabled"
                                    class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 focus:ring-4 focus:ring-indigo-500/30 transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="processImport">شروع ایمپورت</span>
                                <span wire:loading wire:target="processImport">در حال پردازش...</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- نمایش خطاها --}}
                @if(count($importErrors) > 0)
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                        <h4 class="text-red-800 dark:text-red-300 font-medium mb-2">خطاها در ایمپورت:</h4>
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
                            @foreach($importErrors as $error)
                                <li>ردیف {{ $error['row'] }}: {{ $error['error'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($importCount > 0 && !$importing)
                     <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
                        <h4 class="text-green-800 dark:text-green-300 font-medium mb-2">نتیجه ایمپورت:</h4>
                        <p class="text-sm text-green-700 dark:text-green-400">
                            {{ $importCount }} رکورد با موفقیت وارد شد.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
