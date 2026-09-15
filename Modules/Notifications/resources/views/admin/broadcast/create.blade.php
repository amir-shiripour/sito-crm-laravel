@extends('layouts.user')

@section('title', 'ارسال اطلاعیه همگانی جدید')

@section('content')
<div class="w-full mx-auto px-4 py-8 space-y-6" x-data="broadcastForm()">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
                ارسال اطلاعیه همگانی جدید
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                ارسال اعلان فوری یا پیام گروهی به کاربران، گروه‌ها و نقش‌های سازمانی مختلف
            </p>
        </div>

        <div>
            <a href="{{ route('admin.notifications.broadcast.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>بازگشت به تاریخچه</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200 dark:border-red-900/40 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.notifications.broadcast.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ستون فرم --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">
                    
                    {{-- عنوان پیام --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">عنوان اطلاعیه <span class="text-red-500">*</span></label>
                        <input type="text" name="title" x-model="title" required
                               placeholder="مثلاً: بروزرسانی سامانه یا جلسه اضطراری"
                               class="w-full text-sm rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- متن پیام --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">متن پیام <span class="text-red-500">*</span></label>
                        <textarea name="message" x-model="message" rows="4" required
                                  placeholder="متن کامل اطلاعیه را اینجا بنویسید..."
                                  class="w-full text-sm rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    {{-- لینک اقدام مرتبط --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">لینک اقدام مرتبط (اختیاری)</label>
                        <input type="url" name="action_url" x-model="actionUrl"
                               placeholder="https://..."
                               class="w-full text-sm rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-left" dir="ltr">
                    </div>

                    {{-- مخاطبان هدف --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2.5">مخاطبان هدف <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750">
                                <input type="radio" name="target_type" value="all" x-model="targetType" class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <div class="text-xs font-bold text-gray-900 dark:text-white">تمام کاربران فعال سامانه</div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">ارسال برای تمامی پرسنل و کاربران موجود</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750">
                                <input type="radio" name="target_type" value="role" x-model="targetType" class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <div class="text-xs font-bold text-gray-900 dark:text-white">بر اساس نقش‌های سازمانی</div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">انتخاب یک یا چند نقش (مثلاً فروش، پشتیبانی)</div>
                                </div>
                            </label>
                        </div>

                        {{-- چک‌باکس نقش‌ها در صورت انتخاب role --}}
                        <div x-show="targetType === 'role'" x-transition class="mt-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 space-y-2">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">انتخاب نقش‌های سازمانی:</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($roles as $role)
                                    <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="target_roles[]" value="{{ $role->name }}"
                                               class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500">
                                        <span>{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ستون تنظیمات و پیش‌نمایش --}}
            <div class="space-y-6">
                
                {{-- ویژگی‌های اعلان --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700">ویژگی‌ها و کانال‌های ارسال</h3>

                    {{-- کانال‌ها --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">کانال‌های تحویل <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="channels[]" value="database" checked
                                       class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500">
                                <span>درون‌برنامه‌ای (پنل کاربری و تاپ‌بار)</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="channels[]" value="sms"
                                       class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500">
                                <span>پیامک (SMS به شماره همراه)</span>
                            </label>
                        </div>
                    </div>

                    {{-- دسته‌بندی --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">دسته‌بندی</label>
                        <select name="category" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white">
                            @foreach($categories as $cKey => $cVal)
                                <option value="{{ $cKey }}" {{ $cKey === 'broadcast' ? 'selected' : '' }}>{{ $cVal['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- اولویت --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اولویت</label>
                        <select name="priority" x-model="priority" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white">
                            @foreach($priorities as $pKey => $pVal)
                                <option value="{{ $pKey }}" {{ $pKey === 'normal' ? 'selected' : '' }}>{{ $pVal['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- سطح اهمیت / تم رنگی --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نوع پیام (سطح اهمیت)</label>
                        <select name="severity" x-model="severity" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white">
                            @foreach($severities as $sKey => $sVal)
                                <option value="{{ $sKey }}">{{ $sVal['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- دکمه تایید و ارسال --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="submit"
                                onclick="return confirm('آیا از ارسال این اطلاعیه به مخاطبان انتخاب‌شده اطمینان دارید؟');"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            <span>ارسال فوری اطلاعیه</span>
                        </button>
                    </div>

                </div>

                {{-- کارت پیش‌نمایش زنده --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-4 space-y-3">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">پیش‌نمایش زنده در پنل کاربر</div>
                    
                    <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-indigo-100 dark:border-indigo-900/40 shadow-sm flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             :class="{
                                'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400': severity === 'info',
                                'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400': severity === 'success',
                                'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400': severity === 'warning',
                                'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400': severity === 'danger'
                             }">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-gray-900 dark:text-white" x-text="title || 'عنوان پیام'"></div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2" x-text="message || 'متن پیام در اینجا نمایش داده خواهد شد...'"></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </form>
</div>
@endsection

@push('js')
<script>
function broadcastForm() {
    return {
        title: '',
        message: '',
        actionUrl: '',
        targetType: 'all',
        priority: 'normal',
        severity: 'info',
    }
}
</script>
@endpush
