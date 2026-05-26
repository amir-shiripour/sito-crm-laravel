@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:bg-gray-800";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
@endphp

<div class="grid grid-cols-12 gap-6">
    {{-- Sidebar for forms list and settings --}}
    <div class="col-span-12 lg:col-span-4 space-y-6">
        <div class="{{ $cardClass }}">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">فرم‌های تسویه حساب</h3>
                <button wire:click="createNewForm" class="px-3 py-2 text-xs font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all">
                    ایجاد فرم جدید
                </button>
            </div>
            <div class="p-2 space-y-1 max-h-48 overflow-y-auto">
                @foreach($forms as $form)
                    <div wire:click="selectForm({{ $form->id }})"
                         class="p-3 rounded-lg cursor-pointer {{ $activeFormId == $form->id ? 'bg-indigo-100 dark:bg-indigo-900/50' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <p class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $form->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $form->key }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if($activeFormId)
            <div class="{{ $cardClass }} animate-in fade-in">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات عمومی فرم</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label for="form-name" class="{{ $labelClass }}">نام فرم</label>
                        <input id="form-name" type="text" wire:model.defer="name" class="{{ $inputClass }}">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="form-key" class="{{ $labelClass }}">کلید (Key)</label>
                        <input id="form-key" type="text" wire:model.defer="key" class="{{ $inputClass }} text-left dir-ltr font-mono">
                        @error('key') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="form-product" class="{{ $labelClass }}">اختصاص به محصول (Override)</label>
                        <select id="form-product" wire:model.defer="product_id" class="{{ $inputClass }}">
                            <option value="">-- عمومی --</option>
                            @foreach($products as $id => $title)
                                <option value="{{ $id }}">{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="form-category" class="{{ $labelClass }}">اختصاص به دسته‌بندی (Override)</label>
                        <select id="form-category" wire:model.defer="category_id" class="{{ $inputClass }}">
                            <option value="">-- عمومی --</option>
                            @foreach($categories as $id => $catName)
                                <option value="{{ $id }}">{{ $catName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-3 pt-2">
                        <input type="checkbox" wire:model.defer="is_active" class="{{ $checkboxClass }}">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">فرم فعال باشد</span>
                    </label>
                </div>
            </div>
            @include('market::livewire.admin.partials.checkout-field-toolbox')
        @endif
    </div>

    {{-- Main content for form builder --}}
    <div class="col-span-12 lg:col-span-8 space-y-6">
        @if($activeFormId)
            <div class="{{ $cardClass }} animate-in fade-in">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">طراحی فرم: {{ $name }}</h3>
                    <div class="flex items-center gap-2">
                        @if($reorderMode)
                            <button wire:click="cancelReorder" class="px-3 py-2 text-xs font-bold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">لغو مرتب‌سازی</button>
                            <button wire:click="toggleReorderMode" class="px-3 py-2 text-xs font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">ذخیره ترتیب</button>
                        @else
                            <button wire:click="toggleReorderMode" class="px-3 py-2 text-xs font-bold text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition">مرتب‌سازی</button>
                        @endif
                    </div>
                </div>
                <div class="p-6" x-data="sortableGroup()">
                    @if($reorderMode)
                        {{-- Reorder UI --}}
                        <div class="space-y-4" x-sortable-group>
                            @foreach($groupedSchema as $groupIndex => $groupDetails)
                                <div wire:key="group-{{ $groupDetails['id'] }}" x-sortable-group-item="{{ $groupDetails['id'] }}" class="p-4 border-2 border-dashed dark:border-gray-600 rounded-xl">
                                    <div class="flex justify-between items-center mb-3">
                                        <input type="text" wire:model.lazy="schema.groups.{{ $groupIndex }}.name" class="text-sm font-bold text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1 focus:ring-2 focus:ring-indigo-500 outline-none w-full max-w-xs transition-colors">
                                        <svg x-sortable-group-handle class="w-6 h-6 text-gray-400 cursor-move hover:text-indigo-500 transition-colors ml-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                    </div>
                                    <div class="space-y-2 min-h-[40px] p-2 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-200 dark:border-gray-700" x-sortable-item-group="{{ $groupDetails['id'] }}">
                                        @foreach($groupDetails['fields'] as $field)
                                            <div wire:key="field-{{ $field['id'] }}" x-sortable-item="{{ $field['id'] }}" class="flex items-center gap-3 p-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                                <svg x-sortable-handle class="w-5 h-5 text-gray-400 cursor-move hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $field['label'] }}</span>
                                                <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-600 px-2 py-0.5 rounded-full">{{ $field['id'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Form Builder UI --}}
                        <div class="space-y-6">
                            @foreach($groupedSchema as $groupDetails)
                                <div class="p-5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-800/20">
                                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">{{ $groupDetails['name'] }}</h4>
                                    <div class="space-y-4">
                                        @foreach($groupDetails['fields'] as $field)
                                            @php
                                                $fieldIndex = collect($schema['fields'])->search(fn($f) => $f['id'] === $field['id']);
                                            @endphp
                                            @if($fieldIndex !== false)
                                                @include('market::livewire.admin.partials.checkout-field-card', ['index' => $fieldIndex, 'field' => $field])
                                            @endif
                                        @endforeach

                                        @if(empty($groupDetails['fields']))
                                            <div class="text-center py-4 text-xs text-gray-500 dark:text-gray-400">
                                                هیچ فیلدی در این گروه وجود ندارد.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Floating Save Button --}}
            <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl flex items-center gap-4">
                    <button wire:click="saveForm" wire:loading.attr="disabled" class="px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <span wire:loading.remove>ذخیره تغییرات فرم</span>
                        <span wire:loading>در حال ذخیره...</span>
                    </button>
                    @if($activeFormId !== 'new')
                        <button wire:click="deleteForm" wire:confirm="آیا از حذف این فرم اطمینان دارید؟" class="px-4 py-3 rounded-xl bg-red-100 text-red-600 hover:bg-red-200 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    @endif
                </div>
            </div>
        @else
            <div class="{{ $cardClass }} text-center py-24">
                <p class="text-gray-500 dark:text-gray-400">برای شروع، یک فرم را انتخاب کنید یا یک فرم جدید ایجاد کنید.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('sortableGroup', () => ({
            init() {
                // Remove existing sortable instances if any to prevent memory leaks and duplication
                this.destroySortables();

                this.$nextTick(() => {
                    this.initSortableGroups();
                    this.initSortableItems();
                });

                // Re-initialize on Livewire updates
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(({ snapshot, effect }) => {
                        this.$nextTick(() => {
                            this.destroySortables();
                            this.initSortableGroups();
                            this.initSortableItems();
                        });
                    });
                });
            },
            sortableInstances: [],
            destroySortables() {
                this.sortableInstances.forEach(instance => instance.destroy());
                this.sortableInstances = [];
            },
            initSortableGroups() {
                const groupEl = this.$el.querySelector('[x-sortable-group]');
                if (groupEl && typeof window.Sortable !== 'undefined') {
                    const instance = new window.Sortable(groupEl, {
                        animation: 150,
                        handle: '[x-sortable-group-handle]',
                        ghostClass: 'opacity-50',
                        onEnd: (e) => {
                            const newOrder = Array.from(e.target.children).map(child => child.getAttribute('x-sortable-group-item'));
                            @this.call('reorderGroups', newOrder);
                        }
                    });
                    this.sortableInstances.push(instance);
                }
            },
            initSortableItems() {
                this.$el.querySelectorAll('[x-sortable-item-group]').forEach(el => {
                    const groupName = el.getAttribute('x-sortable-item-group');
                    if (el && typeof window.Sortable !== 'undefined') {
                        const instance = new window.Sortable(el, {
                            animation: 150,
                            group: 'shared-fields',
                            handle: '[x-sortable-handle]',
                            ghostClass: 'opacity-50',
                            onEnd: (e) => {
                                // Only trigger reorderFields if it was moved within the SAME group
                                if(e.from === e.to) {
                                     const newOrder = Array.from(e.target.children).map(child => child.getAttribute('x-sortable-item'));
                                     @this.call('reorderFields', groupName, newOrder);
                                }
                            },
                            onAdd: (e) => {
                                // Triggered when a field is moved from another group to this group
                                const fieldId = e.item.getAttribute('x-sortable-item');
                                const newGroupId = e.to.getAttribute('x-sortable-item-group');
                                const newIndex = e.newIndex;
                                @this.call('changeFieldGroup', fieldId, newGroupId, newIndex);
                            }
                        });
                        this.sortableInstances.push(instance);
                    }
                });
            }
        }));
    });
</script>
@endpush
