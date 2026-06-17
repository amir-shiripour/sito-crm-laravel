@extends('layouts.user')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    
    $inProgressEnabled = isset($settings['reminders_in_progress_enabled']) ? $settings['reminders_in_progress_enabled'] == '1' : true;
    $snoozeEnabled = isset($settings['reminders_snooze_enabled']) ? $settings['reminders_snooze_enabled'] == '1' : true;
    $snoozeLimit = $settings['reminders_snooze_limit'] ?? 5;
    $reasonRequired = $settings['reminders_snooze_reason_required'] ?? 'optional';
@endphp

@section('title', 'تنظیمات یادآوری و تعویق')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                تنظیمات یادآوری و تعویق
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-10">
                پیکربندی قوانین و رفتارهای سیستم تعویق (Snooze) و ارجاع خودکار (Escalation) در یادآوری‌ها.
            </p>
        </div>

        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3 shadow-sm animate-in fade-in">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('user.reminders.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- بخش اول: مدیریت رفتارهای تعویق --}}
            <div class="{{ $cardClass }}" x-data="{ snoozeEnabled: @json($snoozeEnabled), inProgressEnabled: @json($inProgressEnabled) }">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">قوانین و رفتارهای تعویق</h2>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">نحوه برخورد سیستم با تعویق یادآوری‌ها</p>
                    </div>
                </div>
                
                <div class="p-6 space-y-6">
                    {{-- فعال بودن وضعیت درحال انجام --}}
                    <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-gray-50/50 dark:bg-gray-900/20 border border-gray-100 dark:border-gray-800">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">امکان تغییر وضعیت به درحال انجام</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block">کاربران بتوانند از روی یادآوری، کار مربوطه را در وضعیت "درحال انجام" قرار دهند.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="reminders_in_progress_enabled" value="1" x-model="inProgressEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600 transition-colors"></div>
                        </label>
                    </div>

                    {{-- فعال بودن تعویق --}}
                    <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-gray-50/50 dark:bg-gray-900/20 border border-gray-100 dark:border-gray-800">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">فعال بودن قابلیت تعویق (Snooze)</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block">کاربران بتوانند از منوی کارت یادآوری، آن را به تعویق بیندازند.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="reminders_snooze_enabled" value="1" x-model="snoozeEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600 transition-colors"></div>
                        </label>
                    </div>

                    {{-- تنظیمات زیرمجموعه در صورت فعال بودن تعویق --}}
                    <div x-show="snoozeEnabled" x-transition.opacity.duration.250ms class="space-y-6 pt-2">
                        
                        {{-- حد مجاز تعداد تعویق --}}
                        <div>
                            <label for="reminders_snooze_limit" class="{{ $labelClass }}">حداکثر تعداد مجاز تعویق</label>
                            <input type="number" id="reminders_snooze_limit" name="reminders_snooze_limit" 
                                   value="{{ $snoozeLimit }}" min="1" max="100" class="{{ $inputClass }}">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">
                                سقف تعداد دفعاتی که کاربر می‌تواند یک یادآوری را به تعویق بیندازد. پس از رسیدن به این عدد، سیستم اقدام به ارجاع (Escalation) می‌کند.
                            </p>
                        </div>

                        {{-- دلیل تعویق --}}
                        <div>
                            <label for="reminders_snooze_reason_required" class="{{ $labelClass }}">وضعیت ثبت دلیل تعویق</label>
                            <div class="relative">
                                <select id="reminders_snooze_reason_required" name="reminders_snooze_reason_required" class="{{ $inputClass }} appearance-none cursor-pointer">
                                    <option value="optional" @selected($reasonRequired === 'optional')>اختیاری (در صورت تمایل کاربر دلیل بنویسد)</option>
                                    <option value="required" @selected($reasonRequired === 'required')>اجباری (کاربر حتما باید دلیلی بنویسد)</option>
                                    <option value="disabled" @selected($reasonRequired === 'disabled')>غیرفعال (امکان ثبت دلیل تعویق وجود نداشته باشد)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">
                                در صورتی که روی اجباری یا اختیاری تنظیم شود، یک فیلد متنی در بخش تعویق یادآوری نمایش داده خواهد شد.
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- بخش دوم: مدیریت ارجاع خودکار (Escalation) --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">تنظیمات ارجاع خودکار (Escalation)</h2>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">تعیین گیرندگان اعلان در صورت تعویق بیش از حد</p>
                    </div>
                </div>

                <div class="p-6 space-y-6" x-data="escalationSelects()">
                    {{-- انتخاب نقش‌های سیستم (Multi select) --}}
                    <div>
                        <label class="{{ $labelClass }}">ارجاع خودکار به نقش‌های زیر</label>
                        <div class="relative" @click.outside="openRoles = false">
                            <div class="flex flex-wrap gap-1.5 p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl min-h-[42px] cursor-pointer" @click="openRoles = !openRoles">
                                <template x-if="selectedRoles.length === 0">
                                    <span class="text-gray-400 text-xs py-1.5 px-2">هیچ نقشی انتخاب نشده است...</span>
                                </template>
                                <template x-for="role in selectedRoles" :key="role.id">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400 dark:border-indigo-900/30 rounded-lg">
                                        <span x-text="role.name"></span>
                                        <button type="button" @click.stop="removeRole(role.id)" class="text-indigo-500 hover:text-indigo-700 focus:outline-none">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            
                            {{-- لیست آبشاری نقش‌ها --}}
                            <div x-show="openRoles" style="display: none;" class="absolute z-10 w-full mt-1.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg max-h-48 overflow-y-auto py-1">
                                @foreach($roles as $r)
                                    <div @click="toggleRole({{ $r->id }}, '{{ $r->name }}')" 
                                         class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750 cursor-pointer flex items-center justify-between">
                                        <span>{{ $r->name }}</span>
                                        <span x-show="hasRole({{ $r->id }})" class="text-indigo-600 dark:text-indigo-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="reminders_escalation_roles" x-bind:value="JSON.stringify(selectedRoles.map(r => r.name))">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">
                            وقتی یادآوری به حد مجاز تعویق برسد، اعضای این نقش‌ها یک اعلان سیستمی دریافت خواهند کرد.
                        </p>
                    </div>

                    {{-- انتخاب کاربران خاص (Multi select) --}}
                    <div>
                        <label class="{{ $labelClass }}">ارجاع خودکار به کاربران زیر</label>
                        <div class="relative" @click.outside="openUsers = false">
                            <div class="flex flex-wrap gap-1.5 p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl min-h-[42px] cursor-pointer" @click="openUsers = !openUsers">
                                <template x-if="selectedUsers.length === 0">
                                    <span class="text-gray-400 text-xs py-1.5 px-2">هیچ کاربری انتخاب نشده است...</span>
                                </template>
                                <template x-for="user in selectedUsers" :key="user.id">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400 dark:border-indigo-900/30 rounded-lg">
                                        <span x-text="user.name"></span>
                                        <button type="button" @click.stop="removeUser(user.id)" class="text-indigo-500 hover:text-indigo-700 focus:outline-none">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            
                            {{-- لیست آبشاری کاربران --}}
                            <div x-show="openUsers" style="display: none;" class="absolute z-10 w-full mt-1.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg max-h-48 overflow-y-auto py-1">
                                @foreach($users as $u)
                                    <div @click="toggleUser({{ $u->id }}, '{{ $u->name }}')" 
                                         class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750 cursor-pointer flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ $u->name }}</span>
                                            <span class="text-[10px] text-gray-400">{{ $u->email }}</span>
                                        </div>
                                        <span x-show="hasUser({{ $u->id }})" class="text-indigo-600 dark:text-indigo-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="reminders_escalation_users" x-bind:value="JSON.stringify(selectedUsers.map(u => u.id))">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">
                            می‌توانید علاوه بر نقش‌ها، کاربران خاصی را نیز مستقیما برای دریافت اعلان ارجاع مشخص کنید.
                        </p>
                    </div>

                </div>
            </div>

            {{-- دکمه ذخیره تنظیمات --}}
            <div class="flex items-center justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-md shadow-indigo-500/25">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span>ذخیره تنظیمات یادآوری</span>
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
<script>
function escalationSelects() {
    return {
        openRoles: false,
        openUsers: false,
        
        // نقش‌های از قبل انتخاب شده
        selectedRoles: @json($roles->filter(fn($r) => in_array($r->name, $escalationRoles))->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()),
        
        // کاربران از قبل انتخاب شده
        selectedUsers: @json($users->filter(fn($u) => in_array($u->id, $escalationUsers))->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()),

        toggleRole(id, name) {
            const index = this.selectedRoles.findIndex(r => r.id === id);
            if (index > -1) {
                this.selectedRoles.splice(index, 1);
            } else {
                this.selectedRoles.push({ id, name });
            }
        },
        hasRole(id) {
            return this.selectedRoles.some(r => r.id === id);
        },
        removeRole(id) {
            this.selectedRoles = this.selectedRoles.filter(r => r.id !== id);
        },

        toggleUser(id, name) {
            const index = this.selectedUsers.findIndex(u => u.id === id);
            if (index > -1) {
                this.selectedUsers.splice(index, 1);
            } else {
                this.selectedUsers.push({ id, name });
            }
        },
        hasUser(id) {
            return this.selectedUsers.some(u => u.id === id);
        },
        removeUser(id) {
            this.selectedUsers = this.selectedUsers.filter(u => u.id !== id);
        }
    }
}
</script>
@endpush
