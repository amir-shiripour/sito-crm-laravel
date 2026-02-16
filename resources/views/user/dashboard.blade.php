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
    <div x-data="dashboardManager(@js($renderList), @js($hiddenWidgets))" class="space-y-6">

        {{-- هدر و دکمه ویرایش --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">داشبورد</h1>
            <button
                @click="toggleEditMode()"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all"
                :class="editMode ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
            >
                <svg x-show="!editMode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                <svg x-show="editMode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span x-text="editMode ? 'ذخیره تغییرات' : 'شخصی‌سازی داشبورد'"></span>
            </button>
        </div>

        {{-- پنل ویجت‌های مخفی (فقط در حالت ویرایش) --}}
        <div x-show="editMode && Object.keys(hiddenWidgets).length > 0" x-transition class="bg-gray-100 dark:bg-gray-800/50 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-4">
            <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-3">ویجت‌های مخفی شده (برای نمایش کلیک کنید)</h3>
            <div class="flex flex-wrap gap-3">
                <template x-for="(widget, key) in hiddenWidgets" :key="key">
                    <button
                        @click="showWidget(key)"
                        class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:border-blue-500 dark:hover:border-blue-500 transition-colors"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="widget.label || widget.key"></span>
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
                    class="widget-card group relative flex flex-col h-full bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 {{ !$widget['visible'] ? 'hidden' : '' }}"
                    :class="{'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-gray-900 cursor-move': editMode}"
                    data-key="{{ $widget['key'] }}"
                    id="widget-{{ $widget['key'] }}"
                >
                    {{-- لایه پوششی برای جلوگیری از تعامل با محتوای ویجت در حالت ویرایش --}}
                    <div x-show="editMode" class="absolute inset-0 z-10 bg-transparent cursor-move"></div>

                    {{-- هدر ویجت در حالت ویرایش --}}
                    <div x-show="editMode" class="relative z-20 p-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 rounded-t-2xl">
                        <div class="flex items-center gap-2 text-gray-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <span class="text-xs font-bold">{{ $widget['label'] ?? $widget['key'] }}</span>
                        </div>
                        <button @click="hideWidget('{{ $widget['key'] }}')" class="text-gray-400 hover:text-red-500 transition-colors p-1 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-6 flex-1">
                        @include($widget['view'])
                    </div>
                </div>
            @endforeach
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
                        animation: 150,
                        disabled: true, // در ابتدا غیرفعال
                        ghostClass: 'opacity-50',
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
                        el.classList.add('hidden');

                        // اضافه کردن به لیست مخفی‌ها
                        if (this.widgetsMap[key]) {
                            this.hiddenWidgets[key] = this.widgetsMap[key];
                        }
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
                        el.classList.add('ring-4', 'ring-green-500');
                        setTimeout(() => el.classList.remove('ring-4', 'ring-green-500'), 1000);
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
                                detail: { type: 'success', text: 'چیدمان داشبورد ذخیره شد' }
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
