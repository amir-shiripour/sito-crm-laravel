<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">مدیریت سوال و جواب دستیار هوشمند</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">سوالات و پاسخ‌های آماده، محصولات پیشنهادی و الگوهای گفت‌وگو را مدیریت کنید.</p>
        </div>
        <button
            wire:click="openModal()"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-sm transition-all transform hover:-translate-y-0.5 duration-200"
        >
            افزودن سوال و جواب جدید
        </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full md:w-96 relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="جستجو در سوالات و کلمات کلیدی..."
                class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            />
            <span class="absolute left-3 top-2.5 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">دسته‌بندی:</span>
            <select
                wire:model.live="category"
                class="border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-xs py-1.5 px-3 focus:ring-2 focus:ring-indigo-500 outline-none"
            >
                <option value="all">همه دسته‌ها</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Questions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900 text-xs font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <th class="p-4">سوال اصلی</th>
                        <th class="p-4">کلمات کلیدی</th>
                        <th class="p-4">نوع پاسخ</th>
                        <th class="p-4">دسته بندی</th>
                        <th class="p-4">اولویت</th>
                        <th class="p-4">وضعیت</th>
                        <th class="p-4 text-left">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    @forelse($questions as $question)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                            <td class="p-4 font-medium text-gray-900 dark:text-white">
                                {{ $question->question_text }}
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($question->keywords ?? [] as $kw)
                                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-300">
                                            {{ $kw }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-4">
                                @if($question->defaultAnswer()?->answer_type === 'product_list')
                                    <span class="inline-flex items-center gap-1 text-xs text-orange-600 bg-orange-50 dark:bg-orange-950/30 px-2 py-0.5 rounded-full font-medium">
                                        لیست محصولات
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-2 py-0.5 rounded-full font-medium">
                                        متنی
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $question->category }}
                            </td>
                            <td class="p-4 text-xs font-semibold">
                                {{ $question->priority }}
                            </td>
                            <td class="p-4">
                                <button
                                    wire:click="toggleStatus({{ $question->id }})"
                                    class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-semibold transition-colors {{ $question->is_active ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-950/30' : 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-950/30' }}"
                                >
                                    {{ $question->is_active ? 'فعال' : 'غیرفعال' }}
                                </button>
                            </td>
                            <td class="p-4 text-left space-x-reverse space-x-2">
                                <button
                                    wire:click="openModal({{ $question->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 text-xs font-semibold"
                                >
                                    ویرایش
                                </button>
                                <button
                                    onclick="confirm('آیا مطمئن هستید؟') || event.stopImmediatePropagation()"
                                    wire:click="delete({{ $question->id }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 text-xs font-semibold"
                                >
                                    حذف
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                هیچ سوال و جوابی یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($questions->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $questions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 backdrop-blur-sm p-4">
            <div class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $editingQuestionId ? 'ویرایش سوال و جواب' : 'ثبت سوال و جواب جدید' }}
                    </h2>
                    <button wire:click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">سوال کاربر</label>
                        <input
                            type="text"
                            wire:model="question_text"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="مثال: قیمت محصولات چقدر است؟"
                        />
                        @error('question_text') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">کلمات کلیدی (با کاما جدا کنید)</label>
                            <input
                                type="text"
                                wire:model="keywords"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="قیمت, خرید, هزینه"
                            />
                            @error('keywords') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">دسته‌بندی</label>
                            <input
                                type="text"
                                wire:model="category_field"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="general"
                            />
                            @error('category_field') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">اولویت تطبیق (بزرگتر = اولویت بیشتر)</label>
                            <input
                                type="number"
                                wire:model="priority"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            @error('priority') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center h-full pt-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:-translate-x-full rtl:peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="ms-3 text-xs font-semibold text-gray-700 dark:text-gray-300">سوال فعال باشد</span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">پاسخ سیستم</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">متن پاسخ</label>
                                <textarea
                                    wire:model="answer_text"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="پاسخی که کاربر دریافت می‌کند..."
                                ></textarea>
                                @error('answer_text') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">نوع پاسخ</label>
                                    <select
                                        wire:model.live="answer_type"
                                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="text">فقط متنی</option>
                                        <option value="product_list">لیست محصولات</option>
                                    </select>
                                    @error('answer_type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>

                                @if($answer_type === 'product_list')
                                    <div class="flex items-center h-full pt-6">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model="show_add_to_cart" class="sr-only peer">
                                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:-translate-x-full rtl:peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            <span class="ms-3 text-xs font-semibold text-gray-700 dark:text-gray-300">امکان افزودن مستقیم به سبد خرید</span>
                                        </label>
                                    </div>
                                @endif
                            </div>

                            @if($answer_type === 'product_list')
                                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-100 dark:border-gray-700 space-y-2">
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">انتخاب محصولات ضمیمه</label>
                                    @if(empty($this->products))
                                        <p class="text-xs text-red-500">ماژول فروشگاه غیرفعال است یا هیچ محصولی یافت نشد.</p>
                                    @else
                                        <div class="max-h-40 overflow-y-auto space-y-1.5 pr-2">
                                            @foreach($this->products as $p)
                                                <label class="flex items-center gap-2 text-xs cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 p-1 rounded">
                                                    <input
                                                        type="checkbox"
                                                        value="{{ $p['id'] }}"
                                                        wire:model="selected_product_ids"
                                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    />
                                                    <span class="text-gray-800 dark:text-gray-200">{{ $p['title'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('selected_product_ids') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700 pt-4 mt-6">
                        <button
                            type="button"
                            wire:click="closeModal()"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
                        >
                            انصراف
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-sm"
                        >
                            ذخیره تغییرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
