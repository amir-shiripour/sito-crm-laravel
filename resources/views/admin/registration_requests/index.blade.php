@extends('layouts.user')

@php
    $title = 'مدیریت درخواست‌های ثبت نام';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </span>
                    درخواست‌های ثبت نام
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">بررسی، تایید و مشاهده جزئیات کاربران جدید</p>
            </div>
        </div>

        {{-- نمایش آلارم موفقیت یا ارور --}}
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3 shadow-sm mb-6">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium flex flex-col gap-2 shadow-sm mb-6">
                @foreach ($errors->all() as $error)
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-800/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif

        {{-- جدول لیست درخواست‌ها --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 w-16 text-center">#</th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400">مشخصات و جزئیات کامل درخواست</th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 w-40 text-center">نقش درخواستی</th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 w-48 text-center">وضعیت بررسی</th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 w-48 text-center">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($requests as $request)
                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-colors duration-150">

                            {{-- آیدی --}}
                            <td class="px-6 py-6 text-gray-400 dark:text-gray-500 font-mono text-sm font-semibold text-center align-top pt-8">
                                {{ $request->id }}
                            </td>

                            {{-- ستون اطلاعات کامل (یوزر + فیلدهای تکمیلی) --}}
                            <td class="px-6 py-6 whitespace-normal align-top">
                                <div class="flex flex-col sm:flex-row items-start gap-4">
                                    {{-- آواتار --}}
                                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl dark:bg-indigo-900/30 dark:text-indigo-400 shrink-0 shadow-sm border border-indigo-100 dark:border-indigo-800/30">
                                        {{ mb_substr($request->name ?? 'ک', 0, 1) }}
                                    </div>

                                    <div class="flex-1 w-full min-w-0">
                                        {{-- اطلاعات پایه (نام، ایمیل، موبایل) --}}
                                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mb-4">
                                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $request->name }}</span>

                                            <div class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded-lg shadow-sm">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                <span class="dir-ltr">{{ $request->email }}</span>
                                            </div>

                                            @if($request->mobile)
                                                <div class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded-lg shadow-sm">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                                    <span class="dir-ltr">{{ $request->mobile }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- اطلاعات تکمیلی (فیلدهای سفارشی) با تشخیص اتوماتیک فایل --}}
                                        @if(!empty($request->custom_fields))
                                            <div class="bg-gray-50/70 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700/70 w-full xl:max-w-3xl">
                                                <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">اطلاعات تکمیلی وارد شده توسط کاربر</span>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-5 gap-x-6">
                                                    @foreach($request->custom_fields as $key => $value)
                                                        @php
                                                            // استخراج اطلاعات فیلد
                                                            $fieldInfo = isset($customFields) && $customFields->has($key) ? $customFields->get($key) : null;
                                                            $fieldLabel = $fieldInfo ? $fieldInfo->label : $key;
                                                            $fieldType = $fieldInfo ? $fieldInfo->field_type : 'text';

                                                            // بررسی آیا این مقدار یک مسیر آپلود شده است (با توجه به تایپ فرم یا مسیر)
                                                            $isFile = $fieldType === 'file' || (is_string($value) && str_starts_with($value, 'uploads/'));
                                                        @endphp

                                                        <div class="flex flex-col">
                                                            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1.5">{{ $fieldLabel }}</span>

                                                            @if($isFile && $value)
                                                                @php
                                                                    $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
                                                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
                                                                @endphp

                                                                @if($isImage)
                                                                    {{-- نمایش پیش‌نمایش تصویر --}}
                                                                    <a href="{{ asset('storage/' . $value) }}" target="_blank" class="block w-24 h-24 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:opacity-80 transition-opacity shadow-sm">
                                                                        <img src="{{ asset('storage/' . $value) }}" alt="{{ $fieldLabel }}" class="w-full h-full object-cover">
                                                                    </a>
                                                                @else
                                                                    {{-- نمایش دکمه دانلود برای سایر فایل‌ها --}}
                                                                    <a href="{{ asset('storage/' . $value) }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-400 px-3 py-2.5 rounded-xl border border-indigo-100 dark:border-indigo-800/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors shadow-sm w-fit">
                                                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                                        دانلود / مشاهده
                                                                    </a>
                                                                @endif
                                                            @else
                                                                {{-- نمایش متن و آرایه برای فیلدهای معمولی --}}
                                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 px-3 py-2 rounded-xl border border-gray-100 dark:border-gray-700/50 shadow-sm leading-relaxed min-h-[42px] flex items-center break-words">
                                                                    @if(is_array($value))
                                                                        {{ implode('، ', $value) }}
                                                                    @else
                                                                        {{ $value ?: '-' }}
                                                                    @endif
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- علت رد (فقط اگر وضعیت رد شده باشد) --}}
                                        @if($request->status === 'rejected' && $request->rejection_reason)
                                            <div class="mt-4 bg-red-50 dark:bg-red-900/10 p-4 rounded-2xl border border-red-100 dark:border-red-800/30 w-full xl:max-w-3xl flex items-start gap-3">
                                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <div>
                                                    <span class="text-sm font-bold text-red-700 dark:text-red-400 block mb-1">علت رد درخواست:</span>
                                                    <span class="text-sm text-red-600 dark:text-red-300 leading-relaxed">{{ $request->rejection_reason }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- ستون نقش --}}
                            <td class="px-6 py-6 align-top pt-8 text-center">
                                <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-lg border border-indigo-200 dark:border-indigo-600/50 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 shadow-sm">
                                    {{ $request->role->display_name ?? $request->role->name ?? 'نامشخص' }}
                                </span>
                            </td>

                            {{-- ستون وضعیت و اطلاعات بررسی کننده --}}
                            <td class="px-6 py-6 align-top pt-8 text-center">
                                @if($request->status === 'pending')
                                    <span class="inline-flex justify-center items-center gap-1.5 w-32 py-2 text-xs font-bold rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 shadow-sm">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        در انتظار تایید
                                    </span>
                                @elseif($request->status === 'approved')
                                    <span class="inline-flex justify-center items-center gap-1.5 w-32 py-2 text-xs font-bold rounded-xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        تایید شده
                                    </span>
                                @elseif($request->status === 'rejected')
                                    <span class="inline-flex justify-center items-center gap-1.5 w-32 py-2 text-xs font-bold rounded-xl border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        رد شده
                                    </span>
                                @endif

                                @if($request->reviewed_by)
                                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700/50 text-xs text-gray-500 dark:text-gray-400 flex flex-col gap-1.5 items-center">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">توسط: {{ $request->reviewer->name ?? 'ناشناس' }}</span>
                                        <span class="dir-ltr inline-block" title="{{ $request->reviewed_at }}">{{ class_exists('\Morilog\Jalali\Jalalian') ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($request->reviewed_at))->format('Y/m/d - H:i') : \Carbon\Carbon::parse($request->reviewed_at)->format('Y/m/d - H:i') }}</span>
                                    </div>
                                @endif
                            </td>

                            {{-- ستون دکمه‌های عملیات --}}
                            <td class="px-6 py-6 align-top pt-8">
                                @if($request->status === 'pending')
                                    <div class="flex flex-col gap-2">
                                        <form action="{{ route('admin.registration-requests.approve', $request) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:text-emerald-400 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800/30 transition-all shadow-sm">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                تایید حساب
                                            </button>
                                        </form>

                                        <button type="button" onclick="openRejectModal({{ $request->id }})" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-red-700 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800/30 transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            رد درخواست
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400 dark:text-gray-600 text-xs mt-2">
                                        -- عملیات پایان یافته --
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">درخواستی یافت نشد</p>
                                    <p class="text-sm mt-1">در حال حاضر هیچ درخواست ثبت نامی در سیستم وجود ندارد.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            @if($requests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal رد درخواست --}}
    <div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm" aria-hidden="true" onclick="closeRejectModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block overflow-hidden text-right align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-200 dark:border-gray-700 relative z-10">
                <form id="rejectForm" method="POST" action="">
                    @csrf
                    <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10 dark:bg-red-900/20 text-red-600 dark:text-red-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-white" id="modal-title">رد درخواست ثبت نام</h3>
                                <div class="mt-4">
                                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">دلیل رد درخواست (برای ثبت در سیستم)</label>
                                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800" placeholder="علت عدم تایید این کاربر را به طور خلاصه بنویسید..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex flex-row-reverse gap-3 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent px-6 py-2.5 bg-red-600 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            ثبت و رد درخواست
                        </button>
                        <button type="button" onclick="closeRejectModal()" class="inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 px-6 py-2.5 bg-white dark:bg-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRejectModal(requestId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/admin/registration-requests/${requestId}/reject`;
            modal.classList.remove('hidden');
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
        }
    </script>
@endsection
