<div class="space-y-4 font-sans">
    {{-- Header & Filters --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-black text-gray-900 dark:text-white">مجتمع‌ها و شناسنامه تاسیسات (B2B)</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت ساختمان‌ها، قراردادهای نگهداری SLA، پایش تجهیزات و اعزام دوره‌ای تکنسین</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" wire:click="openCreateModal" class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500 text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-indigo-600/20 font-sans">
                <span>➕</span>
                <span>ثبت مجتمع جدید</span>
            </button>

            <div class="relative w-56">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی مجتمع، کارفرما یا شهر..." class="w-full h-9 pr-8 pl-3 text-xs rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 font-sans">
                <span class="absolute right-2.5 top-2.5 text-gray-400 text-xs">🔍</span>
            </div>

            <select wire:model.live="typeFilter" class="h-9 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه کاربری‌ها</option>
                <option value="residential">مجتمع مسکونی</option>
                <option value="commercial">مجتمع تجاری / پاساژ</option>
                <option value="administrative">اداری / سازمانی</option>
                <option value="industrial">کارخانه / صنعتی</option>
                <option value="hospital">بیمارستان / درمانی</option>
                <option value="complex">برج و شهرک</option>
            </select>

            <select wire:model.live="slaFilter" class="h-9 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه وضعیت‌های SLA</option>
                <option value="has_sla">دارای قرارداد SLA فعال</option>
                <option value="no_sla">بدون قرارداد نگهداری</option>
            </select>
        </div>
    </div>

    {{-- Buildings Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($buildings as $b)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 p-5 shadow-sm flex flex-col justify-between space-y-4 hover:border-indigo-300 dark:hover:border-indigo-500/40 transition-all">
            <div>
                {{-- Top Badge & Name --}}
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                                🏢
                            </span>
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $b->name }}</h3>
                        </div>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-300">
                            {{ $b->building_type_label }}
                        </span>
                    </div>

                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300">
                        {{ $b->units_count }} واحد / {{ $b->floors_count }} طبقه
                    </span>
                </div>

                {{-- Client & Manager Info --}}
                <div class="mt-3.5 space-y-2 text-xs">
                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <span class="text-gray-400">کارفرما:</span>
                        @if($b->client)
                            <a href="{{ route('clients.show', $b->client->id) }}" target="_blank" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <span>{{ $b->client->full_name }}</span>
                                <span class="text-[10px]">↗</span>
                            </a>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">{{ $b->manager_name ?: 'ثبت نشده' }}</span>
                        @endif
                        @if($b->manager_phone)
                            <span class="text-gray-400">-</span>
                            <span class="dir-ltr text-[11px] text-gray-500 dark:text-gray-400">{{ $b->manager_phone }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <span>📍</span>
                        <span class="font-bold">{{ $b->province }}</span>
                        <span class="text-gray-400">/</span>
                        <span>{{ $b->city }}</span>
                        <span class="text-gray-400">-</span>
                        <span class="text-gray-500 dark:text-gray-400 text-[11px] truncate max-w-[170px]" title="{{ $b->address }}">{{ $b->address ?: 'بدون آدرس دقیق' }}</span>
                    </div>
                </div>

                {{-- Equipment & SLA Stats Bar --}}
                <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 text-center">
                    <div class="bg-gray-50 dark:bg-gray-750 p-2 rounded-xl">
                        <div class="text-[10px] text-gray-400">تجهیزات فنی</div>
                        <div class="font-black text-xs text-gray-800 dark:text-gray-200 mt-0.5 flex items-center justify-center gap-1">
                            <span>{{ $b->equipments->count() }} دستگاه</span>
                            @if($b->critical_equipments_count > 0)
                                <span class="w-2 h-2 rounded-full bg-rose-500" title="{{ $b->critical_equipments_count }} تجهیز نیازمند تعمیر"></span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-750 p-2 rounded-xl">
                        <div class="text-[10px] text-gray-400">قرارداد نگهداری</div>
                        <div class="mt-0.5">
                            @if($b->activeSlaContract())
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                    ★ {{ $b->activeSlaContract()->tier_label }}
                                </span>
                            @else
                                <span class="text-[10px] text-gray-400">بدون قرارداد فعال</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="pt-2 flex items-center justify-between gap-2">
                <button type="button" wire:click="openPassport({{ $b->id }})" class="flex-1 py-1.5 px-3 rounded-xl text-xs font-bold transition bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 flex items-center justify-center gap-1">
                    <span>📋</span>
                    <span>شناسنامه تاسیسات</span>
                </button>

                <button type="button" wire:click="editBuilding({{ $b->id }})" class="p-1.5 rounded-lg text-gray-500 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="ویرایش مجتمع">
                    ✏️
                </button>

                <button type="button" wire:click="deleteBuilding({{ $b->id }})" wire:confirm="آیا از حذف این ساختمان و شناسنامه آن اطمینان دارید؟" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition" title="حذف مجتمع">
                    🗑️
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 font-sans">
            هیچ ساختمانی ثبت نشده است. می‌توانید با کلیک روی «ثبت مجتمع جدید» اولین ساختمان را تعریف نمایید.
        </div>
        @endforelse
    </div>

    @if($buildings->hasPages())
    <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 font-sans">
        {{ $buildings->links() }}
    </div>
    @endif

    {{-- Create / Edit Building Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-2xl w-full p-6 space-y-4 my-8 max-h-[90vh] overflow-y-auto font-sans" @click.outside="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🏢</span>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">
                        {{ $editingBuildingId ? 'ویرایش مشخصات مجتمع / ساختمان' : 'ثبت ساختمان و پرونده B2B جدید' }}
                    </h2>
                </div>
                <button type="button" wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg">✕</button>
            </div>

            <form wire:submit.prevent="saveBuilding" class="space-y-4">
                {{-- Client Selection Row --}}
                <div class="p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-indigo-900 dark:text-indigo-300">
                            کارفرما / مشتری طرف قرارداد (CRM Client)
                        </label>
                        <button type="button" wire:click="$toggle('isCreatingNewClient')" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 underline">
                            {{ $isCreatingNewClient ? 'انتخاب از مشتریان موجود' : '+ ثبت مشتری جدید' }}
                        </button>
                    </div>

                    @if(!$isCreatingNewClient)
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="clientSearch" class="w-full h-10 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="نام یا شماره تماس مشتری را جستجو کنید...">
                            @if($selectedClientId)
                                <button type="button" wire:click="clearSelectedClient" class="absolute left-2.5 top-2.5 text-xs text-rose-500 font-bold">حذف انتخاب</button>
                            @endif

                            @if($matchedClients->isNotEmpty() && !$selectedClientId)
                            <div class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($matchedClients as $mc)
                                <button type="button" wire:click="selectClient({{ $mc->id }})" class="w-full p-2.5 text-right text-xs hover:bg-indigo-50 dark:hover:bg-indigo-900/30 flex items-center justify-between">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mc->full_name }}</span>
                                    <span class="dir-ltr text-[11px] text-gray-500 dark:text-gray-400">{{ $mc->phone }}</span>
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">نام کارفرما / مدیر</label>
                                <input type="text" wire:model="newClientName" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans" placeholder="مهندس حسینی">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">شماره تماس</label>
                                <input type="text" wire:model="newClientPhone" dir="ltr" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans text-right" placeholder="09112110000">
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Building Name & Type --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام مجتمع یا ساختمان *</label>
                        <input type="text" wire:model="name" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="مثال: برج مسکونی نیلوفر">
                        @error('name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نوع کاربری *</label>
                        <select wire:model="building_type" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            <option value="residential">مجتمع مسکونی</option>
                            <option value="commercial">مجتمع تجاری / پاساژ</option>
                            <option value="administrative">اداری / سازمانی</option>
                            <option value="industrial">کارخانه / صنعتی</option>
                            <option value="hospital">بیمارستان / درمانی</option>
                            <option value="complex">برج و شهرک ساختمانی</option>
                        </select>
                    </div>
                </div>

                {{-- Units & Floors --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تعداد واحدها</label>
                        <input type="number" wire:model="units_count" min="1" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تعداد طبقات</label>
                        <input type="number" wire:model="floors_count" min="1" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans">
                    </div>
                </div>

                {{-- Manager Contact --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام مدیر / رابط مجتمع</label>
                        <input type="text" wire:model="manager_name" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans" placeholder="آقای احمدی">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره تماس مدیر</label>
                        <input type="text" wire:model="manager_phone" dir="ltr" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans text-right" placeholder="0911...">
                    </div>
                </div>

                {{-- Province & City (Strict Core ProvinceCity) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">استان *</label>
                        <select wire:model.live="province" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            @foreach($provinces as $prov)
                                <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                        @error('province') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شهر *</label>
                        <select wire:model="city" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            @foreach($cities as $ct)
                                <option value="{{ $ct }}">{{ $ct }}</option>
                            @endforeach
                        </select>
                        @error('city') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Address & Notes --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">آدرس دقیق مجتمع *</label>
                    <textarea wire:model="address" rows="2" class="w-full p-2.5 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans" placeholder="بابل، کمربندی غربی، خیابان توحید، مجتمع نیلوفر"></textarea>
                    @error('address') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">یادداشت و توضیحات فنی</label>
                    <textarea wire:model="notes" rows="1" class="w-full p-2 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans" placeholder="توضیحات دسترسی به موتورخانه یا کلیدبان"></textarea>
                </div>

                {{-- Modal Actions --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition font-sans">
                        انصراف
                    </button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500 text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-indigo-600/30 font-sans">
                        <span>ذخیره مجتمع</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Building Passport Drawer (شناسنامه تاسیسات و تجهیزات) --}}
    @if($showPassportDrawer && $activeBuilding)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-4xl w-full p-6 space-y-5 my-8 max-h-[92vh] overflow-y-auto font-sans" @click.outside="closePassport">
            {{-- Drawer Header --}}
            <div class="flex items-start justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📋</span>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">
                            شناسنامه فنی و تاسیسات: {{ $activeBuilding->name }}
                        </h2>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span>📍 {{ $activeBuilding->province }} / {{ $activeBuilding->city }}</span>
                        <span>•</span>
                        <span>مدیر: {{ $activeBuilding->manager_name ?: 'نامشخص' }} ({{ $activeBuilding->manager_phone }})</span>
                        @if($activeBuilding->client)
                        <span>•</span>
                        <a href="{{ route('clients.show', $activeBuilding->client->id) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            پرونده کارفرما در CRM ↗
                        </a>
                        @endif
                    </div>
                </div>

                <button type="button" wire:click="closePassport" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl p-1">✕</button>
            </div>

            {{-- Drawer Tabs --}}
            <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                <button type="button" wire:click="$set('passportTab', 'equipments')" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $passportTab === 'equipments' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <span>⚙️</span>
                    <span>تجهیزات و موتورخانه ({{ $activeBuilding->equipments->count() }})</span>
                </button>

                <button type="button" wire:click="$set('passportTab', 'sla')" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $passportTab === 'sla' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <span>📜</span>
                    <span>قراردادهای نگهداری SLA ({{ $activeBuilding->slaContracts->count() }})</span>
                </button>

                <button type="button" wire:click="$set('passportTab', 'orders')" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $passportTab === 'orders' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <span>🔧</span>
                    <span>سوابق مراجعات تکنسین ({{ $activeBuilding->orders->count() }})</span>
                </button>
            </div>

            {{-- TAB 1: EQUIPMENTS --}}
            @if($passportTab === 'equipments')
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">فهرست شناسنامه چیلر، بویلر، بوستر پمپ و آسانسورهای این مجتمع</p>
                    <button type="button" wire:click="toggleAddEquipment" class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 text-xs font-bold transition flex items-center gap-1">
                        <span>➕</span>
                        <span>{{ $showAddEquipmentForm ? 'بستن فرم' : 'افزودن تجهیز جدید' }}</span>
                    </button>
                </div>

                {{-- Add Equipment Form --}}
                @if($showAddEquipmentForm)
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 space-y-3">
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">مشخصات تجهیز جدید</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">نوع تجهیز *</label>
                            <select wire:model="equipType" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                                <option value="boiler_room">موتورخانه مرکزی و مشعل</option>
                                <option value="absorption_chiller">چیلر جذبی</option>
                                <option value="compression_chiller">چیلر تراکمی</option>
                                <option value="booster_pump">بوستر پمپ آبرسانی</option>
                                <option value="elevator">آسانسور و بالابر</option>
                                <option value="generator">دیزل ژنراتور اضطراری</option>
                                <option value="vrf">سیستم اسپلیت مرکزی (VRF)</option>
                                <option value="package_central">پکیج و گرمایش مرکزی</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">عنوان تجهیز *</label>
                            <input type="text" wire:model="equipTitle" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans" placeholder="مثال: چیلر جذبی سنچوری ۳۰۰ تن">
                            @error('equipTitle') <span class="text-[10px] text-rose-500 block mt-0.5">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">برند و مدل</label>
                            <input type="text" wire:model="equipBrandModel" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans" placeholder="مثال: Century AR-D300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">شماره سریال</label>
                            <input type="text" wire:model="equipSerialNumber" dir="ltr" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans text-right" placeholder="SN-2022-9841">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">سال نصب</label>
                            <input type="text" wire:model="equipInstallationYear" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans" placeholder="1401">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">وضعیت سلامت فنی</label>
                            <select wire:model="equipHealthStatus" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                                <option value="good">سالم و در حال کار</option>
                                <option value="needs_inspection">نیازمند بررسی و سرویس</option>
                                <option value="critical">بحرانی / نیازمند تعمیر فوری</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" wire:click="toggleAddEquipment" class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-sans">انصراف</button>
                        <button type="button" wire:click="saveEquipment" class="px-5 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold shadow-sm font-sans">ثبت تجهیز در شناسنامه</button>
                    </div>
                </div>
                @endif

                {{-- Equipment Cards --}}
                <div class="space-y-2.5">
                    @forelse($activeBuilding->equipments as $eq)
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-gray-750 border border-gray-200/80 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center text-lg">
                                ⚙️
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-xs text-gray-900 dark:text-white">{{ $eq->title }}</h4>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $eq->health_badge_class }}">
                                        {{ $eq->health_status_label }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-3">
                                    <span>نوع: {{ $eq->equipment_type_label }}</span>
                                    @if($eq->brand_model) <span>• مدل: {{ $eq->brand_model }}</span> @endif
                                    @if($eq->serial_number) <span>• سریال: {{ $eq->serial_number }}</span> @endif
                                    @if($eq->installation_year) <span>• سال نصب: {{ $eq->installation_year }}</span> @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end md:self-center">
                            {{-- Quick Health Status Changer --}}
                            <select wire:change="updateEquipmentHealth({{ $eq->id }}, $event.target.value)" class="h-8 px-2 text-[11px] rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans cursor-pointer">
                                <option value="good" {{ $eq->health_status === 'good' ? 'selected' : '' }}>سالم</option>
                                <option value="needs_inspection" {{ $eq->health_status === 'needs_inspection' ? 'selected' : '' }}>نیازمند سرویس</option>
                                <option value="critical" {{ $eq->health_status === 'critical' ? 'selected' : '' }}>بحرانی</option>
                            </select>

                            <button type="button" wire:click="deleteEquipment({{ $eq->id }})" wire:confirm="آیا از حذف این تجهیز اطمینان دارید؟" class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg transition" title="حذف تجهیز">
                                🗑️
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-750 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        هنوز هیچ تجهیزی در شناسنامه تاسیسات این ساختمان ثبت نشده است. با دکمه بالا اولین تجهیز را اضافه کنید.
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- TAB 2: SLA CONTRACTS --}}
            @if($passportTab === 'sla')
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">قراردادهای پشتیبانی و پایش دوره‌ای (SLA) منعقد شده با کارفرما</p>
                    <button type="button" wire:click="toggleAddSla" class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 text-xs font-bold transition flex items-center gap-1">
                        <span>➕</span>
                        <span>{{ $showAddSlaForm ? 'بستن فرم' : 'ثبت قرارداد SLA جدید' }}</span>
                    </button>
                </div>

                @if($showAddSlaForm)
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 space-y-3">
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">مشخصات قرارداد نگهداری دوره‌ای (SLA)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">عنوان قرارداد *</label>
                            <input type="text" wire:model="slaTitle" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">شماره قرارداد *</label>
                            <input type="text" wire:model="slaContractNumber" dir="ltr" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans text-right">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">سطح تعهد (SLA Tier)</label>
                            <select wire:model="slaTier" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                                <option value="gold_sla">طلایی (پاسخ اضطراری زیر ۲ ساعت)</option>
                                <option value="silver">نقره‌ای (پاسخ اضطراری زیر ۴ ساعت)</option>
                                <option value="bronze">برنزی (پاسخ اضطراری زیر ۱۲ ساعت)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">تاریخ شروع *</label>
                            <input type="date" wire:model="slaStartDate" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">تاریخ پایان *</label>
                            <input type="date" wire:model="slaEndDate" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">مبلغ ماهانه (ریال)</label>
                            <input type="number" wire:model="slaMonthlyFee" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">تعداد بازدید در ماه</label>
                            <input type="number" wire:model="slaVisitsPerMonth" min="1" max="10" class="w-full h-9 px-3 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-sans">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" wire:click="toggleAddSla" class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-sans">انصراف</button>
                        <button type="button" wire:click="saveSlaContract" class="px-5 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold shadow-sm font-sans">ثبت و فعال‌سازی قرارداد</button>
                    </div>
                </div>
                @endif

                <div class="space-y-3">
                    @forelse($activeBuilding->slaContracts as $contract)
                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-750 border border-gray-200/80 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-xs text-gray-900 dark:text-white">{{ $contract->title }}</h4>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $contract->tier_badge_class }}">
                                    {{ $contract->tier_label }}
                                </span>
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-4">
                                <span>شماره: {{ $contract->contract_number }}</span>
                                <span>• شروع: {{ $contract->start_date?->format('Y/m/d') }}</span>
                                <span>• پایان: {{ $contract->end_date?->format('Y/m/d') }}</span>
                                <span>• بازدید ماهانه: {{ $contract->periodic_visits_per_month }} نوبت</span>
                                <span>• پاسخ اضطراری: حداکثر {{ $contract->emergency_response_hours }} ساعت</span>
                            </div>
                        </div>

                        <div class="text-left">
                            <div class="text-[11px] text-gray-400">شهریه ماهانه</div>
                            <div class="font-black text-xs text-indigo-600 dark:text-indigo-400">{{ number_format($contract->monthly_fee) }} ریال</div>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-750 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        هنوز قرارداد SLA فعالی برای این ساختمان ثبت نشده است.
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- TAB 3: ORDERS --}}
            @if($passportTab === 'orders')
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">تاریخچه اعزام‌های ثبت‌شده برای تاسیسات این ساختمان</p>
                    <a href="{{ route('user.fieldservice.orders.index') }}" class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition flex items-center gap-1 shadow-sm font-sans">
                        <span>➕</span>
                        <span>اعزام تکنسین برای این مجتمع</span>
                    </a>
                </div>

                <div class="space-y-2">
                    @forelse($activeBuilding->orders as $order)
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-gray-800 dark:text-gray-200">سفارش #{{ $order->order_number ?? $order->id }}</span>
                            <span class="text-gray-400 mr-2">{{ $order->category?->name ?? 'خدمات عمومی' }}</span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            {{ $order->created_at?->format('Y/m/d') }}
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-750 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        هنوز سفارشی برای این مجتمع ثبت نگردیده است.
                    </div>
                    @endforelse
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
