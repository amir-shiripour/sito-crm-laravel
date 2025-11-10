<div class="h-16 px-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center gap-2 overflow-hidden">
        <svg class="w-7 h-7 shrink-0 text-indigo-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2a10 10 0 1 1-7.07 2.93A10 10 0 0 1 12 2Zm0 4a6 6 0 1 0 6 6 6 6 0 0 0-6-6Z"/>
        </svg>
        <span x-show="!sidebarCollapsed" x-transition
              class="font-bold truncate">داشبورد</span>
    </div>

    <button @click="toggleSidebar()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" :title="sidebarCollapsed ? 'باز کردن منو' : 'جمع کردن منو'">
        <svg x-show="!sidebarCollapsed" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24"><path d="M18 12H6M12 18l-6-6 6-6"/></svg>
        <svg x-show="sidebarCollapsed" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24"><path d="M6 12h12M12 6l6 6-6 6"/></svg>
    </button>
</div>

<nav class="p-3">
    <a href="#" class=" my-1 flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700" :class="isActive('dashboard') ? 'bg-gray-100 dark:bg-gray-700' : ''" :title="sidebarCollapsed ? 'پیشخوان' : null">
        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10a1 1 0 0 1 .4-.8l7-5a1 1 0 0 1 1.2 0l7 5a1 1 0 0 1 .4.8V19a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1Z"/></svg>

        <span x-show="!sidebarCollapsed" x-transition class="truncate">پیشخوان</span>
    </a>

    <div x-data="{ open: isMenuOpen('clients') }" class="mt-1">
        <button @click="toggleMenu('clients'); open = !open" class="w-full flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700" :title="sidebarCollapsed ? 'مشتریان' : null">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4Z"/></svg>
            <span x-show="!sidebarCollapsed" class="flex-1 text-start">مشتریان</span>
            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5"/></svg>
        </button>

        <div x-show="open && !sidebarCollapsed" x-collapse class="pr-4 mt-1 space-y-1">
            <a href="#" class="block rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                لیست مشتریان
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                افزودن مشتری
            </a>
        </div>
    </div>

    <a href="#" class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700" :title="sidebarCollapsed ? 'سفارش‌ها' : null">
        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6a2 2 0 0 1 2-2h3l1-2h4l1 2h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/></svg>
        <span x-show="!sidebarCollapsed" x-transition class="truncate">سفارش‌ها</span>
    </a>
</nav>
