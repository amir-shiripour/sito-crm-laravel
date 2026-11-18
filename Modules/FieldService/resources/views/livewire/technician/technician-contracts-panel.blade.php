<div class="font-sans">
    <style>
        .contract-modal-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.75rem;
            text-align: right;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .contract-modal-body table th,
        .contract-modal-body table td {
            border: 1px solid #cbd5e1;
            padding: 0.5rem 0.75rem;
        }
        .contract-modal-body table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
        }
        .dark .contract-modal-body table,
        .dark .contract-modal-body table th,
        .dark .contract-modal-body table td {
            border-color: #334155;
        }
        .dark .contract-modal-body table th {
            background-color: #1e293b;
            color: #f8fafc;
        }
        .dark .contract-modal-body table td {
            color: #cbd5e1;
        }
    </style>
    @if($compactPortalMode)
        {{-- Compact Mandatory Banner inside /fieldservice/technician --}}
        @if($pendingCount > 0)
            <div class="mx-3 mt-2 p-3.5 rounded-2xl bg-amber-500/15 border border-amber-500/40 text-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-lg backdrop-blur-md">
                <div class="text-xs leading-relaxed">
                    <div class="font-bold text-amber-300">قرارداد همکاری در انتظار تایید و امضای شماست</div>
                    <div class="text-[11px] text-slate-200 mt-0.5">
                        برای امکان آنلاین شدن و دریافت سفارش‌ها، لطفاً قرارداد صادرشده را مطالعه و امضا فرمایید.
                    </div>
                </div>
                <button type="button" wire:click="openFirstPendingContract"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shrink-0 transition">
                    مشاهده و امضای قرارداد
                </button>
            </div>
        @endif
    @else
        {{-- Full Contracts List Tab inside /user/profile --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        قراردادهای همکاری تکنسین
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                        مشاهده و تایید قراردادهای صادرشده از طرف مدیریت
                    </p>
                </div>
                @if($pendingCount > 0)
                    <span class="self-start sm:self-auto px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap shrink-0 bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                        {{ $pendingCount }} قرارداد در انتظار تایید
                    </span>
                @endif
            </div>

            <div class="p-6 space-y-4">
                @if($successMessage)
                    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                        {{ $successMessage }}
                    </div>
                @endif

                @forelse($contracts as $contract)
                    <div wire:key="tech-contract-{{ $contract->id }}"
                         class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/15 px-2.5 py-0.5 rounded-lg">
                                    {{ $contract->contract_number }}
                                </span>
                                @if($contract->status === 'signed')
                                    <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                                        امضا و تایید شده
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300">
                                        در انتظار مطالعه و امضا
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $contract->title }}
                            </div>
                            <div class="text-[11px] text-slate-600 dark:text-slate-300">
                                تاریخ صدور: {{ function_exists('jdate') ? jdate($contract->created_at)->format('Y/m/d') : $contract->created_at->format('Y-m-d') }}
                                @if($contract->signed_at)
                                    • تاریخ تایید: {{ function_exists('jdate') ? jdate($contract->signed_at)->format('Y/m/d H:i') : $contract->signed_at->format('Y-m-d H:i') }}
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0 flex flex-wrap items-center gap-2">
                            <a href="{{ route('user.contracts.pdf', $contract->id) }}" target="_blank"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                مشاهده PDF
                            </a>
                            <a href="{{ route('user.contracts.print', $contract->id) }}" target="_blank"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                چاپ
                            </a>
                            <button type="button" wire:click="openContract({{ $contract->id }})"
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $contract->status === 'active' ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200' }}">
                                {{ $contract->status === 'active' ? 'مشاهده و امضای قرارداد' : 'مشاهده متن قرارداد' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-600 dark:text-slate-300">
                        هنوز قراردادی برای شما صادر نشده است.
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Contract Viewer & Signing Modal --}}
    @if($showContractModal && $viewingContract)
        <div wire:key="tech-contract-modal-{{ $viewingContract->id }}"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm overflow-y-auto">
            <div @click.outside="$wire.closeContractModal()"
                 class="w-full max-w-3xl bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden my-auto font-sans">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $viewingContract->contract_number }}
                        </div>
                        <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">
                            {{ $viewingContract->title }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('user.contracts.pdf', $viewingContract->id) }}" target="_blank"
                           class="px-3 py-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-600 dark:text-indigo-300 border border-indigo-500/30 text-xs font-bold inline-flex items-center gap-1 transition">
                            <span>PDF</span>
                        </a>
                        <a href="{{ route('user.contracts.print', $viewingContract->id) }}" target="_blank"
                           class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1 transition">
                            <span>چاپ</span>
                        </a>
                        <button type="button" wire:click="closeContractModal"
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                            ✕
                        </button>
                    </div>
                </div>

                <div class="contract-modal-body p-6 max-h-[60vh] overflow-y-auto bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 text-xs sm:text-sm leading-relaxed prose dark:prose-invert max-w-none">
                    {!! $viewingContract->rendered_body !!}
                </div>

                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 space-y-3">
                    @if($viewingContract->status === 'active')
                        @if($errorMessage)
                            <div class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-600 dark:text-rose-300 text-xs font-bold">
                                {{ $errorMessage }}
                            </div>
                        @endif

                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" wire:model="agreeTerms"
                                   class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                متن کامل قرارداد را مطالعه کرده و تمام مفاد و شرایط آن را می‌پذیرم.
                            </span>
                        </label>

                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeContractModal"
                                    class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">
                                بستن
                            </button>
                            <button type="button" wire:click="signContract" wire:loading.attr="disabled"
                                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 transition disabled:opacity-70">
                                <span wire:loading.remove wire:target="signContract">تایید و امضای قرارداد</span>
                                <span wire:loading wire:target="signContract">در حال ثبت...</span>
                            </button>
                        </div>
                    @else
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                این قرارداد در تاریخ {{ function_exists('jdate') ? jdate($viewingContract->signed_at)->format('Y/m/d H:i') : $viewingContract->signed_at }} توسط شما تایید شده است.
                            </span>
                            <button type="button" wire:click="closeContractModal"
                                    class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">
                                بستن
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
