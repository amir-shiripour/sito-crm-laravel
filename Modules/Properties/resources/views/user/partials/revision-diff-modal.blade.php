{{-- پنجره مقایسه تغییرات اقامتگاه (Visual Diff Modal) --}}
<div x-data="revisionDiffModal()"
     @open-revision-diff.window="openModal($event.detail)"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm transition-opacity"
         @click="closeModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:w-full sm:max-w-4xl border border-gray-200 dark:border-gray-700/80 font-sans"
             @click.away="closeModal()">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-indigo-50/50 via-transparent to-transparent dark:from-indigo-950/20">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>بررسی و مقایسه تغییرات ویرایش اقامتگاه</span>
                            <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 font-bold">
                                نسخه جدید
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            تغییرات ثبت‌شده توسط: <strong class="text-gray-700 dark:text-gray-200" x-text="revision.user_name || 'میزبان'"></strong> در تاریخ <span x-text="revision.created_at_jalali || '—'"></span>
                        </p>
                    </div>
                </div>

                <button type="button" @click="closeModal()" class="w-9 h-9 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="p-6 space-y-6">
                {{-- بارگذاری --}}
                <div x-show="isLoading" class="py-12 text-center text-gray-400 dark:text-gray-500 space-y-3">
                    <svg class="w-8 h-8 animate-spin mx-auto text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <p class="text-xs">در حال بارگذاری جزئیات تغییرات...</p>
                </div>

                <div x-show="!isLoading">
                    {{-- هشدار راهنما --}}
                    <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 flex items-start gap-3 mb-5 text-xs text-indigo-900 dark:text-indigo-200 leading-relaxed">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>تمامی آیتم‌هایی که توسط میزبان دستخوش تغییر شده‌اند در جدول زیر قبل و بعد از ویرایش به صورت تفکیک‌شده نمایش داده شده‌اند. در صورت تأیید، مقادیر جدید در سایت فعال می‌گردند.</span>
                    </div>

                    {{-- جدول مقایسه تغییرات --}}
                    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700/80">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="py-3 px-4 w-1/4">مشخصه / فیلد</th>
                                    <th class="py-3 px-4 w-3/8 text-rose-700 dark:text-rose-400">مقدار قبلی (قبلاً تأیید شده)</th>
                                    <th class="py-3 px-4 w-3/8 text-emerald-700 dark:text-emerald-400">مقدار جدید (درخواست ویرایش)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                <template x-for="(item, idx) in revision.changes_summary" :key="idx">
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 px-4 font-bold text-gray-800 dark:text-gray-200" x-text="item.label || item.field"></td>
                                        
                                        {{-- مقدار قبلی --}}
                                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400 bg-rose-50/20 dark:bg-rose-950/10">
                                            <template x-if="item.type === 'image'">
                                                <div class="flex items-center gap-2">
                                                    <template x-if="item.old && item.old !== '—'">
                                                        <img :src="'/storage/' + item.old" class="w-12 h-12 rounded-lg object-cover border border-rose-200 dark:border-rose-900/60">
                                                    </template>
                                                    <span x-show="!item.old || item.old === '—'">بدون تصویر</span>
                                                </div>
                                            </template>
                                            <template x-if="item.type !== 'image'">
                                                <span class="line-through decoration-rose-400 text-rose-800 dark:text-rose-300 font-sans" x-text="item.old"></span>
                                            </template>
                                        </td>

                                        {{-- مقدار جدید --}}
                                        <td class="py-3 px-4 font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50/20 dark:bg-emerald-950/10">
                                            <template x-if="item.type === 'image'">
                                                <div class="flex items-center gap-2">
                                                    <template x-if="item.new && item.new !== '—'">
                                                        <img :src="'/storage/' + item.new" class="w-12 h-12 rounded-lg object-cover border border-emerald-300 dark:border-emerald-700">
                                                    </template>
                                                    <span x-show="!item.new || item.new === '—'">حذف شده</span>
                                                </div>
                                            </template>
                                            <template x-if="item.type !== 'image'">
                                                <span class="font-sans" x-text="item.new"></span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>

                                <template x-if="!revision.changes_summary || revision.changes_summary.length === 0">
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                            هیچ تغییری در مشخصات این نسخه ثبت نشده است.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- فرم رد درخواست (در صورت کلیک روی رد) --}}
                    <div x-show="showRejectReasonInput" x-transition class="mt-4 p-4 rounded-2xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 space-y-3">
                        <label class="block text-xs font-bold text-rose-900 dark:text-rose-300">
                            علت عدم تأیید و رد ویرایش (برای میزبان ارسال خواهد شد):
                        </label>
                        <textarea x-model="rejectionReason" rows="2" class="w-full rounded-xl border-rose-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-rose-500 focus:ring-rose-500 dark:border-rose-800 dark:bg-gray-900 dark:text-gray-100 font-sans resize-none" placeholder="مثال: نرخ شبانه نامتعارف است یا تصویر جدید وضوح کافی ندارد..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/40">
                <button type="button" @click="closeModal()" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 dark:border-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    بستن
                </button>

                <div class="flex items-center gap-3" x-show="!isLoading">
                    {{-- دکمه رد درخواست --}}
                    <button type="button"
                            @click="handleRejectAction()"
                            class="px-5 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span x-text="showRejectReasonInput ? 'تأیید و ثبت رد ویرایش' : 'رد ویرایش'"></span>
                    </button>

                    {{-- دکمه تأیید درخواست --}}
                    <button type="button"
                            @click="handleApproveAction()"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>تأیید و اعمال تغییرات در سایت</span>
                    </button>
                </div>
            </div>

            {{-- فرم مخفی برای ارسال اکشن تأیید / رد --}}
            <form id="revision-action-form" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="approval_status" id="revision-form-status">
                <input type="hidden" name="rejection_reason" id="revision-form-reason">
            </form>
        </div>
    </div>
