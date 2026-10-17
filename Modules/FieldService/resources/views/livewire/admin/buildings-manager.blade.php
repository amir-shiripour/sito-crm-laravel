<div class="space-y-4 font-sans">
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-black text-gray-900 dark:text-white">مجتمع‌ها و شناسنامه تاسیسات (B2B)</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت ساختمان‌ها، قراردادهای SLA طلایی و شناسنامه موتورخانه و آسانسور</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" wire:click="openCreateModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                + ثبت مجتمع جدید
            </button>
        </div>
    </div>

    {{-- Buildings list --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($buildings as $b)
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm space-y-3">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $b->name }}</h3>
                    <div class="text-[11px] text-gray-400 mt-0.5">مدیر: {{ $b->manager_name }} ({{ $b->manager_phone }})</div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-300">
                    {{ $b->units_count }} واحد
                </span>
            </div>

            <div class="text-xs text-gray-600 dark:text-gray-300">
                📍 {{ $b->city }}، {{ $b->address }}
            </div>

            <div class="pt-2 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs">
                <span class="text-gray-500 dark:text-gray-400">{{ $b->equipments->count() }} تجهیز ثبت‌شده</span>
                @if($b->activeSlaContract())
                <span class="text-amber-600 dark:text-amber-400 font-bold">قرارداد SLA فعال</span>
                @else
                <span class="text-gray-400">بدون قرارداد</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60">
            ساختمانی ثبت نشده است.
        </div>
        @endforelse
    </div>

    {{-- Create Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-3xl p-6 space-y-4 shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white">ثبت مجتمع / ساختمان جدید</h3>
                <button type="button" @click="$wire.showModal = false" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام ساختمان</label>
                    <input type="text" wire:model="name" placeholder="برج مسکونی یاسمن بابل" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام مدیر مجتمع</label>
                        <input type="text" wire:model="manager_name" placeholder="نام مدیر" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره تماس مدیر</label>
                        <input type="text" wire:model="manager_phone" placeholder="0911xxxxxxx" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans" dir="ltr">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تعداد واحدها</label>
                        <input type="number" wire:model="units_count" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شهر</label>
                        <input type="text" wire:model="city" placeholder="بابل" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">آدرس کامل</label>
                    <textarea wire:model="address" rows="2" placeholder="خیابان، کوچه..." class="w-full p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs font-sans"></textarea>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" @click="$wire.showModal = false" class="w-1/3 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl">انصراف</button>
                <button type="button" wire:click="saveBuilding" class="w-2/3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md">ثبت مجتمع</button>
            </div>
        </div>
    </div>
    @endif
</div>
