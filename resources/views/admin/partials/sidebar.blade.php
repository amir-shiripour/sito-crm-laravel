{{-- admin/partials/sidebar.blade.php --}}
<div class="h-full flex flex-col">
    <div class="h-16 px-5 flex items-center border-b border-gray-200/70 dark:border-gray-700/60">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white grid place-content-center font-bold">C</div>
            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">CRM Admin</div>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-widest text-gray-500/80 dark:text-gray-400/70">
                اصلی</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                        {{-- Home icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" class="w-5 h-5 opacity-80">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10.5L12 3l9 7.5V21a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 21v-10.5z"/>
                        </svg>
                        <span>داشبورد</span>
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-widest text-gray-500/80 dark:text-gray-400/70">
                مدیریت</p>
            <ul class="space-y-1">
                @can('menu.see.users')
                    <li>
                        <a href="{{ route('admin.users.index') }}"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                            {{-- Users icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0v.75H4.5v-.75z"/>
                            </svg>
                            <span>کاربران</span>
                        </a>
                    </li>
                @endcan

                @can('menu.see.roles')
                    <li>
                        <a href="{{ route('admin.roles.index') }}"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ request()->routeIs('admin.roles.*') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                            {{-- Roles icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80">
                                <rect x="3" y="4.5" width="18" height="15" rx="2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9M7.5 12h6M7.5 15h4.5"/>
                            </svg>
                            <span>نقش‌ها</span>
                        </a>
                    </li>
                @endcan

                <li>
                    <a href="{{ route('admin.modules.index') }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ request()->routeIs('admin.modules.*') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                        {{-- Modules icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" class="w-5 h-5 opacity-80">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8.5 3.75h3a2 2 0 012 2v.5h1.25a2.5 2.5 0 110 5H13.5v1.25h1.25a2.5 2.5 0 110 5H13.5v.5a2 2 0 01-2 2h-3a2 2 0 01-2-2v-11a2 2 0 012-2z"/>
                        </svg>
                        <span>ماژول‌ها</span>
                    </a>
                </li>

                {{-- Clients menu item --}}
                @can('clients.manage')
                    <li>
                        <a href="{{ Route::has('admin.clients.index') ? route('admin.clients.index') : (Route::has('clients.index') ? route('clients.index') : '#') }}"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ (request()->routeIs('admin.clients.*') || request()->routeIs('clients.*')) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                            {{-- Clients icon (users group) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17.25 9.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM3 20.25a7.5 7.5 0 0114.25 0v.75H3v-.75z"/>
                            </svg>
                            <span>مشتریان</span>
                        </a>
                    </li>
                @endcan

                @can('menu.see.custom-fields')
                    <li>
                        <a href="{{ route('admin.custom-fields.index') }}"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ request()->routeIs('admin.custom-fields.*') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                            {{-- Custom Fields icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5" class="w-5 h-5 opacity-80">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 12h4m4 0h4m-8-4h8m-8 8h8m-4-4H4a1 1 0 00-1 1v12a1 1 0 001 1h16a1 1 0 001-1V9a1 1 0 00-1-1h-8"></path>
                            </svg>
                            <span>مدیریت فیلدها</span>
                        </a>
                    </li>
                @endcan

                {{-- Version Control menu item - Restricted to super-admin based on routes --}}
                @if(auth()->user()->hasRole('super-admin'))
                    <li>
                        <a href="{{ route('admin.version-control.index') }}"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ request()->routeIs('admin.version-control.*') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                            {{-- Version Control (History/Clock) icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.5" class="w-5 h-5 opacity-80">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>کنترل نسخه‌ها</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('settings.index') }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
{{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                        {{-- Settings icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" class="w-5 h-5 opacity-80">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>تنظیمات</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div
        class="px-4 py-3 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-200/70 dark:border-gray-700/60">
        @php
            $currentVersion = \App\Models\VersionLog::current();
        @endphp
        نسخه {{ $currentVersion ? $currentVersion->version_number : app()->version() }}
    </div>
</div>