</div>

<script>
    function revisionDiffModal() {
        return {
            isOpen: false,
            isLoading: false,
            propertyId: null,
            reviewUrl: null,
            revision: {},
            showRejectReasonInput: false,
            rejectionReason: '',

            openModal(detail) {
                this.propertyId = detail.propertyId;
                this.reviewUrl = detail.reviewUrl;
                this.isOpen = true;
                this.isLoading = true;
                this.showRejectReasonInput = false;
                this.rejectionReason = '';

                fetch(`/user/properties/${this.propertyId}/rental/pending-revision`)
                    .then(res => res.json())
                    .then(data => {
                        this.isLoading = false;
                        if (data.success && data.revision) {
                            this.revision = data.revision;
                        } else {
                            alert(data.message || 'خطا در بارگذاری اطلاعات بازبینی.');
                            this.closeModal();
                        }
                    })
                    .catch(err => {
                        this.isLoading = false;
                        alert('خطا در ارتباط با سرور.');
                        this.closeModal();
                    });
            },

            closeModal() {
                this.isOpen = false;
                this.revision = {};
                this.showRejectReasonInput = false;
                this.rejectionReason = '';
            },

            handleApproveAction() {
                if (!confirm('آیا از تأیید این تغییرات و انتشار آن در سایت اطمینان دارید؟')) {
                    return;
                }
                const form = document.getElementById('revision-action-form');
                form.action = this.reviewUrl;
                document.getElementById('revision-form-status').value = 'approved';
                document.getElementById('revision-form-reason').value = '';
                form.submit();
            },

            handleRejectAction() {
                if (!this.showRejectReasonInput) {
                    this.showRejectReasonInput = true;
                    return;
                }

                if (!this.rejectionReason.trim()) {
                    alert('لطفاً دلیل عدم تأیید را وارد کنید.');
                    return;
                }

                const form = document.getElementById('revision-action-form');
                form.action = this.reviewUrl;
                document.getElementById('revision-form-status').value = 'rejected';
                document.getElementById('revision-form-reason').value = this.rejectionReason.trim();
                form.submit();
            }
        };
    }
</script>
