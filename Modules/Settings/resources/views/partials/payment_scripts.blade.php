<script>
        document.addEventListener('DOMContentLoaded', function () {
            // متغیرهای جاافتاده از نسخه قبل
            const banks = @json($banks);
            const isAccountingActive = @json($isAccountingActive);

            function generateUniqueId(prefix) {
                return prefix + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            }

            // Shared Tailwind class strings used inside dynamically-built HTML below.
            // Defined once here as JS constants (instead of inlining Blade echo tags for
            // the input/label classes throughout this script) so Blade's compiler never
            // has to parse raw JS containing back-to-back closing braces (e.g. from JS
            // object literals), which previously caused "Newline or semicolon expected"
            // Blade compile errors.
            const inputClass = @json($inputClass);
            const labelClass = @json($labelClass);
            // Build selectClass from inputClass (adds appearance-none and cursor-pointer)
            const selectClass = inputClass + ' appearance-none cursor-pointer';

            // Wrap all non-multiple select elements to apply custom chevron styling
            function wrapSelects() {
                document.querySelectorAll('#main-settings-form select:not([multiple])').forEach(select => {
                    if (select.closest('.select-wrapper')) return; // already wrapped

                    const wrapper = document.createElement('div');
                    wrapper.className = 'select-wrapper relative w-full';

                    // Move responsive width classes from select to wrapper
                    ['md:w-1/2', 'md:w-1/3', 'md:w-2/3'].forEach(cls => {
                        if (select.classList.contains(cls)) {
                            wrapper.classList.add(cls);
                            select.classList.remove(cls);
                        }
                    });

                    select.parentNode.insertBefore(wrapper, select);
                    wrapper.appendChild(select);

                    // Add only the missing styling classes (safe – does not remove existing ones)
                    select.classList.add('appearance-none', 'cursor-pointer', '!pl-10');

                    // Enforce bg-gray-50 in light mode — some browsers override it on <select> elements
                    // We use a CSS custom property approach via inline style so dark: variant still wins
                    if (!select.classList.contains('dark:bg-gray-900')) {
                        select.classList.add(
                            'bg-gray-50', 'dark:bg-gray-900',
                            'border-gray-200', 'dark:border-gray-700',
                            'text-gray-900', 'dark:text-gray-100',
                            'rounded-xl', 'text-sm', 'py-2.5', 'px-4',
                            'focus:border-indigo-500', 'focus:ring-2', 'focus:ring-indigo-500/20',
                            'transition-all', 'w-full'
                        );
                    }

                    const iconContainer = document.createElement('div');
                    iconContainer.className = 'pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 dark:text-gray-400';
                    iconContainer.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>';
                    wrapper.appendChild(iconContainer);
                });
            }
            wrapSelects();

            function getCurrencyText() {
                const currencySelect = document.getElementById('payment_currency');
                return currencySelect && currencySelect.value === 'rial' ? 'ریال' : 'تومان';
            }

            // ===================== POS Devices =====================
            const posDevicesContainer = document.getElementById('pos-devices-container');
            const addPosDeviceBtn = document.getElementById('add-pos-device-btn');
            let posDevices = @json($pos_devices);
            if (!Array.isArray(posDevices)) posDevices = [];

            posDevices.forEach(d => {
                if (!d.id) d.id = generateUniqueId('pos');
            });

            function createPosDeviceItem(device = {}, index) {
                const deviceId = `pos_device_${index}`;
                const item = document.createElement('div');
                item.className = 'p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800/50 relative';
                item.setAttribute('data-index', index);

                let bankOptions = '<option value="">انتخاب بانک</option>';
                banks.forEach(bank => {
                    bankOptions += `<option value="${bank.id}" ${device.bank_id == bank.id ? 'selected' : ''}>${bank.name}</option>`;
                });

                let disabledAttr = !isAccountingActive ? 'disabled' : '';
                let warningText = !isAccountingActive ? '<p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>' : '';

                item.innerHTML = `
                <input type="hidden" name="pos_devices[${index}][id]" value="${device.id}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="${deviceId}_name" class="${labelClass}">نام دستگاه</label>
                        <input type="text" data-field="name" id="${deviceId}_name" name="pos_devices[${index}][name]" value="${device.name || ''}" class="${inputClass}" placeholder="مثال: کارتخوان سامان">
                    </div>
                    <div>
                        <label for="${deviceId}_bank_id" class="${labelClass}">بانک متصل</label>
                        <select data-field="bank_id" id="${deviceId}_bank_id" name="pos_devices[${index}][bank_id]" class="${inputClass}" ${disabledAttr}>${bankOptions}</select>
                        ${warningText}
                    </div>
                    <div>
                        <label for="${deviceId}_account_number" class="${labelClass}">شماره حساب</label>
                        <input type="text" data-field="account_number" id="${deviceId}_account_number" name="pos_devices[${index}][account_number]" value="${device.account_number || ''}" class="${inputClass} dir-ltr text-left" placeholder="123-456-789">
                    </div>
                </div>
                <button type="button" class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/40 flex items-center justify-center remove-pos-device-btn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                </button>
            `;
                posDevicesContainer.appendChild(item);
            }

            function renderPosDevices() {
                posDevicesContainer.innerHTML = '';
                posDevices.forEach((device, index) => createPosDeviceItem(device, index));
                wrapSelects();
            }

            posDevicesContainer.addEventListener('input', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    posDevices[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });
            posDevicesContainer.addEventListener('change', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    posDevices[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });

            addPosDeviceBtn.addEventListener('click', () => {
                posDevices.push({id: generateUniqueId('pos')});
                renderPosDevices();
            });

            posDevicesContainer.addEventListener('click', (e) => {
                if (e.target.closest('.remove-pos-device-btn')) {
                    const item = e.target.closest('[data-index]');
                    const index = parseInt(item.getAttribute('data-index'));
                    posDevices.splice(index, 1);
                    renderPosDevices();
                }
            });

            // ===================== Bank Accounts =====================
            const bankAccountsContainer = document.getElementById('bank-accounts-container');
            const addBankAccountBtn = document.getElementById('add-bank-account-btn');
            let bankAccounts = @json($bank_transfer_accounts);
            if (!Array.isArray(bankAccounts)) bankAccounts = [];

            bankAccounts.forEach(a => {
                if (!a.id) a.id = generateUniqueId('bank');
            });

            function createBankAccountItem(account = {}, index) {
                const accountId = `bank_account_${index}`;
                const item = document.createElement('div');
                item.className = 'p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800/50 relative';
                item.setAttribute('data-index', index);

                let bankOptions = '<option value="">انتخاب بانک</option>';
                banks.forEach(bank => {
                    bankOptions += `<option value="${bank.id}" ${account.bank_id == bank.id ? 'selected' : ''}>${bank.name}</option>`;
                });

                let disabledAttr = !isAccountingActive ? 'disabled' : '';
                let warningText = !isAccountingActive ? '<p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>' : '';

                item.innerHTML = `
                <input type="hidden" name="bank_transfer_accounts[${index}][id]" value="${account.id}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="${accountId}_bank_id" class="${labelClass}">نام بانک</label>
                        <select data-field="bank_id" id="${accountId}_bank_id" name="bank_transfer_accounts[${index}][bank_id]" class="${inputClass}" ${disabledAttr}>${bankOptions}</select>
                        ${warningText}
                    </div>
                    <div>
                        <label for="${accountId}_account_number" class="${labelClass}">شماره حساب</label>
                        <input type="text" data-field="account_number" id="${accountId}_account_number" name="bank_transfer_accounts[${index}][account_number]" value="${account.account_number || ''}" class="${inputClass} dir-ltr text-left" placeholder="123-456-789">
                    </div>
                    <div>
                        <label for="${accountId}_card_number" class="${labelClass}">شماره کارت</label>
                        <input type="text" data-field="card_number" id="${accountId}_card_number" name="bank_transfer_accounts[${index}][card_number]" value="${account.card_number || ''}" class="${inputClass} dir-ltr text-left" placeholder="6037-xxxx-xxxx-xxxx">
                    </div>
                    <div>
                        <label for="${accountId}_iban" class="${labelClass}">شماره شبا</label>
                        <input type="text" data-field="iban" id="${accountId}_iban" name="bank_transfer_accounts[${index}][iban]" value="${account.iban || ''}" class="${inputClass} dir-ltr text-left" placeholder="IR...">
                    </div>
                </div>
                <button type="button" class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/40 flex items-center justify-center remove-bank-account-btn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                </button>
            `;
                bankAccountsContainer.appendChild(item);
            }

            function renderBankAccounts() {
                bankAccountsContainer.innerHTML = '';
                bankAccounts.forEach((account, index) => createBankAccountItem(account, index));
                wrapSelects();
            }

            bankAccountsContainer.addEventListener('input', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    bankAccounts[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });
            bankAccountsContainer.addEventListener('change', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    bankAccounts[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });

            addBankAccountBtn.addEventListener('click', () => {
                bankAccounts.push({id: generateUniqueId('bank')});
                renderBankAccounts();
            });

            bankAccountsContainer.addEventListener('click', (e) => {
                if (e.target.closest('.remove-bank-account-btn')) {
                    const item = e.target.closest('[data-index]');
                    const index = parseInt(item.getAttribute('data-index'));
                    bankAccounts.splice(index, 1);
                    renderBankAccounts();
                }
            });

            // ===================== Installment Types =====================
            const availableServices = @json($availableServices ?? []);
            const installmentContainer = document.getElementById('installment-types-container');
            const addInstallmentBtn = document.getElementById('add-installment-btn');

            let installmentTypes = @json(isset($settings['installment_types']) ? (is_string($settings['installment_types']) ? json_decode($settings['installment_types'], true) : $settings['installment_types']) : []);
            if (!Array.isArray(installmentTypes)) installmentTypes = [];

            installmentTypes.forEach(t => {
                if (!t.id) t.id = generateUniqueId('inst');
                if (!t.brand_configs || typeof t.brand_configs !== 'object') t.brand_configs = {};
                if (!Array.isArray(t.price_tiers)) t.price_tiers = [];
                if (!t.default_tier_config || typeof t.default_tier_config !== 'object') {
                    t.default_tier_config = { max_months: '', payment_stages: '', down_payments_map: {}, fees_map: {} };
                }
                if (!t.default_tier_config.down_payments_map) t.default_tier_config.down_payments_map = {};
                if (!t.default_tier_config.fees_map) t.default_tier_config.fees_map = {};

                // Ensure tiers have IDs
                t.price_tiers.forEach(pt => {
                    if (!pt.id) pt.id = generateUniqueId('tier');
                });

                // Ensure brand configs have tiers object
                Object.keys(t.brand_configs).forEach(key => {
                    if (!t.brand_configs[key].tiers) {
                        t.brand_configs[key].tiers = {};
                    }
                });
            });

            function getBrandKey(serviceId, tabTitle, sectionTitle, brandName) {
                const clean = (val) => String(val || '').trim().replace(/\s+/g, ' ');
                return `${clean(serviceId)}__${clean(tabTitle)}__${clean(sectionTitle)}__${clean(brandName)}`;
            }

            function isBrandSelected(item, key) {
                return item.brand_configs && item.brand_configs[key] && item.brand_configs[key].active;
            }

            function syncBrandConfig(card, index) {
                const configs = {};
                card.querySelectorAll('[data-brand-block]').forEach(block => {
                    const key = block.getAttribute('data-brand-block');
                    const activeToggle = block.querySelector('[data-brand-active]');
                    if (!activeToggle || !activeToggle.checked) return;

                    const existingConfig = installmentTypes[index].brand_configs[key] || {};
                    configs[key] = {
                        active: true,
                        tiers: existingConfig.tiers || {}
                    };
                });
                installmentTypes[index].brand_configs = configs;
            }

            function updateTabCounts(card) {
                card.querySelectorAll('[data-tab-block]').forEach(block => {
                    const total = block.querySelectorAll('[data-brand-active]').length;
                    const checked = block.querySelectorAll('[data-brand-active]:checked').length;
                    const countEl = block.querySelector('[data-tab-count]');
                    if (countEl) countEl.textContent = `${checked} از ${total}`;
                    const tabToggle = block.querySelector('[data-tab-toggle]');
                    if (tabToggle) tabToggle.checked = total > 0 && checked === total;
                });
                const totalSelected = card.querySelectorAll('[data-brand-active]:checked').length;
                const totalEl = card.querySelector('[data-total-brands]');
                if (totalEl) totalEl.textContent = `${totalSelected} برند انتخاب شده`;
                
                const summaryBrands = card.querySelector('[data-summary-brands]');
                if (summaryBrands) {
                    summaryBrands.textContent = `${totalSelected} برند فعال`;
                }
            }

            // =============== Plan Level Price Tiers (Min/Max Only) ===============
            function createPriceTierHtml(tier, tierIndex, planIndex) {
                const div = document.createElement('div');
                div.className = 'p-3 border border-blue-100 dark:border-blue-800/30 rounded-lg bg-white dark:bg-gray-800/50 relative';
                div.setAttribute('data-tier-index', tierIndex);

                const currencyText = getCurrencyText();
                const minRaw = parseInt(String(tier.min_price || '').replace(/[^\d]/g, '')) || 0;
                const maxRaw = parseInt(String(tier.max_price || '').replace(/[^\d]/g, '')) || 0;
                const minVal = minRaw ? minRaw.toLocaleString('en-US') : '';
                const maxVal = maxRaw ? maxRaw.toLocaleString('en-US') : '';

                if (!tier.id) tier.id = generateUniqueId('tier');

                div.innerHTML = `
                <input type="hidden" name="installment_types[${planIndex}][price_tiers][${tierIndex}][id]" value="${tier.id}">
                <div class="grid grid-cols-2 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">حداقل مبلغ (<span class="currency-label">${currencyText}</span>)</label>
                        <input type="text" inputmode="numeric" data-tier-field="min_price" data-amount-format name="installment_types[${planIndex}][price_tiers][${tierIndex}][min_price]" value="${minVal}" class="${inputClass} dir-ltr text-left text-xs" placeholder="50,000">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">حداکثر مبلغ (<span class="currency-label">${currencyText}</span>)</label>
                        <input type="text" inputmode="numeric" data-tier-field="max_price" data-amount-format name="installment_types[${planIndex}][price_tiers][${tierIndex}][max_price]" value="${maxVal}" class="${inputClass} dir-ltr text-left text-xs" placeholder="520,000">
                    </div>
                </div>
                <button type="button" class="remove-price-tier-btn absolute -top-2 -right-2 w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-200 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
                return div;
            }

            function renderPriceTiers(card, planIndex) {
                const tiersContainer = card.querySelector('.price-tiers-container');
                if (!tiersContainer) return;
                tiersContainer.innerHTML = '';
                const tiers = installmentTypes[planIndex].price_tiers || [];
                tiers.forEach((tier, tIdx) => {
                    const tierItem = createPriceTierHtml(tier, tIdx, planIndex);
                    tiersContainer.appendChild(tierItem);
                });
            }

            // =============== Plan-Level Default Tier Config (Fallback) ===============
            function renderDefaultTierConfig(card, planIndex) {
                let container = card.querySelector('.default-tier-config-container');
                if (!container) return;

                const plan = installmentTypes[planIndex];
                if (!plan.default_tier_config) {
                    plan.default_tier_config = { max_months: '', payment_stages: '', down_payments_map: {}, fees_map: {} };
                }
                const cfg = plan.default_tier_config;
                if (!cfg.down_payments_map) cfg.down_payments_map = {};
                if (!cfg.fees_map) cfg.fees_map = {};

                container.innerHTML = `
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">حداکثر ماه‌ها</label>
                        <input type="number" min="1" data-default-tier-field="max_months" data-default-tier-trigger name="installment_types[${planIndex}][default_tier_config][max_months]" value="${cfg.max_months || ''}" class="${inputClass} dir-ltr text-left text-xs py-2" placeholder="12">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">مراحل پرداخت</label>
                        <input type="number" min="1" data-default-tier-field="payment_stages" data-default-tier-trigger name="installment_types[${planIndex}][default_tier_config][payment_stages]" value="${cfg.payment_stages || ''}" class="${inputClass} dir-ltr text-left text-xs py-2" placeholder="3">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 mb-1">کارمزد سالانه (٪)</label>
                        <input type="number" min="0" max="100" data-default-tier-field="annual_fee_percent" name="installment_types[${planIndex}][default_tier_config][annual_fee_percent]" value="${cfg.annual_fee_percent || ''}" class="${inputClass} dir-ltr text-left text-xs py-2" placeholder="20">
                    </div>
                </div>
                <div class="default-down-payments-grid grid grid-cols-4 gap-2 mt-2"></div>
                <div class="default-fees-grid grid grid-cols-4 gap-2 mt-2"></div>
            `;
                renderDefaultTierDynamicMaps(container, planIndex);
            }

            function renderDefaultTierDynamicMaps(container, planIndex) {
                const downPayContainer = container.querySelector('.default-down-payments-grid');
                const feesContainer = container.querySelector('.default-fees-grid');
                const maxMonthsInput = container.querySelector('[data-default-tier-field="max_months"]');
                const stagesInput = container.querySelector('[data-default-tier-field="payment_stages"]');
                const maxMonths = parseInt(maxMonthsInput.value) || 0;
                const stages = parseInt(stagesInput.value) || 1;

                downPayContainer.innerHTML = '';
                feesContainer.innerHTML = '';

                if (maxMonths === 0 || stages === 0) return;

                const cfg = installmentTypes[planIndex].default_tier_config;
                if (!cfg.down_payments_map) cfg.down_payments_map = {};
                if (!cfg.fees_map) cfg.fees_map = {};

                let dpHtml = `
                <div class="col-span-4 flex items-center justify-between text-[10px] font-bold text-blue-600 dark:text-blue-400 mb-1 mt-1">
                    <span>پیش‌پرداخت پیش‌فرض برای هر بازه (٪):</span>
                    <button type="button" data-action="copy-first-dp" class="text-[9px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold transition-colors">کپی مقدار اول به همه</button>
                </div>`;
                
                let feeHtml = `
                <div class="col-span-4 flex items-center justify-between text-[10px] font-bold text-amber-600 dark:text-amber-400 mb-1 mt-1">
                    <span>کارمزد پیش‌فرض برای هر بازه (٪):</span>
                    <button type="button" data-action="copy-first-fee" class="text-[9px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold transition-colors">کپی مقدار اول به همه</button>
                </div>`;

                for (let m = stages; m <= maxMonths; m += stages) {
                    const dpVal = cfg.down_payments_map[m] || cfg.down_payments_map[String(m)] || '';
                    const feeVal = cfg.fees_map[m] || cfg.fees_map[String(m)] || '';

                    dpHtml += `
                    <div class="col-span-1">
                        <label class="block text-[9px] text-gray-500 mb-0.5">${m} ماه</label>
                        <input type="number" min="0" max="100" data-default-tier-field="down_payments_map" data-default-tier-month="${m}" name="installment_types[${planIndex}][default_tier_config][down_payments_map][${m}]" value="${dpVal}" class="${inputClass} dir-ltr text-left text-xs py-1.5" placeholder="20">
                    </div>
                `;
                    feeHtml += `
                    <div class="col-span-1">
                        <label class="block text-[9px] text-gray-500 mb-0.5">${m} ماه</label>
                        <input type="number" min="0" max="100" data-default-tier-field="fees_map" data-default-tier-month="${m}" name="installment_types[${planIndex}][default_tier_config][fees_map][${m}]" value="${feeVal}" class="${inputClass} dir-ltr text-left text-xs py-1.5" placeholder="2">
                    </div>
                `;
                }
                downPayContainer.innerHTML = dpHtml;
                feesContainer.innerHTML = feeHtml;
            }

            // =============== Brand Level Tier Settings (Months, Stages, DP, Fee) ===============
            function renderBrandTierSettings(block, planIndex, brandKey) {
                let container = block.querySelector('.brand-tier-settings-container');
                const plan = installmentTypes[planIndex];
                const brandCfg = plan.brand_configs[brandKey] || { tiers: {} };

                const hasOverride = Object.keys(brandCfg.tiers).some(tierId => {
                    const t = brandCfg.tiers[tierId];
                    return t.max_months || t.payment_stages || t.annual_fee_percent;
                });

                if (!container) {
                    container = document.createElement('div');
                    container.className = 'brand-tier-settings-container w-full mt-3 p-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 rounded-lg space-y-3';
                    
                    container.innerHTML = `
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100 dark:border-gray-700/50">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                <input type="checkbox" data-brand-override class="brand-override-checkbox w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer" ${hasOverride ? 'checked' : ''}>
                                <span>شخصی‌سازی تنظیمات کارمزد و اقساط برای این برند</span>
                            </label>
                        </div>
                        <div class="brand-tier-inputs-wrapper space-y-3" style="display: ${hasOverride ? 'block' : 'none'};">
                        </div>
                    `;
                    block.appendChild(container);

                    const overrideCheckbox = container.querySelector('[data-brand-override]');
                    overrideCheckbox.addEventListener('change', (e) => {
                        const wrapper = container.querySelector('.brand-tier-inputs-wrapper');
                        wrapper.style.display = e.target.checked ? 'block' : 'none';
                        
                        wrapper.querySelectorAll('input, select, textarea').forEach(input => {
                            input.disabled = !e.target.checked;
                        });

                        if (!e.target.checked) {
                            brandCfg.tiers = {};
                        } else {
                            renderBrandTierFields(container, planIndex, brandKey);
                        }
                    });
                }

                if (hasOverride) {
                    renderBrandTierFields(container, planIndex, brandKey);
                }
            }

            function renderBrandTierFields(container, planIndex, brandKey) {
                const wrapper = container.querySelector('.brand-tier-inputs-wrapper');
                if (!wrapper) return;
                
                const plan = installmentTypes[planIndex];
                const tiers = plan.price_tiers || [];
                const brandCfg = plan.brand_configs[brandKey] || { tiers: {} };
                
                wrapper.innerHTML = '';
                if (tiers.length === 0) {
                    wrapper.innerHTML = '<p class="text-xs text-rose-500 font-bold p-2">ابتدا بازه‌های قیمتی را برای طرح تعریف کنید.</p>';
                    return;
                }

                const currencyText = getCurrencyText();

                tiers.forEach(tier => {
                    const tierId = tier.id;
                    if (!brandCfg.tiers[tierId]) {
                        brandCfg.tiers[tierId] = { max_months: '', payment_stages: '', down_payments_map: {}, fees_map: {} };
                    }
                    const tierCfg = brandCfg.tiers[tierId];

                    const tierDiv = document.createElement('div');
                    tierDiv.className = 'p-3 rounded-lg bg-white dark:bg-gray-800 border border-indigo-100 dark:border-indigo-800/30 mb-2 last:mb-0';
                    tierDiv.setAttribute('data-brand-tier-id', tierId);

                    const minVal = Number(tier.min_price || 0).toLocaleString('en-US');
                    const maxVal = Number(tier.max_price || 0).toLocaleString('en-US');

                    tierDiv.innerHTML = `
                    <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300">بازه قیمتی:</span>
                        <span class="text-xs font-bold dir-ltr text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-md">
                            ${minVal} تا ${maxVal} <span class="currency-label text-[10px] font-normal mr-1">${currencyText}</span>
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">حداکثر ماه‌ها</label>
                            <input type="number" min="1" data-brand-tier-field="max_months" data-tier-trigger name="installment_types[${planIndex}][brand_configs][${brandKey}][tiers][${tierId}][max_months]" value="${tierCfg.max_months || ''}" class="${inputClass} dir-ltr text-left text-xs py-2" placeholder="12">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">مراحل پرداخت</label>
                            <input type="number" min="1" data-brand-tier-field="payment_stages" data-tier-trigger name="installment_types[${planIndex}][brand_configs][${brandKey}][tiers][${tierId}][payment_stages]" value="${tierCfg.payment_stages || ''}" class="${inputClass} dir-ltr text-left text-xs py-2" placeholder="3">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 mb-1">کارمزد سالانه (٪)</label>
                            <input type="number" min="0" max="100" data-brand-tier-field="annual_fee_percent" name="installment_types[${planIndex}][brand_configs][${brandKey}][tiers][${tierId}][annual_fee_percent]" value="${tierCfg.annual_fee_percent || ''}" class="${inputClass} dir-ltr text-left text-xs py-2" placeholder="20">
                        </div>
                    </div>
                    <div class="brand-down-payments-grid grid grid-cols-4 gap-2 mt-2" data-tier-id="${tierId}"></div>
                    <div class="brand-fees-grid grid grid-cols-4 gap-2 mt-2" data-tier-id="${tierId}"></div>
                `;
                    wrapper.appendChild(tierDiv);
                    renderBrandTierDynamicMaps(tierDiv, planIndex, brandKey, tierId);
                });
                wrapSelects();
            }

            function renderBrandTierDynamicMaps(tierDiv, planIndex, brandKey, tierId) {
                const downPayContainer = tierDiv.querySelector('.brand-down-payments-grid');
                const feesContainer = tierDiv.querySelector('.brand-fees-grid');
                const maxMonthsInput = tierDiv.querySelector('[data-brand-tier-field="max_months"]');
                const stagesInput = tierDiv.querySelector('[data-brand-tier-field="payment_stages"]');
                const maxMonths = parseInt(maxMonthsInput.value) || 0;
                const stages = parseInt(stagesInput.value) || 1;

                downPayContainer.innerHTML = '';
                feesContainer.innerHTML = '';

                if (maxMonths === 0 || stages === 0) return;

                const tierCfg = installmentTypes[planIndex].brand_configs[brandKey].tiers[tierId];
                if (!tierCfg.down_payments_map) tierCfg.down_payments_map = {};
                if (!tierCfg.fees_map) tierCfg.fees_map = {};

                let dpHtml = `
                <div class="col-span-4 flex items-center justify-between text-[10px] font-bold text-blue-600 dark:text-blue-400 mb-1 mt-1">
                    <span>پیش‌پرداخت برای هر بازه (٪):</span>
                    <button type="button" data-action="copy-brand-first-dp" class="text-[9px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold transition-colors">کپی مقدار اول در همه</button>
                </div>`;
                
                let feeHtml = `
                <div class="col-span-4 flex items-center justify-between text-[10px] font-bold text-amber-600 dark:text-amber-400 mb-1 mt-1">
                    <span>کارمزد برای هر بازه (٪):</span>
                    <button type="button" data-action="copy-brand-first-fee" class="text-[9px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold transition-colors">کپی مقدار اول در همه</button>
                </div>`;

                for (let m = stages; m <= maxMonths; m += stages) {
                    const dpVal = tierCfg.down_payments_map[m] || tierCfg.down_payments_map[String(m)] || '';
                    const feeVal = tierCfg.fees_map[m] || tierCfg.fees_map[String(m)] || '';

                    dpHtml += `
                    <div class="col-span-1">
                        <label class="block text-[9px] text-gray-500 mb-0.5">${m} ماه</label>
                        <input type="number" min="0" max="100" data-brand-tier-field="down_payments_map" data-tier-month="${m}" name="installment_types[${planIndex}][brand_configs][${brandKey}][tiers][${tierId}][down_payments_map][${m}]" value="${dpVal}" class="${inputClass} dir-ltr text-left text-xs py-1.5" placeholder="20">
                    </div>
                `;
                    feeHtml += `
                    <div class="col-span-1">
                        <label class="block text-[9px] text-gray-500 mb-0.5">${m} ماه</label>
                        <input type="number" min="0" max="100" data-brand-tier-field="fees_map" data-tier-month="${m}" name="installment_types[${planIndex}][brand_configs][${brandKey}][tiers][${tierId}][fees_map][${m}]" value="${feeVal}" class="${inputClass} dir-ltr text-left text-xs py-1.5" placeholder="2">
                    </div>
                `;
                }
                downPayContainer.innerHTML = dpHtml;
                feesContainer.innerHTML = feeHtml;
            }

            function createInstallmentItem(item = {}, index) {
                const div = document.createElement('div');
                div.className = 'installment-card collapsed bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm transition-all duration-300 relative mb-4';
                div.setAttribute('data-index', index);

                const isSpecialChecked = item.is_special ? 'checked' : '';
                const specialDisplay = item.is_special ? 'grid' : 'none';

                let servicesHtml = '';
                if (availableServices.length === 0) {
                    servicesHtml = `<div class="p-6 text-center text-gray-400">سرویسی یافت نشد.</div>`;
                } else {
                    const tabColorClasses = [
                        { bg: 'bg-purple-50 dark:bg-purple-900/20', text: 'text-purple-700 dark:text-purple-300', count: 'text-purple-600', border: 'border border-purple-100 dark:border-purple-800/40' },
                        { bg: 'bg-teal-50 dark:bg-teal-900/20', text: 'text-teal-700 dark:text-teal-300', count: 'text-teal-600', border: 'border border-teal-100 dark:border-teal-800/40' },
                        { bg: 'bg-amber-50 dark:bg-amber-900/20', text: 'text-amber-700 dark:text-amber-300', count: 'text-amber-600', border: 'border border-amber-100 dark:border-amber-800/40' },
                        { bg: 'bg-blue-50 dark:bg-blue-900/20', text: 'text-blue-700 dark:text-blue-300', count: 'text-blue-600', border: 'border border-blue-100 dark:border-blue-800/40' },
                        { bg: 'bg-rose-50 dark:bg-rose-900/20', text: 'text-rose-700 dark:text-rose-300', count: 'text-rose-600', border: 'border border-rose-100 dark:border-rose-800/40' },
                    ];
                    let tabColorIndex = 0;

                    availableServices.forEach(service => {
                        (service.tabs || []).forEach(tab => {
                            const color = tabColorClasses[tabColorIndex % tabColorClasses.length];
                            tabColorIndex++;

                            let totalBrands = 0;
                            let checkedBrands = 0;
                            let sectionsHtml = '';

                            (tab.sections || []).forEach(section => {
                                const validBrands = (section.brands || []).filter(b => b.name && b.name.trim() !== '' && b.is_installment === true);
                                if (validBrands.length === 0) return;

                                let brandsHtml = '';
                                validBrands.forEach(brand => {
                                    const key = getBrandKey(service.id, tab.title, section.title, brand.name);
                                    const isChecked = isBrandSelected(item, key);
                                    if (isChecked) checkedBrands++;
                                    totalBrands++;

                                    const basePriceNum = brand.price ? Number(brand.price) : 0;
                                    const basePrice = basePriceNum ? basePriceNum.toLocaleString('fa-IR') : '—';

                                    brandsHtml += `
                                <div data-brand-block="${key}" class="border-t border-gray-100 dark:border-gray-700/50">
                                    <label class="relative flex flex-wrap items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors group">
                                        <input type="checkbox" data-brand-active name="installment_types[${index}][brand_configs][${key}][active]" value="1" class="peer sr-only" ${isChecked ? 'checked' : ''}>
                                        <span class="w-5 h-5 rounded-md border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center transition-all peer-checked:bg-indigo-600 peer-checked:border-indigo-600 group-hover:border-indigo-400">
                                            <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span class="text-xs flex-1 font-semibold text-gray-700 dark:text-gray-300 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 transition-colors">${brand.name}</span>
                                        <span class="text-xs font-bold text-gray-500 dir-ltr tabular-nums ml-2" data-base-price="${basePriceNum}">${basePrice}</span>
                                    </label>
                                </div>`;
                                });

                                const allChecked = validBrands.every(b => isBrandSelected(item, getBrandKey(service.id, tab.title, section.title, b.name)));
                                sectionsHtml += `
                            <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden mb-2 last:mb-0">
                                <div class="px-3 py-2 bg-gray-50/70 dark:bg-gray-900/20 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-800 dark:text-gray-100">${section.title}</span>
                                    </div>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" data-section-toggle class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer" ${allChecked ? 'checked' : ''}>
                                        <span class="text-[11px] text-gray-500">همه</span>
                                    </label>
                                </div>
                                ${brandsHtml}
                            </div>`;
                            });

                            if (!sectionsHtml) return;

                            servicesHtml += `
                        <div data-tab-block class="rounded-xl overflow-hidden mb-3 last:mb-0 ${color.border}">
                            <div class="${color.bg} px-3 py-2.5 flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" data-tab-toggle class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer">
                                    <span class="text-xs font-bold ${color.text}">${tab.title}</span>
                                    <span class="text-[11px] text-gray-500">${service.name}</span>
                                </label>
                                <span data-tab-count class="text-[11px] font-bold ${color.count}">${checkedBrands} از ${totalBrands}</span>
                            </div>
                            <div class="divide-y divide-gray-50 dark:divide-gray-700/30 p-2 space-y-2">${sectionsHtml}</div>
                        </div>`;
                        });
                    });
                }

                div.innerHTML = `
            <input type="hidden" name="installment_types[${index}][id]" value="${item.id}">
            
            <!-- Accordion Header -->
            <div class="installment-header p-5 flex items-center justify-between cursor-pointer select-none border-b border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-3">
                     <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm">${index + 1}</span>
                     <div>
                         <h4 class="text-sm font-bold text-gray-900 dark:text-white" data-title-display>${item.title || 'طرح بدون عنوان'}</h4>
                         <div class="flex flex-wrap gap-1.5 mt-1">
                             <span class="text-[10px] px-2 py-0.5 rounded-full ${item.is_special ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'} font-medium" data-summary-special>
                                 ${item.is_special ? 'مناسبتی' : 'عمومی'}
                             </span>
                             <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 font-medium" data-summary-tiers>
                                 ${item.price_tiers?.length || 0} بازه قیمتی
                             </span>
                             <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 font-medium" data-summary-brands>
                                 ${Object.keys(item.brand_configs || {}).filter(k => item.brand_configs[k].active).length} برند فعال
                             </span>
                         </div>
                     </div>
                </div>
                <div class="flex items-center gap-2">
                     <button type="button" class="remove-inst-btn w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 flex items-center justify-center transition-colors mr-2" title="حذف طرح">
                         <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                     </button>
                     <span class="toggle-icon text-gray-400 dark:text-gray-500 transition-transform duration-300">
                         <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                     </span>
                </div>
            </div>

            <!-- Accordion Body -->
            <div class="installment-body p-5 space-y-6">
                <!-- گام ۱: مشخصات طرح -->
                <div class="border-b border-gray-100 dark:border-gray-700/50 pb-5">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 mb-3 block">گام ۱: مشخصات کلی و محدودیت‌ها</span>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="${labelClass}">عنوان طرح</label>
                            <input type="text" data-field="title" name="installment_types[${index}][title]" value="${item.title || ''}" class="${inputClass}" placeholder="مثال: طرح لبخند">
                        </div>
                    </div>
                    
                    <div class="mt-4 p-4 bg-purple-50/60 dark:bg-purple-900/10 rounded-xl border border-purple-100 dark:border-purple-800/30">
                        <label class="flex items-center gap-2 cursor-pointer mb-3">
                             <input type="checkbox" data-field="is_special" name="installment_types[${index}][is_special]" class="special-toggle w-4 h-4 rounded border-purple-300 dark:border-purple-800 text-purple-600 focus:ring-purple-500 dark:bg-gray-900 cursor-pointer" value="1" ${isSpecialChecked}>
                            <span class="text-sm font-bold text-purple-900 dark:text-purple-300">طرح مناسبتی / دارای محدودیت</span>
                        </label>
                        <div class="special-fields grid-cols-1 md:grid-cols-3 gap-4" style="display:${specialDisplay};">
                            <div>
                                <label class="${labelClass}">محدودیت تعداد دندان</label>
                                <input type="number" min="0" data-field="teeth_limit" name="installment_types[${index}][teeth_limit]" value="${item.teeth_limit || ''}" class="${inputClass} dir-ltr text-left" placeholder="5">
                            </div>
                            <div>
                                <label class="${labelClass}">تاریخ شروع</label>
                                <input type="text" data-jdp-only-date readonly placeholder="YYYY/MM/DD" data-field="start_month" name="installment_types[${index}][start_month]" value="${item.start_month || ''}" class="${inputClass} dir-ltr text-left">
                            </div>
                            <div>
                                <label class="${labelClass}">تاریخ پایان</label>
                                <input type="text" data-jdp-only-date readonly placeholder="YYYY/MM/DD" data-field="end_month" name="installment_types[${index}][end_month]" value="${item.end_month || ''}" class="${inputClass} dir-ltr text-left">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- گام ۲: بازه‌های قیمتی و پیش‌فرض‌ها -->
                <div class="border-b border-gray-100 dark:border-gray-700/50 pb-5">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 mb-3 block">گام ۲: بازه‌های قیمتی و تنظیمات پیش‌فرض</span>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- بازه‌های قیمتی -->
                        <div class="p-4 bg-blue-50/60 dark:bg-blue-900/10 rounded-xl border border-blue-100 dark:border-blue-800/30">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-bold text-blue-900 dark:text-blue-300">بازه‌های قیمتی طرح (منطق پلکانی)</span>
                                <button type="button" class="add-price-tier-btn text-xs px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    افزودن بازه
                                </button>
                            </div>
                            <div class="price-tiers-container space-y-3"></div>
                        </div>

                        <!-- تنظیمات پیش‌فرض -->
                        <div class="p-4 bg-emerald-50/60 dark:bg-emerald-900/10 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    تنظیمات پیش‌فرض طرح
                                </span>
                            </div>
                            <p class="text-[10px] text-emerald-700/80 dark:text-emerald-400/80 mb-3 leading-relaxed">
                                زمانی استفاده می‌شود که برند فعال باشد ولی فیلدهای بازه‌اش خالی باشد یا مبلغ کل از حداکثر بازه‌ها بیشتر باشد.
                            </p>
                            <div class="default-tier-config-container"></div>
                        </div>
                    </div>
                </div>

                <!-- گام ۳: برندهای مشمول -->
                <div>
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 mb-3 block">گام ۳: برندهای مشمول طرح</span>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">برندهای مشمول این طرح</span>
                        <span data-total-brands class="text-xs px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 font-bold">${Object.keys(item.brand_configs || {}).filter(k => item.brand_configs[k].active).length} انتخاب شده</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mb-3">با فعال کردن هر برند، تنظیمات اختصاصی بازه‌ها برای آن ظاهر می‌شود.</p>
                    ${servicesHtml}
                </div>
            </div>`;

                installmentContainer.appendChild(div);
                renderPriceTiers(div, index);
                renderDefaultTierConfig(div, index);

                // Render brand tier settings for active brands
                div.querySelectorAll('[data-brand-block]').forEach(block => {
                    const key = block.getAttribute('data-brand-block');
                    if (installmentTypes[index].brand_configs[key] && installmentTypes[index].brand_configs[key].active) {
                        renderBrandTierSettings(block, index, key);
                    }
                });
            }
            function renderInstallmentTypes() {
                installmentContainer.innerHTML = '';
                installmentTypes.forEach((type, index) => createInstallmentItem(type, index));
                wrapSelects();

                if (window.jalaliDatepicker) {
                    jalaliDatepicker.startWatch({
                        selector: '[data-jdp-only-date]',
                        minDate: 'attr'
                    });
                }
            }

            addInstallmentBtn.addEventListener('click', () => {
                installmentTypes.push({id: generateUniqueId('inst'), brand_configs: {}, price_tiers: [], default_tier_config: { max_months: '', payment_stages: '', down_payments_map: {}, fees_map: {} }});
                renderInstallmentTypes();
            });

            installmentContainer.addEventListener('click', (e) => {
                const header = e.target.closest('.installment-header');
                if (header) {
                    if (e.target.closest('.remove-inst-btn')) return;

                    const card = header.closest('.installment-card');
                    const isCollapsed = card.classList.contains('collapsed');

                    // Collapse all
                    installmentContainer.querySelectorAll('.installment-card').forEach(c => {
                        c.classList.add('collapsed');
                    });

                    // Toggle current
                    if (isCollapsed) {
                        card.classList.remove('collapsed');
                    }
                    return;
                }

                if (e.target.closest('.remove-inst-btn')) {
                    const item = e.target.closest('[data-index]');
                    installmentTypes.splice(parseInt(item.getAttribute('data-index')), 1);
                    renderInstallmentTypes();
                    return;
                }

                if (e.target.closest('.add-price-tier-btn')) {
                    const card = e.target.closest('[data-index]');
                    const index = parseInt(card.getAttribute('data-index'));
                    if (!Array.isArray(installmentTypes[index].price_tiers)) {
                        installmentTypes[index].price_tiers = [];
                    }
                    installmentTypes[index].price_tiers.push({ id: generateUniqueId('tier') });
                    renderPriceTiers(card, index);

                    // Re-render brand tier settings to show the new tier
                    card.querySelectorAll('[data-brand-block]').forEach(block => {
                        const key = block.getAttribute('data-brand-block');
                        if (installmentTypes[index].brand_configs[key] && installmentTypes[index].brand_configs[key].active) {
                            renderBrandTierSettings(block, index, key);
                        }
                    });

                    const summaryTiers = card.querySelector('[data-summary-tiers]');
                    if (summaryTiers) {
                        summaryTiers.textContent = `${installmentTypes[index].price_tiers.length} بازه قیمتی`;
                    }
                    return;
                }

                if (e.target.closest('.remove-price-tier-btn')) {
                    const tierItem = e.target.closest('[data-tier-index]');
                    const card = e.target.closest('[data-index]');
                    const planIndex = parseInt(card.getAttribute('data-index'));
                    const tierIndex = parseInt(tierItem.getAttribute('data-tier-index'));

                    const removedTierId = installmentTypes[planIndex].price_tiers[tierIndex].id;
                    installmentTypes[planIndex].price_tiers.splice(tierIndex, 1);
                    renderPriceTiers(card, planIndex);

                    // Remove the tier from brand configs and re-render
                    Object.keys(installmentTypes[planIndex].brand_configs).forEach(key => {
                        if (installmentTypes[planIndex].brand_configs[key].tiers) {
                            delete installmentTypes[planIndex].brand_configs[key].tiers[removedTierId];
                        }
                    });

                    card.querySelectorAll('[data-brand-block]').forEach(block => {
                        const key = block.getAttribute('data-brand-block');
                        if (installmentTypes[planIndex].brand_configs[key] && installmentTypes[planIndex].brand_configs[key].active) {
                            renderBrandTierSettings(block, planIndex, key);
                        }
                    });

                    const summaryTiers = card.querySelector('[data-summary-tiers]');
                    if (summaryTiers) {
                        summaryTiers.textContent = `${installmentTypes[planIndex].price_tiers.length} بازه قیمتی`;
                    }
                    return;
                }

                // Quick fill event handlers
                const quickFillDp = e.target.closest('[data-action="copy-first-dp"]');
                if (quickFillDp) {
                    const card = quickFillDp.closest('[data-index]');
                    const planIndex = parseInt(card.getAttribute('data-index'));
                    const cfg = installmentTypes[planIndex].default_tier_config;
                    
                    const inputs = card.querySelectorAll('[data-default-tier-field="down_payments_map"]');
                    if (inputs.length > 0) {
                        const firstVal = inputs[0].value;
                        inputs.forEach(input => {
                            input.value = firstVal;
                            const month = input.getAttribute('data-default-tier-month');
                            cfg.down_payments_map[month] = firstVal;
                        });
                    }
                    return;
                }

                const quickFillFee = e.target.closest('[data-action="copy-first-fee"]');
                if (quickFillFee) {
                    const card = quickFillFee.closest('[data-index]');
                    const planIndex = parseInt(card.getAttribute('data-index'));
                    const cfg = installmentTypes[planIndex].default_tier_config;
                    
                    const inputs = card.querySelectorAll('[data-default-tier-field="fees_map"]');
                    if (inputs.length > 0) {
                        const firstVal = inputs[0].value;
                        inputs.forEach(input => {
                            input.value = firstVal;
                            const month = input.getAttribute('data-default-tier-month');
                            cfg.fees_map[month] = firstVal;
                        });
                    }
                    return;
                }

                const quickFillBrandDp = e.target.closest('[data-action="copy-brand-first-dp"]');
                if (quickFillBrandDp) {
                    const block = quickFillBrandDp.closest('[data-brand-block]');
                    const brandKey = block.getAttribute('data-brand-block');
                    const tierDiv = quickFillBrandDp.closest('[data-brand-tier-id]');
                    const tierId = tierDiv.getAttribute('data-brand-tier-id');
                    const card = quickFillBrandDp.closest('[data-index]');
                    const planIndex = parseInt(card.getAttribute('data-index'));
                    const tierCfg = installmentTypes[planIndex].brand_configs[brandKey].tiers[tierId];

                    const inputs = tierDiv.querySelectorAll('[data-brand-tier-field="down_payments_map"]');
                    if (inputs.length > 0) {
                        const firstVal = inputs[0].value;
                        inputs.forEach(input => {
                            input.value = firstVal;
                            const month = input.getAttribute('data-tier-month');
                            tierCfg.down_payments_map[month] = firstVal;
                        });
                    }
                    return;
                }

                const quickFillBrandFee = e.target.closest('[data-action="copy-brand-first-fee"]');
                if (quickFillBrandFee) {
                    const block = quickFillBrandFee.closest('[data-brand-block]');
                    const brandKey = block.getAttribute('data-brand-block');
                    const tierDiv = quickFillBrandFee.closest('[data-brand-tier-id]');
                    const tierId = tierDiv.getAttribute('data-brand-tier-id');
                    const card = quickFillBrandFee.closest('[data-index]');
                    const planIndex = parseInt(card.getAttribute('data-index'));
                    const tierCfg = installmentTypes[planIndex].brand_configs[brandKey].tiers[tierId];

                    const inputs = tierDiv.querySelectorAll('[data-brand-tier-field="fees_map"]');
                    if (inputs.length > 0) {
                        const firstVal = inputs[0].value;
                        inputs.forEach(input => {
                            input.value = firstVal;
                            const month = input.getAttribute('data-tier-month');
                            tierCfg.fees_map[month] = firstVal;
                        });
                    }
                    return;
                }
            });

            installmentContainer.addEventListener('change', (e) => {
                const card = e.target.closest('[data-index]');
                if (!card) return;
                const index = parseInt(card.getAttribute('data-index'));

                if (e.target.classList.contains('special-toggle')) {
                    card.querySelector('.special-fields').style.display = e.target.checked ? 'grid' : 'none';
                    installmentTypes[index].is_special = e.target.checked;
                    
                    const summarySpecial = card.querySelector('[data-summary-special]');
                    if (summarySpecial) {
                        summarySpecial.textContent = e.target.checked ? 'مناسبتی' : 'عمومی';
                        summarySpecial.className = `text-[10px] px-2 py-0.5 rounded-full ${e.target.checked ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'} font-medium`;
                    }
                    return;
                }

                if (e.target.hasAttribute('data-tab-toggle')) {
                    const tabBlock = e.target.closest('[data-tab-block]');
                    tabBlock.querySelectorAll('[data-brand-active]').forEach(cb => cb.checked = e.target.checked);
                    tabBlock.querySelectorAll('[data-section-toggle]').forEach(cb => cb.checked = e.target.checked);
                    syncBrandConfig(card, index);
                    updateTabCounts(card);

                    // Render/remove brand settings on tab toggle
                    tabBlock.querySelectorAll('[data-brand-block]').forEach(block => {
                        const key = block.getAttribute('data-brand-block');
                        const isActive = installmentTypes[index].brand_configs[key]?.active;
                        if (isActive) {
                            renderBrandTierSettings(block, index, key);
                        } else {
                            const settingsContainer = block.querySelector('.brand-tier-settings-container');
                            if (settingsContainer) settingsContainer.remove();
                        }
                    });
                    return;
                }

                if (e.target.hasAttribute('data-section-toggle')) {
                    const sectionBlock = e.target.closest('.border.border-gray-100, .border.border-gray-700');
                    sectionBlock.querySelectorAll('[data-brand-active]').forEach(cb => cb.checked = e.target.checked);
                    syncBrandConfig(card, index);
                    updateTabCounts(card);

                    sectionBlock.querySelectorAll('[data-brand-block]').forEach(block => {
                        const key = block.getAttribute('data-brand-block');
                        const isActive = installmentTypes[index].brand_configs[key]?.active;
                        if (isActive) {
                            renderBrandTierSettings(block, index, key);
                        } else {
                            const settingsContainer = block.querySelector('.brand-tier-settings-container');
                            if (settingsContainer) settingsContainer.remove();
                        }
                    });
                    return;
                }

                if (e.target.hasAttribute('data-brand-active')) {
                    syncBrandConfig(card, index);
                    updateTabCounts(card);

                    const key = e.target.closest('[data-brand-block]').getAttribute('data-brand-block');
                    const block = e.target.closest('[data-brand-block]');
                    if (e.target.checked) {
                        renderBrandTierSettings(block, index, key);
                    } else {
                        const settingsContainer = block.querySelector('.brand-tier-settings-container');
                        if (settingsContainer) settingsContainer.remove();
                    }
                    return;
                }
            });

            installmentContainer.addEventListener('input', (e) => {
                const card = e.target.closest('[data-index]');
                if (!card) return;
                const index = parseInt(card.getAttribute('data-index'));

                if (e.target.getAttribute('data-field') === 'title') {
                    const titleDisplay = card.querySelector('[data-title-display]');
                    if (titleDisplay) {
                        titleDisplay.textContent = e.target.value || 'طرح بدون عنوان';
                    }
                }

                // Handle Plan-level Price Tier inputs (min/max amount)
                if (e.target.hasAttribute('data-amount-format')) {
                    let rawValue = e.target.value.replace(/[^\d]/g, '');
                    if (rawValue) {
                        e.target.value = Number(rawValue).toLocaleString('en-US');
                    } else {
                        e.target.value = '';
                    }

                    const tierItem = e.target.closest('[data-tier-index]');
                    const tierIndex = parseInt(tierItem.getAttribute('data-tier-index'));
                    const field = e.target.getAttribute('data-tier-field');

                    if (!installmentTypes[index].price_tiers) installmentTypes[index].price_tiers = [];
                    if (!installmentTypes[index].price_tiers[tierIndex]) installmentTypes[index].price_tiers[tierIndex] = {};

                    installmentTypes[index].price_tiers[tierIndex][field] = rawValue;

                    // Update readonly labels in brand configs
                    card.querySelectorAll('[data-brand-block]').forEach(block => {
                        const key = block.getAttribute('data-brand-block');
                        if (installmentTypes[index].brand_configs[key] && installmentTypes[index].brand_configs[key].active) {
                            renderBrandTierSettings(block, index, key);
                        }
                    });
                    return;
                }

                // Handle Plan-level Default Tier Config inputs (fallback)
                if (e.target.hasAttribute('data-default-tier-field')) {
                    const field = e.target.getAttribute('data-default-tier-field');
                    if (!installmentTypes[index].default_tier_config) {
                        installmentTypes[index].default_tier_config = { max_months: '', payment_stages: '', down_payments_map: {}, fees_map: {} };
                    }
                    const cfg = installmentTypes[index].default_tier_config;

                    if (field === 'down_payments_map' || field === 'fees_map') {
                        const month = e.target.getAttribute('data-default-tier-month');
                        if (!cfg[field]) cfg[field] = {};
                        cfg[field][month] = e.target.value;
                    } else {
                        cfg[field] = e.target.value;
                        if (e.target.hasAttribute('data-default-tier-trigger')) {
                            const container = card.querySelector('.default-tier-config-container');
                            renderDefaultTierDynamicMaps(container, index);
                        }
                    }
                    return;
                }

                // Handle Brand-level Tier Settings inputs
                if (e.target.hasAttribute('data-brand-tier-field')) {
                    const block = e.target.closest('[data-brand-block]');
                    const key = block.getAttribute('data-brand-block');
                    const tierDiv = e.target.closest('[data-brand-tier-id]');
                    const tierId = tierDiv.getAttribute('data-brand-tier-id');
                    const field = e.target.getAttribute('data-brand-tier-field');

                    if (!installmentTypes[index].brand_configs[key]) {
                        installmentTypes[index].brand_configs[key] = { active: true, tiers: {} };
                    }
                    if (!installmentTypes[index].brand_configs[key].tiers[tierId]) {
                        installmentTypes[index].brand_configs[key].tiers[tierId] = {};
                    }
                    const tierCfg = installmentTypes[index].brand_configs[key].tiers[tierId];

                    if (field === 'down_payments_map' || field === 'fees_map') {
                        const month = e.target.getAttribute('data-tier-month');
                        if (!tierCfg[field]) tierCfg[field] = {};
                        tierCfg[field][month] = e.target.value;
                    } else {
                        tierCfg[field] = e.target.value;
                        if (e.target.hasAttribute('data-tier-trigger')) {
                            renderBrandTierDynamicMaps(tierDiv, index, key, tierId);
                        }
                    }
                    return;
                }

                if (e.target.hasAttribute('data-field')) {
                    const fieldName = e.target.getAttribute('data-field');
                    installmentTypes[index][fieldName] = e.target.value;
                }
            });

            // Currency Change Listener
            const currencySelect = document.getElementById('payment_currency');
            if (currencySelect) {
                currencySelect.addEventListener('change', function() {
                    const currencyText = this.value === 'rial' ? 'ریال' : 'تومان';
                    document.querySelectorAll('.currency-label').forEach(el => el.textContent = currencyText);
                    renderInstallmentTypes(); // Re-render to update labels
                });
            }

            // Form Submit Listener
            document.getElementById('main-settings-form').addEventListener('submit', function() {
                document.querySelectorAll('[data-amount-format]').forEach(input => {
                    input.value = input.value.replace(/[^\d]/g, '');
                });
            });

            // ===================== Installment Rounding Live Preview =====================
            (function initRoundingPreview() {
                const sampleAmount = 345200;
                const factorInput = document.getElementById('installment_rounding_factor');
                const outputEl = document.getElementById('rounding-preview-output');
                const inputEl = document.getElementById('rounding-preview-input');
                const modeRadios = document.querySelectorAll('.rounding-mode-radio');
                if (!factorInput || !outputEl || !modeRadios.length) return;

                function getSelectedMode() {
                    const checked = document.querySelector('.rounding-mode-radio:checked');
                    return checked ? checked.value : 'none';
                }

                function roundPreview(amount, mode, factor) {
                    if (!mode || mode === 'none' || !factor || factor <= 0) return Math.round(amount);
                    if (mode === 'up') return Math.ceil(amount / factor) * factor;
                    if (mode === 'down') return Math.floor(amount / factor) * factor;
                    return Math.round(amount);
                }

                function updatePreview() {
                    const mode = getSelectedMode();
                    const factor = parseInt(factorInput.value, 10) || 0;
                    const result = roundPreview(sampleAmount, mode, factor);

                    inputEl.textContent = sampleAmount.toLocaleString('en-US');
                    outputEl.textContent = result.toLocaleString('en-US');

                    outputEl.classList.remove('text-emerald-600', 'text-red-600', 'text-gray-700', 'dark:text-gray-200');
                    if (mode === 'up') {
                        outputEl.classList.add('text-emerald-600');
                    } else if (mode === 'down') {
                        outputEl.classList.add('text-red-600');
                    } else {
                        outputEl.classList.add('text-gray-700', 'dark:text-gray-200');
                    }
                }

                modeRadios.forEach(r => r.addEventListener('change', updatePreview));
                factorInput.addEventListener('input', updatePreview);
                updatePreview();
            })();

            // اجرای رندرهای اولیه
            renderPosDevices();
            renderBankAccounts();
            renderInstallmentTypes();
        });
    </script>