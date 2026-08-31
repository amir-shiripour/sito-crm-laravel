@extends('layouts.user')

@section('content')
    @includeIf('partials.jalali-date-picker')
    <style>
        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }
        .tooth-selected {
            fill: #3b82f6 !important;
            stroke: #2563eb !important;
            stroke-width: 2.5px !important;
            filter: drop-shadow(0 2px 6px rgba(37, 99, 235, 0.45));
        }
        .dark .tooth-selected {
            fill: #1d4ed8 !important;
            stroke: #3b82f6 !important;
        }
        .tooth-unselected {
            fill: #ffffff !important;
            stroke: #cbd5e1;
        }
        .dark .tooth-unselected {
            fill: #334155 !important;
            stroke: #475569;
        }
        .tooth-unselected:hover {
            fill: #f8fafc !important;
            stroke: #3b82f6;
        }
        .dark .tooth-unselected:hover {
            fill: #1e293b !important;
            stroke: #60a5fa;
        }
    </style>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-2 h-full bg-indigo-500 rounded-r-2xl"></div>
            <div class="pr-3">
                <h1 class="text-xl font-black text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    جزئیات نوبت #{{ $appointment->id }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">مشاهده اطلاعات کامل و سوابق نوبت ثبت شده</p>
            </div>
            <div class="flex items-center gap-3 pr-3 sm:pr-0">
                <a class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 dark:bg-gray-700/70 dark:text-gray-200 dark:hover:bg-gray-600 transition-all duration-200"
                   href="{{ route('user.booking.appointments.index') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                    </svg>
                    بازگشت
                </a>
                @can('booking.appointments.edit')
                    <a class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200"
                       href="{{ route('user.booking.appointments.edit', $appointment) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        ویرایش
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-5 py-4 shadow-sm animate-fade-in">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-500">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(!empty($appointment->cancel_reason) || in_array($appointment->status, [\Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT]))
            <div class="flex items-start gap-4 rounded-2xl border border-rose-200 dark:border-rose-800/80 bg-rose-50/80 dark:bg-rose-900/30 text-rose-900 dark:text-rose-100 p-5 shadow-sm">
                <div class="p-2.5 rounded-xl bg-rose-100 dark:bg-rose-800/60 text-rose-600 dark:text-rose-300 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-rose-700 dark:text-rose-300">علت لغو نوبت:</div>
                    <div class="text-sm font-bold mt-1 text-rose-900 dark:text-rose-100">{{ $appointment->cancel_reason ?: 'دلیلی ثبت نشده است.' }}</div>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 lg:p-8">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">اطلاعات اصلی نوبت</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">سرویس</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ optional($appointment->service)->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ config('booking.labels.provider', 'ارائه‌دهنده') }}</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ optional($appointment->provider)->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">مشتری</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ optional($appointment->client)->full_name ?? '—' }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">وضعیت</div>
                        <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-bold mt-0.5 tracking-wide {{ $statusMeta['class'] }}">
                            {{ $statusMeta['label'] }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تاریخ (شمسی)</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm tracking-wider">{{ $dateJalali }}</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">بازه زمانی</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm tracking-wider" dir="ltr">{{ $startTime }} - {{ $endTime }}</div>
                    </div>
                </div>

                @if($settings->allow_appointment_entry_exit_times)
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">زمان ورود</div>
                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $entryValue }}</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">زمان خروج</div>
                            <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $exitValue }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">یادداشت ثبت شده</span>
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line bg-gray-50 dark:bg-gray-900/50 border-r-4 border-gray-300 dark:border-gray-600 rounded-l-xl p-4 shadow-inner">
                    {{ $appointment->notes ?: 'یادداشتی برای این نوبت ثبت نشده است.' }}
                </div>
            </div>
        </div>

        {{-- بخش مدیریت و اطلاعات پرداخت --}}
        @if($servicePaymentMode !== \Modules\Booking\Entities\BookingService::PAYMENT_MODE_NONE || $payments->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
                 x-data="{
                     showCreateModal: false,
                     showEditModal: false,
                     currencyUnit: '{{ $currencyUnit }}',
                     createType: '{{ array_key_first($availablePaymentMethods) }}',
                     createSubItemLabel: '',
                     editType: 'manual',
                     editSubItemLabel: '',
                     createAmountDisplay: '{{ number_format($suggestedAmount) }}',
                     editAmountDisplay: '',
                     subItems: {{ json_encode($paymentSubItems) }},
                     editData: { id: '', amount: '', type: 'manual', status: 'PAID', gateway_ref: '', notes: '', paid_at_jalali: '{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}' },
                     init() {
                         this.$watch('showCreateModal', val => {
                             document.body.classList.toggle('overflow-hidden', val || this.showEditModal);
                         });
                         this.$watch('showEditModal', val => {
                             document.body.classList.toggle('overflow-hidden', val || this.showCreateModal);
                         });
                     },
                     getPaymentMeta(payment) {
                          let meta = {};
                          if (payment.meta && typeof payment.meta === 'object') {
                              meta = { ...payment.meta };
                          }
                          if (payment.notes) {
                              if (!meta.sub_item) {
                                  const itemMatch = payment.notes.match(/آیتم:\s*([^\-|]+)/);
                                  if (itemMatch) meta.sub_item = itemMatch[1].trim();
                              }
                              if (!meta.payer_name) {
                                  const payerMatch = payment.notes.match(/واریزکننده:\s*([^\-|]+)/);
                                  if (payerMatch) meta.payer_name = payerMatch[1].trim();
                              }
                              if (!meta.tracking_code) {
                                  const trackingMatch = payment.notes.match(/کد پیگیری:\s*([^\-|]+)/);
                                  if (trackingMatch) meta.tracking_code = trackingMatch[1].trim();
                              }
                              if (!meta.payment_date) {
                                  const dateMatch = payment.notes.match(/تاریخ فیش:\s*([^\-|]+)/);
                                  if (dateMatch) meta.payment_date = dateMatch[1].trim();
                              }
                              if (!meta.receipt_url) {
                                  const receiptMatch = payment.notes.match(/رسید:\s*(https?:\/\/[^\s\-|]+)/);
                                  if (receiptMatch) meta.receipt_url = receiptMatch[1].trim();
                              }
                          }
                          return meta;
                      },
                      openEdit(payment) {
                          const rawAmt = Math.round(parseFloat(payment.amount) || 0);
                          const displayAmt = (this.currencyUnit === 'IRT') ? Math.round(rawAmt / 10) : rawAmt;
                          const meta = this.getPaymentMeta(payment);
                          
                          let rawType = (payment.type || '').toLowerCase().trim();
                          let pType = rawType;
                          if (['booking', 'gateway', 'online_gateway'].includes(rawType)) {
                              pType = 'online';
                          } else if (['bank_transfer', 'card', 'sheba', 'bank'].includes(rawType)) {
                              pType = 'transfer';
                          } else if (['cash', 'in_person'].includes(rawType)) {
                              pType = 'cod';
                          } else if (['cheque', 'installments'].includes(rawType)) {
                              pType = 'installment';
                          }
                          if (!pType) {
                              pType = 'manual';
                          }

                          let rawSubItem = meta.sub_item || meta.sub_item_label || '';
                          if (!rawSubItem && payment.notes) {
                              const pipeParts = payment.notes.split('|');
                              if (pipeParts.length > 0 && pipeParts[0].trim()) {
                                  rawSubItem = pipeParts[0].trim();
                              }
                          }

                          let foundCategory = pType;
                          let foundItem = null;

                          // 1. Search in payment's own type category first
                          if (this.subItems[pType] && Array.isArray(this.subItems[pType])) {
                              if (rawSubItem) {
                                  foundItem = this.subItems[pType].find(i => 
                                      String(i.id).toLowerCase() === String(rawSubItem).toLowerCase() || 
                                      String(i.label).toLowerCase() === String(rawSubItem).toLowerCase() || 
                                      String(i.label).includes(String(rawSubItem)) || 
                                      String(rawSubItem).includes(String(i.label)) ||
                                      (i.id && String(rawSubItem).includes(String(i.id)))
                                  );
                              }
                              if (!foundItem && this.subItems[pType].length === 1) {
                                  foundItem = this.subItems[pType][0];
                              }
                          }

                          // 2. If not found in payment's type, search across all categories (transfer, pos, online, installment)
                          if (!foundItem && rawSubItem) {
                              for (const [cat, list] of Object.entries(this.subItems)) {
                                  if (Array.isArray(list)) {
                                      const match = list.find(i => 
                                          String(i.id).toLowerCase() === String(rawSubItem).toLowerCase() || 
                                          String(i.label).toLowerCase() === String(rawSubItem).toLowerCase() || 
                                          String(i.label).includes(String(rawSubItem)) || 
                                          String(rawSubItem).includes(String(i.label)) ||
                                          (i.id && String(rawSubItem).includes(String(i.id)))
                                      );
                                      if (match) {
                                          foundItem = match;
                                          foundCategory = cat;
                                          break;
                                      }
                                  }
                              }
                          }

                          // 3. If still no item matched but rawSubItem exists and is a bank/pos ID
                          if (!foundItem && rawSubItem && String(rawSubItem).startsWith('bank_')) {
                              foundCategory = 'transfer';
                              if (this.subItems['transfer'] && this.subItems['transfer'].length > 0) {
                                  foundItem = this.subItems['transfer'].find(i => String(i.id) === String(rawSubItem)) || this.subItems['transfer'][0];
                              }
                          } else if (!foundItem && rawSubItem && String(rawSubItem).startsWith('pos_')) {
                              foundCategory = 'pos';
                              if (this.subItems['pos'] && this.subItems['pos'].length > 0) {
                                  foundItem = this.subItems['pos'].find(i => String(i.id) === String(rawSubItem)) || this.subItems['pos'][0];
                              }
                          }

                          // 4. If category is transfer/pos and we have items but none matched, pick first
                          if (!foundItem && this.subItems[foundCategory] && Array.isArray(this.subItems[foundCategory]) && this.subItems[foundCategory].length > 0) {
                              if (!rawSubItem) {
                                  foundItem = this.subItems[foundCategory][0];
                              }
                          }

                          let matchedLabel = foundItem ? foundItem.label : (meta.sub_item_label || rawSubItem);

                          this.editType = foundCategory;
                          this.editSubItemLabel = matchedLabel || '';

                          if (matchedLabel) {
                              meta.display_label = matchedLabel;
                          }

                          // Clean notes string so admin notes input stays clean
                          let cleanNotes = payment.notes || '';
                          if (cleanNotes.includes('آیتم:') || cleanNotes.includes('واریزکننده:') || cleanNotes.includes('کد پیگیری:')) {
                              cleanNotes = cleanNotes.replace(/آیتم:\s*[^\|]+(\s*\|\s*)?/g, '')
                                                     .replace(/واریزکننده:\s*[^\|]+(\s*\|\s*)?/g, '')
                                                     .replace(/کد پیگیری:\s*[^\|]+(\s*\|\s*)?/g, '')
                                                     .replace(/تاریخ فیش:\s*[^\|]+(\s*\|\s*)?/g, '')
                                                     .replace(/رسید:\s*https?:\/\/[^\s\|]+(\s*\|\s*)?/g, '')
                                                     .replace(/^[\s\|]+|[\s\|]+$/g, '').trim();
                          } else if (matchedLabel && cleanNotes.startsWith(matchedLabel)) {
                              cleanNotes = cleanNotes.substring(matchedLabel.length).replace(/^[\s\|]+/, '').trim();
                          } else if (rawSubItem && cleanNotes.startsWith(rawSubItem)) {
                              cleanNotes = cleanNotes.substring(rawSubItem.length).replace(/^[\s\|]+/, '').trim();
                          }

                          this.editData = {
                              id: payment.id,
                              amount: displayAmt,
                              type: foundCategory,
                              status: payment.status === 'PENDING' ? 'PAID' : payment.status,
                              gateway_ref: payment.gateway_ref || meta.tracking_code || '',
                              notes: cleanNotes,
                              meta: meta,
                              paid_at_jalali: '{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}'
                          };
                          this.editAmountDisplay = this.formatNumber(displayAmt);
                          this.showEditModal = true;

                          this.$nextTick(() => {
                              this.editType = foundCategory;
                              this.editSubItemLabel = matchedLabel || '';
                          });
                     },
                     formatNumber(val) {
                         if (val === null || val === undefined || val === '') return '';
                         const clean = String(val).replace(/,/g, '').trim();
                         if (clean === '') return '';
                         const num = Math.round(parseFloat(clean));
                         if (isNaN(num)) return '';
                         return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                     },
                     unformatNumber(val) {
                         if (val === null || val === undefined || val === '') return '';
                         const clean = String(val).replace(/,/g, '').trim();
                         if (clean === '') return '';
                         const num = Math.round(parseFloat(clean));
                         return isNaN(num) ? '' : String(num);
                     }
                 }">

                {{-- هدر کارت پرداخت --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                            اطلاعات و وضعیت پرداخت
                        </h2>

                        {{-- Badge سیاست پرداخت سرویس --}}
                        @if($servicePaymentMode === \Modules\Booking\Entities\BookingService::PAYMENT_MODE_REQUIRED)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                سیاست: پرداخت الزامی
                            </span>
                        @elseif($servicePaymentMode === \Modules\Booking\Entities\BookingService::PAYMENT_MODE_OPTIONAL)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                سیاست: پرداخت اختیاری
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                بدون پرداخت
                            </span>
                        @endif
                    </div>

                    @can('booking.payments.manage')
                        @if($servicePaymentMode !== \Modules\Booking\Entities\BookingService::PAYMENT_MODE_NONE)
                            <button type="button"
                                    @click="showCreateModal = true"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                ثبت پرداخت جدید
                            </button>
                        @endif
                    @endcan
                </div>

                {{-- هشدار نیاز به پرداخت --}}
                @if($servicePaymentMode === \Modules\Booking\Entities\BookingService::PAYMENT_MODE_REQUIRED && $appointment->status === \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT && !$hasPaidPayment)
                    <div class="mx-6 mt-5 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span class="text-xs font-bold text-amber-800 dark:text-amber-300">
                            این نوبت به علت عدم پرداخت در وضعیت «در انتظار پرداخت» است. با ثبت پرداخت تاییدشده (پرداخت شده)، وضعیت نوبت خودکار به «تایید شده» تغییر خواهد کرد.
                        </span>
                    </div>
                @endif

                {{-- جدول پرداخت‌ها --}}
                @if($payments->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-600 bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">مبلغ (ریال)</th>
                                <th scope="col" class="px-6 py-4 font-semibold">روش پرداخت</th>
                                <th scope="col" class="px-6 py-4 font-semibold">وضعیت</th>
                                <th scope="col" class="px-6 py-4 font-semibold">کد پیگیری / مرجع</th>
                                <th scope="col" class="px-6 py-4 font-semibold">تاریخ پرداخت</th>
                                <th scope="col" class="px-6 py-4 font-semibold">توضیحات و آیتم</th>
                                @can('booking.payments.manage')
                                    <th scope="col" class="px-6 py-4 font-semibold text-center">عملیات</th>
                                @endcan
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($payments as $payment)
                                @php
                                    $pStatusMeta = $paymentStatusMap[$payment->status] ?? ['label' => $payment->status, 'class' => 'bg-gray-100 text-gray-700'];
                                    $pModeLabel = $availablePaymentMethods[$payment->type] ?? ($payment->type === 'manual' ? 'ثبت دستی (ادمین)' : ($payment->type === 'booking' ? 'درگاه آنلاین' : $payment->type));
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-gray-100">
                                        {{ number_format($payment->amount) }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-700 dark:text-gray-300">
                                        <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $pModeLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-bold tracking-wide {{ $pStatusMeta['class'] }}">
                                            {{ $pStatusMeta['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $payment->gateway_ref ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium" dir="ltr">
                                        {{ $payment->paid_at ? \Morilog\Jalali\Jalalian::fromDateTime($payment->paid_at)->format('Y/m/d H:i') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                        @if(!empty($payment->meta['sub_item']))
                                            <span class="font-bold block text-gray-800 dark:text-gray-200">{{ $payment->meta['sub_item'] }}</span>
                                        @endif
                                        @if(!empty($payment->notes))
                                            <span class="text-gray-500 block truncate">{{ $payment->notes }}</span>
                                        @elseif(empty($payment->meta['sub_item']))
                                            —
                                        @endif
                                    </td>
                                    @can('booking.payments.manage')
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button"
                                                        @click="openEdit({{ json_encode($payment) }})"
                                                        class="p-1.5 rounded-lg text-gray-600 hover:text-indigo-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-gray-700 transition"
                                                        title="ویرایش پرداخت">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>

                                                @if($payment->status !== \Modules\Booking\Entities\BookingPayment::STATUS_CANCELLED)
                                                    <form method="POST" action="{{ route('user.booking.appointments.payments.destroy', [$appointment, $payment]) }}"
                                                          onsubmit="return confirm('آیا از لغو این پرداخت اطمینان دارید؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="p-1.5 rounded-lg text-gray-600 hover:text-rose-600 hover:bg-rose-50 dark:text-gray-400 dark:hover:text-rose-400 dark:hover:bg-rose-900/30 transition"
                                                                title="لغو پرداخت">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        هنوز پرداختی برای این نوبت ثبت نشده است.
                    </div>
                @endif

                {{-- Modal ثبت پرداخت جدید --}}
                <div x-show="showCreateModal"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                     @keydown.escape.window="showCreateModal = false">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 relative animate-in fade-in zoom-in-95 duration-150">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                ثبت پرداخت جدید برای نوبت #{{ $appointment->id }}
                            </h3>
                            <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('user.booking.appointments.payments.store', $appointment) }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">مبلغ (به {{ $currencyLabel }}) *</label>
                                    <input type="text"
                                           x-model="createAmountDisplay"
                                           @input="createAmountDisplay = formatNumber($event.target.value)"
                                           required
                                           placeholder="مثلاً ۱,۰۰۰,۰۰۰"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-emerald-500/20 dark:focus:ring-emerald-500/40 transition-colors">
                                    <input type="hidden" name="amount" :value="unformatNumber(createAmountDisplay)">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">روش پرداخت *</label>
                                    <select name="type" x-model="createType" required
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-emerald-500/20 dark:focus:ring-emerald-500/40 transition-colors">
                                        @foreach($availablePaymentMethods as $key => $label)
                                            <option value="{{ $key }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- سلکت باکس انتخاب زیر-آیتم (حساب/کارتخوان/درگاه/طرح اقساط) --}}
                            <template x-if="subItems[createType] && subItems[createType].length > 0">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">گزینه / حساب پرداخت *</label>
                                    <select x-model="createSubItemLabel"
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-emerald-500/20 dark:focus:ring-emerald-500/40 transition-colors">
                                        <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">-- انتخاب کنید --</option>
                                        <template x-for="item in subItems[createType]" :key="item.id">
                                            <option :value="item.label" x-text="item.label" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"></option>
                                        </template>
                                    </select>
                                    <input type="hidden" name="sub_item_label" :value="createSubItemLabel">
                                </div>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت پرداخت *</label>
                                    <select name="status" required
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-emerald-500/20 dark:focus:ring-emerald-500/40 transition-colors">
                                        <option value="PAID" selected class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">پرداخت شده (تایید شده)</option>
                                        <option value="PENDING" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">در انتظار پرداخت</option>
                                        <option value="FAILED" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">ناموفق</option>
                                        <option value="REFUNDED" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">استرداد شده</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ پرداخت *</label>
                                    <input type="text"
                                           name="paid_at_jalali"
                                           data-jdp
                                           data-jdp-only-date
                                           value="{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}"
                                           required
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-emerald-500/20 dark:focus:ring-emerald-500/40 transition-colors">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">کد پیگیری / شناسه مرجع</label>
                                    <input type="text" name="gateway_ref" placeholder="مثلاً: 12345678"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-emerald-500/20 dark:focus:ring-emerald-500/40 transition-colors">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات و یادداشت ادمین</label>
                                <textarea name="notes" rows="2" placeholder="یادداشت اختیاری..."
                                          class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-emerald-500/20 dark:focus:ring-emerald-500/40 transition-colors"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <button type="button" @click="showCreateModal = false"
                                        class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    انصراف
                                </button>
                                <button type="submit"
                                        class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-500/20 transition">
                                    ثبت پرداخت
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Modal ویرایش پرداخت --}}
                <div x-show="showEditModal"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                     @keydown.escape.window="showEditModal = false">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 relative animate-in fade-in zoom-in-95 duration-150">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                ویرایش پرداخت
                            </h3>
                            <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <form method="POST" :action="`{{ url('/user/booking/appointments/' . $appointment->id . '/payments') }}/${editData.id}`" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            {{-- کارت مشخصات فیش/اطلاعات ارسالی کاربر --}}
                            <template x-if="editData.meta && (editData.meta.payer_name || editData.meta.tracking_code || editData.meta.receipt_url || editData.meta.sub_item)">
                                <div class="bg-indigo-50/70 dark:bg-indigo-950/40 p-4 rounded-2xl border border-indigo-100 dark:border-indigo-900/50 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                                        <span class="text-xs font-bold text-indigo-900 dark:text-indigo-200 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            مشخصات پرداخت و فیش ارسالی کاربر
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        <template x-if="editData.meta.payer_name">
                                            <div class="bg-white/80 dark:bg-gray-800/80 p-2.5 rounded-xl border border-indigo-100/60 dark:border-gray-700">
                                                <span class="text-gray-500 dark:text-gray-400 block mb-0.5">واریزکننده:</span>
                                                <span class="font-bold text-gray-900 dark:text-gray-100" x-text="editData.meta.payer_name + (editData.meta.payer_mobile ? ' (' + editData.meta.payer_mobile + ')' : '')"></span>
                                            </div>
                                        </template>
                                        <template x-if="editData.meta.tracking_code">
                                            <div class="bg-white/80 dark:bg-gray-800/80 p-2.5 rounded-xl border border-indigo-100/60 dark:border-gray-700">
                                                <span class="text-gray-500 dark:text-gray-400 block mb-0.5">کد پیگیری:</span>
                                                <span class="font-bold text-gray-900 dark:text-gray-100" x-text="editData.meta.tracking_code"></span>
                                            </div>
                                        </template>
                                        <template x-if="editData.meta.payment_date">
                                            <div class="bg-white/80 dark:bg-gray-800/80 p-2.5 rounded-xl border border-indigo-100/60 dark:border-gray-700">
                                                <span class="text-gray-500 dark:text-gray-400 block mb-0.5">تاریخ فیش:</span>
                                                <span class="font-bold text-gray-900 dark:text-gray-100" x-text="editData.meta.payment_date"></span>
                                            </div>
                                        </template>
                                        <template x-if="editSubItemLabel || editData.meta.sub_item_label || editData.meta.sub_item">
                                            <div class="bg-white/80 dark:bg-gray-800/80 p-2.5 rounded-xl border border-indigo-100/60 dark:border-gray-700">
                                                <span class="text-gray-500 dark:text-gray-400 block mb-0.5">حساب / گزینه انتخابی:</span>
                                                <span class="font-bold text-gray-900 dark:text-gray-100" x-text="editSubItemLabel || editData.meta.display_label || editData.meta.sub_item_label || editData.meta.sub_item"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <template x-if="editData.meta.receipt_url">
                                        <div class="pt-1">
                                            <a :href="editData.meta.receipt_url" target="_blank"
                                               class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                <span>مشاهده و دریافت تصویر/فایل رسید پرداخت</span>
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">مبلغ (به {{ $currencyLabel }}) *</label>
                                    <input type="text"
                                           x-model="editAmountDisplay"
                                           @input="editAmountDisplay = formatNumber($event.target.value)"
                                           required
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-colors">
                                    <input type="hidden" name="amount" :value="unformatNumber(editAmountDisplay)">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">روش پرداخت *</label>
                                    <select name="type" x-model="editType" required
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-colors">
                                        @foreach($availablePaymentMethods as $key => $label)
                                            <option value="{{ $key }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- سلکت باکس انتخاب زیر-آیتم در حالت ویرایش --}}
                            <template x-if="subItems[editType] && subItems[editType].length > 0">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">گزینه / حساب پرداخت *</label>
                                    <select x-model="editSubItemLabel" required
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-colors">
                                        <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">-- انتخاب کنید --</option>
                                        <template x-for="item in subItems[editType]" :key="item.id">
                                            <option :value="item.label" :selected="editSubItemLabel === item.label" x-text="item.label" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"></option>
                                        </template>
                                    </select>
                                    <input type="hidden" name="sub_item_label" :value="editSubItemLabel">
                                </div>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت پرداخت *</label>
                                    <select name="status" x-model="editData.status" required
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-colors">
                                        <option value="PAID" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">پرداخت شده</option>
                                        <option value="PENDING" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">در انتظار پرداخت</option>
                                        <option value="FAILED" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">ناموفق</option>
                                        <option value="REFUNDED" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">استرداد شده</option>
                                        <option value="CANCELLED" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">لغو شده</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ پرداخت *</label>
                                    <input type="text"
                                           name="paid_at_jalali"
                                           x-model="editData.paid_at_jalali"
                                           data-jdp
                                           data-jdp-only-date
                                           required
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-colors">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">کد پیگیری / شناسه مرجع</label>
                                    <input type="text" name="gateway_ref" x-model="editData.gateway_ref"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-colors">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات و یادداشت ادمین</label>
                                <textarea name="notes" x-model="editData.notes" rows="2"
                                          class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-colors"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <button type="button" @click="showEditModal = false"
                                        class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    انصراف
                                </button>
                                <button type="submit"
                                        class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition">
                                    ذخیره تغییرات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 lg:p-8">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                </svg>
                فرم اطلاعات تکمیلی نوبت
            </h2>

            @if(!empty($formResponses) || !empty($legacyResponses))
                @if(!empty($formResponses))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach($formResponses as $response)
                            @php
                                $value = $response['value'];
                                $isToothNumber = isset($response['type']) && $response['type'] === 'tooth_number';

                                $parsedTeeth = [];
                                if ($isToothNumber) {
                                    if (is_array($value)) {
                                        $parsedTeeth = array_values(array_filter(array_map('intval', $value)));
                                    } elseif (is_string($value) && trim($value) !== '') {
                                        $decoded = json_decode($value, true);
                                        if (is_array($decoded)) {
                                            $parsedTeeth = array_values(array_filter(array_map('intval', $decoded)));
                                        } else {
                                            $parsedTeeth = array_values(array_filter(array_map('intval', preg_split('/[\s,،]+/', $value))));
                                        }
                                    } elseif (is_numeric($value)) {
                                        $parsedTeeth = [(int)$value];
                                    }
                                }
                            @endphp

                            <div class="flex flex-col gap-2 {{ $isToothNumber ? 'col-span-1 md:col-span-2' : '' }}">
                                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $response['label'] }}</div>

                                @if($isToothNumber)
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mt-1"
                                         x-data="{
                                             selected: @js($parsedTeeth),
                                             isReadOnly: true,
                                             getToothLabel(id) {
                                                 const mapping = {
                                                     1:  { num: 7, pos: 'UR' }, 2:  { num: 6, pos: 'UR' }, 3:  { num: 5, pos: 'UR' }, 4:  { num: 4, pos: 'UR' },
                                                     5:  { num: 3, pos: 'UR' }, 6:  { num: 2, pos: 'UR' }, 7:  { num: 1, pos: 'UR' },
                                                     8:  { num: 1, pos: 'UL' }, 9:  { num: 2, pos: 'UL' }, 10: { num: 3, pos: 'UL' }, 11: { num: 4, pos: 'UL' },
                                                     12: { num: 5, pos: 'UL' }, 13: { num: 6, pos: 'UL' }, 14: { num: 7, pos: 'UL' },
                                                     15: { num: 7, pos: 'LR' }, 16: { num: 6, pos: 'LR' }, 17: { num: 5, pos: 'LR' }, 18: { num: 4, pos: 'LR' },
                                                     19: { num: 3, pos: 'LR' }, 20: { num: 2, pos: 'LR' }, 21: { num: 1, pos: 'LR' },
                                                     22: { num: 1, pos: 'LL' }, 23: { num: 2, pos: 'LL' }, 24: { num: 3, pos: 'LL' }, 25: { num: 4, pos: 'LL' },
                                                     26: { num: 5, pos: 'LL' }, 27: { num: 6, pos: 'LL' }, 28: { num: 7, pos: 'LL' }
                                                 };
                                                 return mapping[id] ?? { num: id, pos: 'UR' };
                                             },
                                             getQuadrantClasses(id) {
                                                 const tooth = this.getToothLabel(id);
                                                 switch(tooth.pos) {
                                                     case 'UR': return '!border-r-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                                                     case 'UL': return '!border-l-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                                                     case 'LR': return '!border-r-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                                                     case 'LL': return '!border-l-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                                                     default:   return '';
                                                 }
                                             },
                                             getQuadrantTeeth(teethArray, pos) {
                                                 return (teethArray || []).map(Number).filter(t => this.getToothLabel(t).pos === pos).sort((a,b) => a - b);
                                             },
                                             toggle(id) {},
                                             is(id) {
                                                 return this.selected.map(Number).includes(Number(id)) ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                                             }
                                         }">
                                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2.5 h-6 rounded-full bg-rose-500 shrink-0"></span>
                                                <h2 class="font-bold text-gray-800 dark:text-gray-100 text-sm">نقشه دندانی (مشاهده نوبت)</h2>
                                            </div>
                                        </div>
                                        <div class="px-4 pt-5 pb-2 relative">
                                            <div class="absolute top-6 left-6 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm
                                                        px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold block mb-0.5">تعداد انتخاب</span>
                                                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400"
                                                      x-text="selected.length">0</span>
                                            </div>
                                            <div class="flex justify-center select-none dental-chart-wrapper mx-auto mb-4">
                                                <x-booking::dental-chart/>
                                            </div>
                                        </div>
                                        <div class="px-5 py-3.5 flex items-center gap-3 min-h-14 border-t border-gray-150 dark:border-gray-700/50 bg-gray-50/60 dark:bg-gray-900/20">
                                            <template x-if="selected.length > 0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-bold shrink-0">دندان‌های انتخابی:</span>
                                                    <div class="inline-grid grid-cols-2 select-none">
                                                        <!-- Row 1: UR | UL -->
                                                        <!-- UR -->
                                                        <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'UR')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <!-- UL -->
                                                        <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'UL')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>

                                                        <!-- Row 2: LR | LL -->
                                                        <!-- LR -->
                                                        <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'LR')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <!-- LL -->
                                                        <div class="pt-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                                            <template x-for="t in getQuadrantTeeth(selected, 'LL')" :key="t">
                                                                <div class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black border-0 border-solid rounded-none pointer-events-none"
                                                                     :class="[getQuadrantClasses(t)]"
                                                                     x-text="getToothLabel(t).num">
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="selected.length === 0">
                                                <span class="text-xs text-gray-400 dark:text-gray-500 self-center flex items-center gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    هیچ دندانی انتخاب نشده است
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-base text-gray-900 dark:text-gray-100 font-semibold p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700/60 inline-block w-full">
                                        @if(is_array($value))
                                            {{ implode('، ', array_filter(array_map('strval', $value))) ?: '—' }}
                                        @else
                                            {{ $value !== null && $value !== '' ? $value : '—' }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(!empty($legacyResponses))
                    <div class="mt-8 border-t border-gray-150 dark:border-gray-700/50 pt-6">
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-amber-500 rounded-full"></span>
                            اطلاعات قدیمی (مربوط به نسخه قبل)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($legacyResponses as $response)
                                @php
                                    $val = $response['value'];
                                @endphp
                                <div class="flex flex-col gap-2">
                                    <div class="text-xs text-gray-400 dark:text-gray-500 font-medium">{{ $response['label'] }}</div>
                                    <div class="text-sm text-gray-900 dark:text-gray-100 font-semibold p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700/60 inline-block w-full">
                                        @if(is_array($val))
                                            {{ implode('، ', array_filter(array_map('strval', $val))) ?: '—' }}
                                        @else
                                            {{ $val !== null && $val !== '' ? $val : '—' }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mb-3 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <p class="text-sm font-medium">فرمی برای این نوبت ثبت نشده است.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
