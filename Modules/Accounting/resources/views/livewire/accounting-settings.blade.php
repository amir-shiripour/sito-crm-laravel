<div x-data="{ activeTab: 'defaults' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">تنظیمات حسابداری</h1>

        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Tabs Navigation --}}
            <aside class="lg:w-1/4">
                <nav class="space-y-1">
                    <button @click="activeTab = 'defaults'" :class="activeTab === 'defaults' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800/50 dark:hover:text-gray-300'" class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg">
                        <span class="truncate">سرفصل‌های پیش‌فرض</span>
                    </button>
                    <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800/50 dark:hover:text-gray-300'" class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg">
                        <span class="truncate">تنظیمات عمومی</span>
                    </button>
                    <button @click="activeTab = 'integration'" :class="activeTab === 'integration' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800/50 dark:hover:text-gray-300'" class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg">
                        <span class="truncate">یکپارچه‌سازی ماژول‌ها</span>
                    </button>
                </nav>
            </aside>

            {{-- Tabs Content --}}
            <div class="lg:w-3/4">
                <form wire:submit.prevent="saveSettings">
                    {{-- Default Accounts Settings --}}
                    <div x-show="activeTab === 'defaults'" class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">اتوماسیون ثبت اسناد</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">با تنظیم این موارد، اسناد حسابداری مربوط به فاکتورها و چک‌ها به صورت خودکار در سرفصل صحیح ثبت می‌شوند.</p>

                            <div class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
                                <div>
                                    <label for="default_sales_income_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">درآمد فروش/خدمات</label>
                                    <select wire:model="default_sales_income_category_id" id="default_sales_income_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($incomeCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="default_receivables_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">حساب‌های دریافتنی (مشتریان)</label>
                                    <select wire:model="default_receivables_category_id" id="default_receivables_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($assetCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="default_sales_tax_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">مالیات بر ارزش افزوده فروش</label>
                                    <select wire:model="default_sales_tax_category_id" id="default_sales_tax_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($liabilityCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="default_cheques_receivable_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">اسناد دریافتنی (صندوق چک)</label>
                                    <select wire:model="default_cheques_receivable_category_id" id="default_cheques_receivable_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($assetCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="default_cash_fund_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">صندوق پیش‌فرض</label>
                                    <select wire:model="default_cash_fund_id" id="default_cash_fund_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($fundAccounts->where('type', 'cash') as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="default_bank_fund_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">بانک پیش‌فرض</label>
                                    <select wire:model="default_bank_fund_id" id="default_bank_fund_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($fundAccounts->where('type', 'bank') as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 text-right sm:px-6 rounded-b-3xl">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                ذخیره تنظیمات
                            </button>
                        </div>
                    </div>

                    {{-- General Settings --}}
                    <div x-show="activeTab === 'general'" class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">تنظیمات عمومی و فروشنده</h2>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">واحد پول پایه</label>
                                    <input type="text" wire:model="currency" {{ !$isSuperAdmin ? 'disabled' : '' }} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-600 {{ !$isSuperAdmin ? 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-800' : '' }}">
                                    @if(!$isSuperAdmin)
                                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-medium">تغییر واحد مالی فقط توسط سوپر ادمین امکان‌پذیر است.</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">نام فروشنده/مجموعه</label>
                                    <input type="text" wire:model="seller_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">شماره اقتصادی</label>
                                    <input type="text" wire:model="economic_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">آدرس مجموعه</label>
                                    <input type="text" wire:model="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                                </div>
                                <div class="col-span-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" wire:model="allow_negative_balance" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="mr-2 text-sm text-gray-700 dark:text-gray-300">اجازه منفی شدن موجودی حساب‌های خزانه‌داری</span>
                                    </label>
                                </div>
                                <div class="col-span-2">
                                    <label class="flex items-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" wire:model="check_cheque_due_dates" class="peer sr-only">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-indigo-600 transition-colors"></div>
                                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                        </div>
                                        <div class="mr-4">
                                            <span class="block text-sm font-bold text-gray-900 dark:text-white">فعال‌سازی بررسی تاریخ سررسید چک‌ها (کرون جاب)</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">با فعال‌سازی این گزینه، فاکتورهای پرداخت شده با چک تا زمان وصول چک در حالت انتظار باقی می‌مانند و دکمه وصول چک نیز فقط پس از رسیدن تاریخ سررسید نمایش داده می‌شود.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 text-right sm:px-6 rounded-b-3xl">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                ذخیره تنظیمات
                            </button>
                        </div>
                    </div>

                    {{-- Cross-Module Integration Settings --}}
                    <div x-show="activeTab === 'integration'" class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">یکپارچه‌سازی ماژول‌های CRM</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">تنظیم فعال‌سازی صدور خودکار سند حسابداری هنگام دریافت تراکنش از سایر ماژول‌ها.</p>
                            <div class="mt-6 space-y-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="auto_sync_services" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300">ثبت خودکار سند هنگام پرداخت فاکتور در ماژول سرویس‌ها و خدمات (Services)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="auto_sync_booking" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300">ثبت خودکار سند هنگام رزرو و پرداخت آنلاین نوبت‌دهی (Booking)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="auto_sync_market" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300">ثبت خودکار سند هنگام خرید و پرداخت سفارشات فروشگاه (Market)</span>
                                </label>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 text-right sm:px-6 rounded-b-3xl">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                ذخیره تنظیمات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
