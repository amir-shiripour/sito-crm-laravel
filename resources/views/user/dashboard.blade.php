@extends('layouts.user')

@php
    use App\Support\WidgetRegistry;
    use App\Models\WidgetSetting;
    use App\Models\UserDashboardSetting;

    $title = 'داشبورد';

    // همه ویجت‌های ثبت‌شده از هسته + ماژول‌ها
    $allWidgets = WidgetRegistry::all();

    $userWidgetsKeys = [];
    $userLayout = [];

    if(auth()->check()) {
        $user = auth()->user();

        // اگر User از Spatie\HasRoles استفاده می‌کند:
        $roleIds = method_exists($user, 'roles')
            ? $user->roles()->pluck('id')
            : collect();

        if ($roleIds->isNotEmpty()) {
            $userWidgetsKeys = WidgetSetting::whereIn('role_id', $roleIds)
                ->where('is_active', true)
                ->pluck('widget_key')
                ->unique()
                ->toArray();
        }

        // دریافت تنظیمات چیدمان کاربر
        $dashboardSetting = UserDashboardSetting::where('user_id', $user->id)->first();
        if ($dashboardSetting && !empty($dashboardSetting->layout)) {
            $userLayout = $dashboardSetting->layout;
        }
    }

    // فیلتر کردن ویجت‌های مجاز
    $authorizedWidgets = [];
    foreach($allWidgets as $key => $widget) {
        $enabledForRole = in_array($widget['key'], $userWidgetsKeys, true);
        $hasPermission = true;
        if (!empty($widget['permission']) && auth()->check()) {
            $hasPermission = auth()->user()->can($widget['permission']);
        }

        if ($enabledForRole && $hasPermission) {
            $authorizedWidgets[$key] = $widget;
        }
    }

    // مرتب‌سازی ویجت‌ها بر اساس چیدمان کاربر
    $sortedWidgets = [];
    $hiddenWidgets = []; // برای استفاده در JS

    // لیست نهایی برای رندر (شامل مخفی‌ها هم می‌شود تا در DOM باشند)
    $renderList = [];

    // اگر کاربر چیدمان ذخیره شده دارد
    if (!empty($userLayout)) {
        // ویجت‌های موجود در چیدمان کاربر
        foreach ($userLayout as $item) {
            if (isset($authorizedWidgets[$item['key']])) {
                $widget = $authorizedWidgets[$item['key']];
                $widget['visible'] = $item['visible'] ?? true;

                $renderList[] = $widget;

                if (!$widget['visible']) {
                    $hiddenWidgets[$item['key']] = $widget;
                }

                // حذف از لیست اصلی تا تکراری نشود
                unset($authorizedWidgets[$item['key']]);
            }
        }
    }

    // اضافه کردن ویجت‌های جدید که هنوز در چیدمان کاربر نیستند
    foreach ($authorizedWidgets as $widget) {
        $widget['visible'] = true; // پیش‌فرض نمایش داده شوند
        $renderList[] = $widget;
    }
@endphp

