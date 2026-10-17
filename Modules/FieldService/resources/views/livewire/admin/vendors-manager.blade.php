<div class="space-y-4 font-sans">
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-black text-gray-900 dark:text-white">فروشگاه‌های همکار و فعال‌سازی گارانتی</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">سامانه همکاران B2B فروش لوازم خانگی و تاسیسات جهت ثبت نصب خریدار و پورسانت معرف</p>
        </div>

        <button type="button" wire:click="openCreateModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition">
            + ثبت فروشگاه همکار جدید
        </button>
    </div>

    {{-- Vendors Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($vendors as $v)
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm space-y-3">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $v->store_name }}</h3>
                    <div class="text-[11px] text-gray-400 mt-0.5">مالک: {{ $v->owner_name }} ({{ $v->mobile }})</div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300">
                    پورسانت: {{ $v->referral_commission_percent }}٪
                </span>
            </div>

            <div class="text-xs text-gray-600 dark:text-gray-300">
                📍 {{ $v->city }}، {{ $v->address }}
            </div>

            <div class="pt-2 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ $v->orders->count() }} سفارش نصب ثبت‌شده</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">فعال</span>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60">
            فروشگاهی ثبت نشده است.
        </div>
        @endforelse
    </div>

    {{-- Create Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-3xl p-6 space-y-4 shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white">ثبت فروشگاه همکار جدید</h3>
                <button type="button" @click="$wire.showModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام فروشگاه</label>
                    <input type="text" wire:model="store_name" placeholder="فروشگاه لوازم خانگی قائم" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام مدیر فروشگاه</label>
                        <input type="text" wire:model="owner_name" placeholder="نام صاحب فروشگاه" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تلفن همراه</label>
                        <input type="text" wire:model="mobile" placeholder="0911xxxxxxx" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans" dir="ltr">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شهر</label>
                        <input type="text" wire:model="city" placeholder="بابل" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">پورسانت معرف (٪)</label>
                        <input type="number" step="0.5" wire:model="referral_commission_percent" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">آدرس فروشگاه</label>
                    <textarea wire:model="address" rows="2" placeholder="خیابان، پلاک..." class="w-full p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans"></textarea>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" @click="$wire.showModal = false" class="w-1/3 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl">انصراف</button>
                <button type="button" wire:click="saveVendor" class="w-2/3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md">ثبت فروشگاه</button>
            </div>
        </div>
    </div>
    @endif
</div>
