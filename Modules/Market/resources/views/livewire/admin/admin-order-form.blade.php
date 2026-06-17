<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">فرم‌های تسویه حساب</h3>
            </div>
            <div class="p-4 space-y-2">
                @foreach($forms as $form)
                    <div wire:click="selectForm({{ $form->id }})"
                         class="p-3 rounded-lg cursor-pointer {{ $selectedFormId == $form->id ? 'bg-indigo-100 dark:bg-indigo-900/50' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <p class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $form->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $form->key }}</p>
                    </div>
                @endforeach
                <button wire:click="selectForm(null)" class="w-full mt-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    ایجاد فرم جدید
                </button>
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                    {{ $editingForm->exists ? 'ویرایش فرم: ' . $editingForm->name : 'ایجاد فرم جدید' }}
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">نام فرم</label>
                    <input type="text" wire:model.defer="editingForm.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    @error('editingForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">کلید (Key)</label>
                    <input type="text" wire:model.defer="editingForm.key" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    @error('editingForm.key') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" wire:model.defer="editingForm.is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                    <label class="ml-2 block text-sm text-gray-900 dark:text-gray-300">فعال</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">اختصاص به محصول خاص</label>
                    <select wire:model.defer="editingForm.product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <option value="">-- بدون اختصاص --</option>
                        @foreach($products as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">اختصاص به دسته‌بندی خاص</label>
                    <select wire:model.defer="editingForm.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <option value="">-- بدون اختصاص --</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- A simple textarea for schema editing for now --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">اسکیما (JSON)</label>
                    <textarea wire:model.defer="editingForm.schema" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 font-mono"></textarea>
                    @error('editingForm.schema') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end">
                    <button wire:click="save" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        ذخیره فرم
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