@section('content')
    <div x-data="dashboardManager(@js($renderList), @js($hiddenWidgets))" class="space-y-8 pb-12">

        {{-- هدر و دکمه ویرایش --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </span>
                    داشبورد
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14">
                    نمای کلی وضعیت سیستم و ویجت‌های کاربردی
                </p>
            </div>

            <button
                @click="toggleEditMode()"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all transform active:scale-95"
                :class="editMode ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm'"
            >
                <svg x-show="!editMode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                <svg x-show="editMode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span x-text="editMode ? 'ذخیره چیدمان' : 'شخصی‌سازی داشبورد'"></span>
            </button>
        </div>

        {{-- پنل ویجت‌های مخفی (فقط در حالت ویرایش) --}}
        <div x-show="editMode && Object.keys(hiddenWidgets).length > 0" x-transition class="bg-indigo-50 dark:bg-indigo-900/10 border-2 border-dashed border-indigo-200 dark:border-indigo-800/50 rounded-2xl p-6 animate-in fade-in slide-in-from-top-4">
            <div class="flex items-center gap-2 mb-4">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                </span>
                <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-100">ویجت‌های مخفی شده</h3>
                <span class="text-xs text-indigo-500 dark:text-indigo-400 mr-auto">برای افزودن به داشبورد کلیک کنید</span>
            </div>

            <div class="flex flex-wrap gap-3">
                <template x-for="(widget, key) in hiddenWidgets" :key="key">
                    <button
                        @click="showWidget(key)"
                        class="group flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 rounded-xl border border-indigo-100 dark:border-gray-700 shadow-sm hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                    >
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" x-text="widget.label || widget.key"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- شبکه ویجت‌ها --}}
        <div
            x-ref="grid"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            @foreach($renderList as $widget)
                <div
                    class="widget-card group relative flex flex-col h-full bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-300 hover:shadow-md {{ !$widget['visible'] ? 'hidden' : '' }}"
                    :class="{'ring-2 ring-indigo-500 ring-offset-4 ring-offset-gray-50 dark:ring-offset-gray-900 cursor-move scale-[0.98] opacity-90 hover:opacity-100 hover:scale-100': editMode}"
                    data-key="{{ $widget['key'] }}"
                    id="widget-{{ $widget['key'] }}"
                >
                    {{-- لایه پوششی برای جلوگیری از تعامل با محتوای ویجت در حالت ویرایش --}}
                    <div x-show="editMode" class="absolute inset-0 z-10 bg-white/10 dark:bg-black/10 backdrop-blur-[1px] rounded-2xl cursor-move flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="bg-white dark:bg-gray-800 px-3 py-1.5 rounded-lg shadow-lg text-xs font-bold text-gray-600 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            جابجایی
                        </div>
                    </div>

                    {{-- هدر ویجت در حالت ویرایش --}}
                    <div x-show="editMode" class="relative z-20 px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/80 dark:bg-gray-800/80 rounded-t-2xl backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            <span class="text-xs font-bold">{{ $widget['label'] ?? $widget['key'] }}</span>
                        </div>
                        <button @click="hideWidget('{{ $widget['key'] }}')" class="text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition-all cursor-pointer" title="مخفی کردن">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-6 flex-1">
                        @include($widget['view'])
                    </div>
                </div>
            @endforeach
        </div>

        {{-- پیام خالی بودن داشبورد --}}
        <div x-show="!editMode && document.querySelectorAll('.widget-card:not(.hidden)').length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">داشبورد شما خالی است</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">هیچ ویجتی برای نمایش وجود ندارد. برای افزودن ویجت‌ها روی دکمه شخصی‌سازی کلیک کنید.</p>
            <button @click="toggleEditMode()" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                شخصی‌سازی داشبورد
            </button>
        </div>

    </div>

    {{-- اسکریپت SortableJS --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        function dashboardManager(allWidgets, initialHidden) {
            return {
                editMode: false,
                // تبدیل آرایه ویجت‌ها به آبجکت برای دسترسی سریع‌تر
                widgetsMap: allWidgets.reduce((acc, w) => { acc[w.key] = w; return acc; }, {}),
                hiddenWidgets: initialHidden,
                sortable: null,

                init() {
                    // مقداردهی اولیه SortableJS
                    this.sortable = new Sortable(this.$refs.grid, {
                        animation: 200,
                        disabled: true, // در ابتدا غیرفعال
                        ghostClass: 'opacity-40',
                        dragClass: 'cursor-grabbing',
                        onEnd: () => {
                            // ترتیب جدید را ذخیره نمی‌کنیم تا زمانی که دکمه ذخیره زده شود
                        }
                    });
                },

                toggleEditMode() {
                    this.editMode = !this.editMode;

                    // فعال/غیرفعال کردن Sortable
                    this.sortable.option("disabled", !this.editMode);

                    if (!this.editMode) {
                        this.saveLayout();
                    }
                },

                hideWidget(key) {
                    const el = document.getElementById('widget-' + key);
                    if (el) {
                        // انیمیشن مخفی شدن
                        el.classList.add('scale-90', 'opacity-0');
                        setTimeout(() => {
                            el.classList.add('hidden');
                            el.classList.remove('scale-90', 'opacity-0');

                            // اضافه کردن به لیست مخفی‌ها
                            if (this.widgetsMap[key]) {
                                this.hiddenWidgets[key] = this.widgetsMap[key];
                            }
                        }, 200);
                    }
                },

                showWidget(key) {
                    const el = document.getElementById('widget-' + key);
                    if (el) {
                        el.classList.remove('hidden');
                        delete this.hiddenWidgets[key];

                        // اسکرول به ویجت
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // انیمیشن
                        el.classList.add('ring-4', 'ring-emerald-500', 'scale-105');
                        setTimeout(() => el.classList.remove('ring-4', 'ring-emerald-500', 'scale-105'), 600);
                    }
                },

                saveLayout() {
                    // جمع‌آوری ترتیب فعلی از DOM
                    const grid = this.$refs.grid;
                    const items = Array.from(grid.children);
                    const layout = items.map(item => {
                        const key = item.getAttribute('data-key');
                        const isHidden = item.classList.contains('hidden');
                        return {
                            key: key,
                            visible: !isHidden
                        };
                    });

                    // ارسال به سرور
                    fetch('{{ route("user.dashboard.update-layout") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ layout: layout })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: { type: 'success', text: 'چیدمان داشبورد با موفقیت ذخیره شد' }
                            }));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: { type: 'error', text: 'خطا در ذخیره چیدمان' }
                        }));
                    });
                }
            }
        }
    </script>
@endsection
