@extends('layouts.user')
@php($title = 'ویرایش کمپین')
@section('content')
    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.sales.campaigns.index') }}" class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">ویرایش کمپین</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">به‌روزرسانی اطلاعات کمپین «{{ $campaign->name }}»</p>
                </div>
            </div>
            
            {{-- Delete Button --}}
            <form action="{{ route('user.sales.campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('آیا از حذف این کمپین اطمینان دارید؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-900/30 rounded-xl text-sm font-bold transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    حذف کمپین
                </button>
            </form>
        </div>

        {{-- Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sm:p-8"
             x-data="{
                 budget: '{{ $campaign->budget ?? '' }}',
                 actualCost: '{{ $campaign->actual_cost ?? '' }}',
                 get formattedBudget() {
                     return this.budget ? Number(this.budget.toString().replace(/\D/g, '')).toLocaleString() : '';
                 },
                 updateBudget(val) {
                     this.budget = val.replace(/\D/g, '');
                 },
                 get formattedActualCost() {
                     return this.actualCost ? Number(this.actualCost.toString().replace(/\D/g, '')).toLocaleString() : '';
                 },
                 updateActualCost(val) {
                     this.actualCost = val.replace(/\D/g, '');
                 }
             }">
            <form action="{{ route('user.sales.campaigns.update', $campaign) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">نام کمپین <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">نوع کمپین <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                            <option value="sms" {{ old('type', $campaign->type) === 'sms' ? 'selected' : '' }}>پیامکی</option>
                            <option value="email" {{ old('type', $campaign->type) === 'email' ? 'selected' : '' }}>ایمیلی</option>
                            <option value="call" {{ old('type', $campaign->type) === 'call' ? 'selected' : '' }}>تلفنی</option>
                            <option value="social" {{ old('type', $campaign->type) === 'social' ? 'selected' : '' }}>شبکه‌های اجتماعی</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت کمپین</label>
                        <select name="status" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                            <option value="draft" {{ old('status', $campaign->status) === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                            <option value="active" {{ old('status', $campaign->status) === 'active' ? 'selected' : '' }}>فعال</option>
                            <option value="paused" {{ old('status', $campaign->status) === 'paused' ? 'selected' : '' }}>متوقف‌شده</option>
                            <option value="completed" {{ old('status', $campaign->status) === 'completed' ? 'selected' : '' }}>تکمیل‌شده</option>
                            <option value="archived" {{ old('status', $campaign->status) === 'archived' ? 'selected' : '' }}>بایگانی‌شده</option>
                        </select>
                    </div>

                    <div>
                        @php
                            $initialAssigneeValue = '';
                            if ($campaign->assigned_to) {
                                $initialAssigneeValue = 'user:' . $campaign->assigned_to;
                            } elseif ($campaign->assigned_role) {
                                $initialAssigneeValue = 'role:' . $campaign->assigned_role;
                            }
                        @endphp
                        <div x-data="{
                            open: false,
                            search: '',
                            assigneeType: 'unassigned',
                            selectedVal: '{{ old('assignee_value', $initialAssigneeValue) }}',
                            selectedText: 'تخصیص داده نشده',
                            roles: [
                                @foreach($roles as $role)
                                    { value: 'role:{{ $role->name }}', text: 'نقش: ' + ({ 'super-admin': 'مدیر سیستم (super-admin)', 'sales-agent': 'کارشناس فروش (sales-agent)' }['{{ $role->name }}'] || '{{ $role->name }}'), type: 'role' },
                                @endforeach
                            ],
                            users: [
                                @foreach($users as $user)
                                    { value: 'user:{{ $user->id }}', text: 'کاربر: {{ $user->name }} ({{ $user->email }})', type: 'user' },
                                @endforeach
                            ],
                            init() {
                                if (this.selectedVal.startsWith('role:')) {
                                    this.assigneeType = 'role';
                                    let match = this.roles.find(r => r.value === this.selectedVal);
                                    if (match) this.selectedText = match.text;
                                } else if (this.selectedVal.startsWith('user:')) {
                                    this.assigneeType = 'user';
                                    let match = this.users.find(u => u.value === this.selectedVal);
                                    if (match) this.selectedText = match.text;
                                } else {
                                    this.assigneeType = 'unassigned';
                                    this.selectedText = 'تخصیص داده نشده';
                                }
                            },
                            get filteredItems() {
                                let list = [];
                                if (this.assigneeType === 'role') list = this.roles;
                                else if (this.assigneeType === 'user') list = this.users;
                                
                                if (!this.search) return list;
                                return list.filter(i => i.text.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            select(item) {
                                this.selectedVal = item.value;
                                this.selectedText = item.text;
                                this.open = false;
                                this.search = '';
                            },
                            setAssigneeType(type) {
                                this.assigneeType = type;
                                if (type === 'unassigned') {
                                    this.selectedVal = '';
                                    this.selectedText = 'تخصیص داده نشده';
                                    this.open = false;
                                } else {
                                    this.selectedVal = '';
                                    this.selectedText = type === 'role' ? 'انتخاب نقش...' : 'انتخاب کاربر...';
                                    this.open = true;
                                    this.$nextTick(() => { this.$refs.searchInput?.focus(); });
                                }
                            }
                        }" class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">کارشناس مسئول</label>
                            
                            <!-- Segmented Toggle Control -->
                            <div class="flex rounded-xl bg-gray-100 dark:bg-gray-900 p-1 w-full border border-gray-200 dark:border-gray-700">
                                <button type="button" @click="setAssigneeType('unassigned')" 
                                        class="flex-1 text-center py-1.5 rounded-lg text-xs font-bold transition-all"
                                        :class="assigneeType === 'unassigned' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-750 dark:hover:text-gray-300'">
                                    تخصیص داده نشده
                                </button>
                                <button type="button" @click="setAssigneeType('role')" 
                                        class="flex-1 text-center py-1.5 rounded-lg text-xs font-bold transition-all"
                                        :class="assigneeType === 'role' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-750 dark:hover:text-gray-300'">
                                    نقش سازمانی
                                </button>
                                <button type="button" @click="setAssigneeType('user')" 
                                        class="flex-1 text-center py-1.5 rounded-lg text-xs font-bold transition-all"
                                        :class="assigneeType === 'user' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-750 dark:hover:text-gray-300'">
                                    کاربر خاص
                                </button>
                            </div>

                            <!-- Dropdown Trigger button (only if role or user selected) -->
                            <div x-show="assigneeType !== 'unassigned'" class="relative" style="display: none;">
                                <button type="button" @click="open = !open" 
                                        class="w-full text-right flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-4 py-2.5 text-sm text-gray-900 dark:text-white transition-all">
                                    <span x-text="selectedText"></span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <!-- Hidden Input -->
                                <input type="hidden" name="assignee_value" :value="selectedVal">
                                
                                <!-- Dropdown List -->
                                <div x-show="open" @click.outside="open = false" 
                                     class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl max-h-60 overflow-y-auto p-2"
                                     x-transition style="display: none;">
                                    <!-- Search Input -->
                                    <div class="p-1 mb-2 border-b border-gray-150 dark:border-gray-700">
                                        <input type="text" x-model="search" x-ref="searchInput" @click.stop placeholder="جستجو..." 
                                               class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                    </div>
                                    <!-- Options -->
                                    <ul class="space-y-1">
                                        <template x-for="item in filteredItems" :key="item.value">
                                            <li>
                                                <button type="button" @click="select(item)" 
                                                        class="w-full text-right text-xs px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-300 flex items-center justify-between transition-colors"
                                                        :class="{'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 font-bold': selectedVal === item.value}">
                                                    <span x-text="item.text"></span>
                                                    <div>
                                                        <span x-show="item.type === 'role'" class="bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 text-[10px] px-1.5 py-0.5 rounded">نقش</span>
                                                        <span x-show="item.type === 'user'" class="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 text-[10px] px-1.5 py-0.5 rounded">کاربر</span>
                                                    </div>
                                                </button>
                                            </li>
                                        </template>
                                        <div x-show="filteredItems.length === 0" class="text-center text-xs text-gray-400 py-3">
                                            موردی یافت نشد.
                                        </div>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">تاریخ شروع (جلالی)</label>
                        <input type="text" name="start_date" value="{{ old('start_date', $startDateJalali) }}" data-jdp-only-date class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all text-center" placeholder="۱۴۰۳/۰1/۰1">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">تاریخ پایان (جلالی)</label>
                        <input type="text" name="end_date" value="{{ old('end_date', $endDateJalali) }}" data-jdp-only-date class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all text-center" placeholder="۱۴۰۳/۱۲/۲۹">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">بودجه (ریال)</label>
                        <input type="text" :value="formattedBudget" @input="updateBudget($event.target.value)" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all text-left dir-ltr" placeholder="0">
                        <input type="hidden" name="budget" :value="budget">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">هزینه واقعی (ریال)</label>
                        <input type="text" :value="formattedActualCost" @input="updateActualCost($event.target.value)" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all text-left dir-ltr" placeholder="0">
                        <input type="hidden" name="actual_cost" :value="actualCost">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">هدف اصلی کمپین</label>
                        <select name="goal" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">
                            <option value="">انتخاب هدف</option>
                            <option value="lead_generation" {{ old('goal', $campaign->goal) === 'lead_generation' ? 'selected' : '' }}>تولید سرنخ (Lead Generation)</option>
                            <option value="conversion" {{ old('goal', $campaign->goal) === 'conversion' ? 'selected' : '' }}>افزایش نرخ تبدیل (Conversion)</option>
                            <option value="retention" {{ old('goal', $campaign->goal) === 'retention' ? 'selected' : '' }}>حفظ مشتریان فعلی (Retention)</option>
                            <option value="upsell" {{ old('goal', $campaign->goal) === 'upsell' ? 'selected' : '' }}>بیش‌فروشی (Upsell)</option>
                            <option value="awareness" {{ old('goal', $campaign->goal) === 'awareness' ? 'selected' : '' }}>آگاهی‌بخشی برند (Awareness)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">مخاطبان هدف</label>
                        <input type="text" name="target_audience" value="{{ old('target_audience', is_array($campaign->target_audience) ? implode(', ', $campaign->target_audience) : $campaign->target_audience) }}" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all" placeholder="مثلاً: مشتریان قدیمی">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">توضیحات</label>
                        <textarea name="description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:placeholder-gray-500 transition-all">{{ old('description', $campaign->description) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
                        بروزرسانی کمپین
                    </button>
                </div>
            </form>
        </div>
    </div>
    @include('partials.jalali-date-picker')
@endsection
