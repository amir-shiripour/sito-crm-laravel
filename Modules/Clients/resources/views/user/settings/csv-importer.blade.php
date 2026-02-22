@php
    $title = 'ایمپورت مشتریان از CSV';
@endphp

<div class="flex justify-center">
    <div class="w-full max-w-5xl">
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">ایمپورت مشتریان از CSV</h3>
                    @if($filePath)
                        <button wire:click="resetImport" class="text-sm font-medium text-red-600 hover:text-red-700 transition-colors">
                            لغو و شروع مجدد
                        </button>
                    @endif
                </div>

                {{-- Display final results --}}
                @if($importCount > 0 || $updateCount > 0)
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 dark:bg-green-900/20 dark:border-green-800">
                        <h4 class="text-sm font-bold text-green-800 dark:text-green-300 mb-2">عملیات با موفقیت انجام شد</h4>
                        <p class="text-xs text-green-700 dark:text-green-400">
                            {{ $importCount }} مشتری جدید ایجاد و {{ $updateCount }} مشتری موجود آپدیت شد.
                        </p>
                    </div>
                @endif

                @if(count($importErrors) > 0)
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-800">
                        <h4 class="text-sm font-bold text-red-800 dark:text-red-300 mb-2 flex items-center gap-2">
                            خطاها در حین ایمپورت ({{ count($importErrors) }} مورد)
                        </h4>
                        <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-400 space-y-1 max-h-40 overflow-y-auto">
                            @foreach($importErrors as $error)
                                <li>ردیف {{ $error['row'] }}: {{ $error['error'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                @if(!$filePath)
                    {{-- Step 1: Upload --}}
                    <form action="{{ route('user.settings.clients.import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/></svg>
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
                    {{-- Step 2: Mapping --}}
                    <div wire:loading wire:target="processImport" class="w-full">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center dark:bg-blue-900/20 dark:border-blue-800">
                            <div class="animate-spin w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                            <h4 class="text-lg font-bold text-blue-800 dark:text-blue-300 mb-2">در حال پردازش فایل...</h4>
                            <p class="text-sm text-blue-600 dark:text-blue-400">لطفاً تا پایان عملیات صبر کنید و صفحه را نبندید.</p>
                        </div>
                    </div>

                    <div wire:loading.remove wire:target="processImport" class="space-y-8">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model.live="hasHeaders" id="hasHeaders" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="hasHeaders" class="text-sm text-gray-700 dark:text-gray-300">فایل دارای هدر (عنوان ستون) است</label>
                        </div>

                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-right">ستون فایل CSV</th>
                                    <th scope="col" class="px-6 py-3 text-right">فیلد متناظر در سیستم</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($csvHeaders as $index => $header)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                            {{ $header }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <select wire:model="fieldMapping.{{ $index }}" class="block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
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

                        <!-- Options Section -->
                        <div class="mt-8 bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800 rounded-xl p-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" wire:model="updateExisting" id="updateExisting" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">آپدیت مشتریان موجود</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        اگر مشتری با موبایل، ایمیل، کد ملی یا شماره پرونده یکسان در سیستم وجود داشته باشد، اطلاعات آن آپدیت می‌شود. در غیر این صورت، مشتری جدید ساخته خواهد شد.
                                    </span>
                                </div>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button wire:click="processImport" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform active:scale-95 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                شروع عملیات ایمپورت
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
