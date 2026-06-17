<div class="space-y-6 text-right" dir="rtl">
    <div class="flex justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white">مدیریت وضعیت‌های سفارش</h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">امکان تعریف مراحل و فرآیندهای خرید مجزا برای ادمین و کلاینت به صورت کاملا پویا</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl text-xs transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            افزودن وضعیت جدید
        </button>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl dark:bg-emerald-950/20 dark:border-emerald-900/30 dark:text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- فرم -->
        <div class="lg:col-span-1 {{ $isEditing ? 'block' : 'hidden lg:block' }}">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
                <h3 class="text-base font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ $editingId ? 'ویرایش وضعیت' : 'افزودن وضعیت جدید' }}
                </h3>
                
                <form wire:submit.prevent="save" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">عنوان اختصاصی (برای ادمین)</label>
                        <input type="text" wire:model="admin_label" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500" placeholder="مثال: بررسی در انبار مرکزی">
                        @error('admin_label') <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">عنوان عمومی (نمایشی به کلاینت)</label>
                        <input type="text" wire:model="client_label" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500" placeholder="مثال: در حال آماده‌سازی">
                        @error('client_label') <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نگاشت به نوع سیستمی (System Type)</label>
                        <select wire:model="system_type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                            <option value="pending">در انتظار پرداخت (Pending)</option>
                            <option value="processing">در حال پردازش (Processing)</option>
                            <option value="shipped">ارسال شده (Shipped)</option>
                            <option value="delivered">تحویل داده شده (Delivered)</option>
                            <option value="canceled">لغو شده (Canceled)</option>
                            <option value="returned">مرجوعی (Returned)</option>
                        </select>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5 leading-relaxed">تذکر: این نگاشت مشخص می‌کند که سیستم، سفارشاتِ با این وضعیت را برای مسائلی مانند مدیریت انبار و تراکنش‌های مالی چطور در نظر بگیرد.</p>
                        @error('system_type') <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- بخش انتخاب بصری رنگ -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-3">انتخاب بصری رنگ لیبل (پالت رنگ ویژه)</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach($colorPresets as $name => $classes)
                                <button type="button" wire:click="selectColor('{{ $name }}')" 
                                    class="h-9 rounded-xl border flex items-center justify-center text-[10px] font-bold transition-all relative overflow-hidden {{ $classes }} {{ $color_class === $classes ? 'ring-2 ring-indigo-500 ring-offset-2 dark:ring-offset-gray-800 scale-105' : 'hover:scale-102' }}">
                                    @if($color_class === $classes)
                                        <span class="absolute top-0.5 right-0.5 bg-indigo-600 text-white rounded-full w-3 h-3 flex items-center justify-center text-[8px]">✓</span>
                                    @endif
                                    {{ ucfirst($name) }}
                                </button>
                            @endforeach
                        </div>
                        <div class="mt-3 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-2xl flex items-center justify-between border border-gray-100 dark:border-gray-700/50 text-xs">
                            <span class="text-gray-400 text-[10px]">پیش‌نمایش لیبل:</span>
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold border {{ str_replace('bg-', 'border-', $color_class) }} {{ $color_class }}">
                                {{ $client_label ?: 'نمونه وضعیت' }}
                            </span>
                        </div>
                        @error('color_class') <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">ترتیب اولویت نمایش</label>
                        <input type="number" wire:model="sort_order" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                        @error('sort_order') <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- کنترل‌های پیشرفته استپر و دید کلاینت -->
                    <div class="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 space-y-4">
                        <h4 class="text-xs font-black text-gray-800 dark:text-gray-200 border-b dark:border-gray-700/50 pb-2 mb-2">دسترسی و استپرها</h4>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-700 dark:text-gray-300">۱. قابل نمایش به کلاینت؟</span>
                            <button type="button" wire:click="$set('show_to_client', {{ !$show_to_client ? 'true' : 'false' }})" 
                                class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $show_to_client ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $show_to_client ? '-translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-700 dark:text-gray-300">۲. مرحله در استپر کلاینت؟</span>
                            <button type="button" wire:click="$set('show_in_client_stepper', {{ !$show_in_client_stepper ? 'true' : 'false' }})" 
                                class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $show_in_client_stepper ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $show_in_client_stepper ? '-translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-700 dark:text-gray-300">۳. مرحله در استپر ادمین؟</span>
                            <button type="button" wire:click="$set('show_in_admin_stepper', {{ !$show_in_admin_stepper ? 'true' : 'false' }})" 
                                class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $show_in_admin_stepper ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $show_in_admin_stepper ? '-translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-700 dark:text-gray-300">۴. فعال و دردسترس؟</span>
                            <button type="button" wire:click="$set('is_active', {{ !$is_active ? 'true' : 'false' }})" 
                                class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $is_active ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $is_active ? '-translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 dark:border-gray-700/50 pt-4">
                        @if($isEditing || $editingId)
                            <button type="button" wire:click="cancel" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 px-4 py-2.5 rounded-2xl text-xs font-bold transition-colors">انصراف</button>
                        @endif
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-2xl text-xs font-bold transition-colors shadow-sm">
                            {{ $editingId ? 'بروزرسانی وضعیت' : 'ذخیره وضعیت' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- لیست -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <span class="font-bold text-gray-900 dark:text-white text-base">لیست مراحل فرآیند سفارش</span>
                    <span class="text-[10px] text-gray-400 dark:text-gray-500">قابلیت کشیدن و رها کردن برای تنظیم نهایی ترتیب نمایش</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50/50 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="py-4 px-5 font-bold">ترتیب</th>
                                <th class="py-4 px-5 font-bold">موقعیت ادمین (Admin)</th>
                                <th class="py-4 px-5 font-bold text-center">موقعیت کلاینت (Client)</th>
                                <th class="py-4 px-5 font-bold text-center">پیکربندی استپر</th>
                                <th class="py-4 px-5 font-bold text-left pl-8">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50" wire:sortable="updateOrder">
                            @foreach($statuses as $status)
                                <tr wire:sortable.item="{{ $status->id }}" wire:key="status-row-{{ $status->id }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors text-gray-900 dark:text-white">
                                    <td class="py-4 px-5">
                                        <div wire:sortable.handle class="cursor-move text-gray-400 hover:text-gray-600 inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                            </svg>
                                            <span class="font-mono text-xs font-semibold">{{ $status->sort_order }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $status->admin_label }}</div>
                                        <div class="text-[9px] text-gray-400 mt-0.5">سیستم: {{ $status->system_type }}</div>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        @if($status->show_to_client)
                                            <span class="inline-block px-3 py-1 rounded-xl text-[10px] font-extrabold border {{ str_replace('bg-', 'border-', $status->color_class) }} {{ $status->color_class }}">
                                                {{ $status->client_label }}
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-1 rounded-xl text-[9px] font-bold bg-gray-100 text-gray-400 dark:bg-gray-900 dark:text-gray-600">
                                                مخفی از کلاینت
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="flex flex-col gap-1 items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] px-1.5 py-0.5 rounded {{ $status->show_in_admin_stepper ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30' : 'bg-gray-50 text-gray-400 dark:bg-gray-900 dark:text-gray-600' }}">استپر ادمین</span>
                                                <span class="text-[9px] px-1.5 py-0.5 rounded {{ $status->show_in_client_stepper ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30' : 'bg-gray-50 text-gray-400 dark:bg-gray-900 dark:text-gray-600' }}">استپر کلاینت</span>
                                            </div>
                                            <div>
                                                @if($status->is_active)
                                                    <span class="text-[9px] text-emerald-600 font-bold bg-emerald-50 dark:bg-emerald-950/20 px-1.5 py-0.5 rounded">فعال</span>
                                                @else
                                                    <span class="text-[9px] text-rose-600 font-bold bg-rose-50 dark:bg-rose-950/20 px-1.5 py-0.5 rounded">غیرفعال</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 text-left pl-8">
                                        <div class="flex gap-2 justify-end">
                                            <button wire:click="edit({{ $status->id }})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 rounded-xl transition-colors" title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button wire:click="delete({{ $status->id }})" wire:confirm="آیا از حذف این وضعیت اطمینان دارید؟" class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition-colors" title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($statuses->isEmpty())
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 dark:text-gray-500">هیچ وضعیتی یافت نشد.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
