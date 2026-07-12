<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" dir="rtl">
    <!-- Left/Center Column: Contacts List -->
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <h3 class="text-base font-extrabold text-gray-900 dark:text-white">لیست مخاطبین هدف کمپین</h3>
                
                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو در مخاطبین کمپین..." 
                        class="w-full text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Bulk Assign Contacts Bar -->
            @if(count($selectedContactIds) > 0)
                <div class="mb-4 bg-indigo-50 dark:bg-indigo-950/20 px-4 py-2.5 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 flex flex-col sm:flex-row items-center justify-between gap-4 animate-fade-in">
                    <span class="text-xs text-indigo-700 dark:text-indigo-400 font-bold whitespace-nowrap">
                        {{ count($selectedContactIds) }} مخاطب انتخاب شده:
                    </span>
                    <div class="flex items-center gap-2">
                        <!-- Searchable Dropdown component using Alpine -->
                        <div x-data="{
                            open: false,
                            search: '',
                            assigneeType: 'unassigned',
                            selectedVal: @entangle('assigneeValue'),
                            selectedText: 'انتخاب کارشناس/نقش...',
                            roles: [
                                @foreach($roles as $role)
                                    { value: 'role:{{ $role->name }}', text: 'نقش: ' + ({ 'super-admin': 'مدیر سیستم (super-admin)', 'sales-agent': 'کارشناس فروش (sales-agent)' }['{{ $role->name }}'] || '{{ $role->name }}'), type: 'role' },
                                @endforeach
                            ],
                            users: [
                                @foreach($salesAgents as $agent)
                                    { value: 'user:{{ $agent->id }}', text: 'کاربر: {{ $agent->name }}', type: 'user' },
                                @endforeach
                            ],
                            init() {
                                this.$watch('selectedVal', val => {
                                    if (!val) {
                                        this.assigneeType = 'unassigned';
                                        this.selectedText = 'تخصیص داده نشده';
                                        return;
                                    }
                                    if (val.startsWith('role:')) {
                                        this.assigneeType = 'role';
                                        let match = this.roles.find(r => r.value === val);
                                        this.selectedText = match ? match.text : 'انتخاب نقش...';
                                    } else if (val.startsWith('user:')) {
                                        this.assigneeType = 'user';
                                        let match = this.users.find(u => u.value === val);
                                        this.selectedText = match ? match.text : 'انتخاب کاربر...';
                                    }
                                });
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
                        }" class="flex flex-col sm:flex-row items-center gap-2">
                            <!-- Segmented Toggle Control -->
                            <div class="flex rounded-xl bg-gray-100 dark:bg-gray-900 p-0.5 border border-gray-200 dark:border-gray-700">
                                <button type="button" @click="setAssigneeType('role')" 
                                        class="px-3 py-1 rounded-lg text-[10px] font-bold transition-all"
                                        :class="assigneeType === 'role' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-750 dark:hover:text-gray-300'">
                                    نقش سازمانی
                                </button>
                                <button type="button" @click="setAssigneeType('user')" 
                                        class="px-3 py-1 rounded-lg text-[10px] font-bold transition-all"
                                        :class="assigneeType === 'user' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-750 dark:hover:text-gray-300'">
                                    کاربر خاص
                                </button>
                            </div>

                            <!-- Dropdown Trigger button (only if role or user selected) -->
                            <div x-show="assigneeType !== 'unassigned'" class="relative" style="display: none;">
                                <button type="button" @click="open = !open" 
                                        class="w-56 text-right flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-1.5 text-[11px] text-gray-700 dark:text-gray-300 transition-all">
                                    <span x-text="selectedText"></span>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.outside="open = false" 
                                     class="absolute z-50 mt-1 w-56 bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 shadow-xl max-h-48 overflow-y-auto p-2"
                                     x-transition style="display: none;">
                                    <div class="p-1 mb-2 border-b border-gray-100 dark:border-gray-700">
                                        <input type="text" x-model="search" x-ref="searchInput" @click.stop placeholder="جستجو..." 
                                               class="w-full text-[10px] rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-2 py-1 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                    </div>
                                    <ul class="space-y-1">
                                        <template x-for="item in filteredItems" :key="item.value">
                                            <li>
                                                <button type="button" @click="select(item)" 
                                                        class="w-full text-right text-[10px] px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-750 dark:text-gray-300 flex items-center justify-between transition-colors"
                                                        :class="{'bg-indigo-50 dark:bg-indigo-950/20 font-bold': selectedVal === item.value}">
                                                    <span x-text="item.text"></span>
                                                    <div>
                                                        <span x-show="item.type === 'role'" class="bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 text-[9px] px-1 py-0.2 rounded">نقش</span>
                                                        <span x-show="item.type === 'user'" class="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 text-[9px] px-1 py-0.2 rounded">کاربر</span>
                                                    </div>
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>


                        <button wire:click="bulkAssignContacts" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold px-4 py-1.5 rounded-xl shadow transition-all whitespace-nowrap">
                            تخصیص کارشناس / نقش
                        </button>
                    </div>
                </div>
            @endif

            <!-- Contacts Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-bold border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="p-4 w-12 text-center">
                                <input type="checkbox" 
                                       x-on:click="$wire.toggleSelectAllContacts([{{ implode(',', $contactIdsOnPage) }}])"
                                       {{ count($selectedContactIds) === count($contactIdsOnPage) && count($contactIdsOnPage) > 0 ? 'checked' : '' }}
                                       class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                            </th>
                            <th class="p-4">نام</th>
                            <th class="p-4">شماره تماس</th>
                            <th class="p-4">منبع ثبت</th>
                            <th class="p-4">مسئول پیگیری</th>
                            <th class="p-4">وضعیت تماس</th>
                            <th class="p-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-gray-900 dark:text-gray-100">
                        @forelse($contacts as $contact)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-colors">
                                <td class="p-4 text-center">
                                    <input type="checkbox" value="{{ $contact->id }}" wire:model.live="selectedContactIds"
                                           class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                </td>
                                <td class="p-4 font-bold text-gray-900 dark:text-white">
                                    {{ $contact->name }}
                                    @if($contact->client_id)
                                        <span class="text-[9px] bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400 px-1.5 py-0.5 rounded font-normal ms-1">عضو CRM</span>
                                    @endif
                                </td>
                                <td class="p-4 font-semibold" dir="ltr">{{ $contact->phone }}</td>
                                <td class="p-4">
                                    @if($contact->source === 'import')
                                        <span class="text-gray-400">ثبت گروهی</span>
                                    @elseif($contact->source === 'manual')
                                        <span class="text-blue-500 font-medium">ثبت دستی</span>
                                    @else
                                        <span class="text-gray-400">فیلتر سیستمی</span>
                                    @endif
                                </td>
                                <td class="p-4 font-semibold text-gray-700 dark:text-gray-300">
                                    @if($contact->assignee)
                                        {{ $contact->assignee->name }}
                                    @elseif($contact->assigned_role)
                                        <span class="bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 px-2 py-0.5 rounded text-[10px]">
                                            نقش: {{ array_key_exists($contact->assigned_role, ['super-admin' => 'مدیر سیستم', 'sales-agent' => 'کارشناس فروش']) ? ['super-admin' => 'مدیر سیستم', 'sales-agent' => 'کارشناس فروش'][$contact->assigned_role] : $contact->assigned_role }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">تخصیص نیافته</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <select 
                                        wire:change="updateContactStatus({{ $contact->id }}, $event.target.value)"
                                        class="text-[11px] rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 py-1 px-2 focus:ring-1 focus:ring-indigo-500"
                                        {{ $contact->status === 'converted' ? 'disabled' : '' }}
                                    >
                                        <option value="pending" {{ $contact->status === 'pending' ? 'selected' : '' }}>در انتظار تماس</option>
                                        <option value="contacted" {{ $contact->status === 'contacted' ? 'selected' : '' }}>تماس گرفته شده</option>
                                        <option value="responded" {{ $contact->status === 'responded' ? 'selected' : '' }}>پاسخ‌داده‌شده</option>
                                        <option value="converted" {{ $contact->status === 'converted' ? 'selected' : '' }} disabled>تبدیل به پرونده</option>
                                        <option value="lost" {{ $contact->status === 'lost' ? 'selected' : '' }}>شکست خورده</option>
                                    </select>
                                </td>
                                <td class="p-4 text-center flex items-center justify-center gap-2">
                                    @if(!\Modules\Sales\App\Models\SalesSetting::getValue('auto_create_deal', false))
                                        @if($contact->status !== 'converted')
                                            <button wire:click="convertToDeal({{ $contact->id }})" 
                                                    title="تبدیل به پرونده فروش (Deal)"
                                                    class="text-emerald-600 hover:text-emerald-800 transition-colors p-1.5 bg-emerald-50 dark:bg-emerald-950/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 flex items-center gap-1 font-bold">
                                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <span>تبدیل به پرونده</span>
                                            </button>
                                        @else
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 text-[11px] bg-emerald-50 dark:bg-emerald-950/30 px-2 py-1 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                پرونده فعال
                                            </span>
                                        @endif
                                    @else
                                        @if($contact->status === 'converted')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 text-[11px] bg-emerald-50 dark:bg-emerald-950/30 px-2 py-1 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                پرونده فعال
                                            </span>
                                        @endif
                                    @endif

                                    <button wire:click="deleteContact({{ $contact->id }})" 
                                            onclick="return confirm('آیا از حذف این مخاطب از کمپین مطمئن هستید؟')" 
                                            title="حذف مخاطب"
                                            class="text-red-500 hover:text-red-700 transition-colors p-1.5 bg-red-50 dark:bg-red-950/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-400">
                                    هیچ مخاطبی هنوز به این کمپین تخصیص نیافته است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>

    <!-- Right Column: Add Contacts Controls -->
    <div class="space-y-6" x-data="{ tab: 'bulk' }">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <!-- Tabs Navigation -->
            <div class="flex border-b border-gray-100 dark:border-gray-700 mb-6">
                <button @click="tab = 'bulk'" :class="tab === 'bulk' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-400'" class="flex-1 text-center py-2.5 font-bold border-b-2 text-xs transition-colors">
                    افزودن دسته‌جمعی شماره‌ها
                </button>
                <button @click="tab = 'single'" :class="tab === 'single' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-400'" class="flex-1 text-center py-2.5 font-bold border-b-2 text-xs transition-colors">
                    ثبت تکی مخاطب
                </button>
            </div>

            <!-- Tab Content: Bulk Add -->
            <div x-show="tab === 'bulk'" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2">لیست شماره‌های تماس (هر شماره در یک خط جدید یا با کاما جدا شود):</label>
                    <textarea wire:model="bulkNumbers" rows="8" placeholder="09120000000&#10;09350000000&#10;09210000000" class="w-full text-xs rounded-xl border-gray-200 bg-gray-50 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 transition-all text-left dir-ltr"></textarea>
                    @error('bulkNumbers') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <button wire:click="addBulk" class="w-full py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
                    افزودن شماره‌ها به کمپین
                </button>
            </div>

            <!-- Tab Content: Single Add -->
            <div x-show="tab === 'single'" class="space-y-4">
                <form wire:submit.prevent="addSingle" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-2">نام مخاطب <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="مثال: علی رضایی" class="w-full text-xs rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 transition-all">
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-2">شماره تماس <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="phone" placeholder="0912xxxxxxx" class="w-full text-xs rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 transition-all text-left dir-ltr">
                        @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-2">پست الکترونیکی (اختیاری)</label>
                        <input type="email" wire:model="email" placeholder="example@mail.com" class="w-full text-xs rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 transition-all text-left dir-ltr">
                        @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
                        ثبت مخاطب
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
