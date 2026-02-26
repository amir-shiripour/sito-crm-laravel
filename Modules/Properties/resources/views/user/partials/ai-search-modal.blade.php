<div x-show="showAiModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] overflow-y-auto"
     style="display: none;">

    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showAiModal = false"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="showAiModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

            <div class="bg-purple-50/50 dark:bg-purple-900/20 px-6 py-4 border-b border-purple-100 dark:border-purple-800 flex items-center justify-between">
                <h3 class="text-lg font-bold text-purple-900 dark:text-purple-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    جستجوی هوشمند ملک
                </h3>
                <button @click="showAiModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-6 py-6 space-y-4">
                <div class="flex justify-between items-center">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        توضیح دهید چه ملکی مد نظرتان است.
                    </p>
                    <div x-data="{ tooltip: getVoiceSupportTooltip() }">
                        <button type="button" id="ai-voice-btn" :disabled="!isVoiceTypingSupported"
                                x-tooltip="tooltip"
                                class="text-xs flex items-center gap-1.5 px-3 py-1 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-help"
                                :class="{
                                    'bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40': !isVoiceTyping,
                                    'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40 animate-pulse': isVoiceTyping
                                }">
                            <svg x-show="!isVoiceTyping" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14a2 2 0 0 0 2-2V6a2 2 0 0 0-4 0v6a2 2 0 0 0 2 2Zm-2-8a2 2 0 0 1 4 0v6a2 2 0 0 1-4 0V6Zm8 5a1 1 0 0 0-1 1v1a5 5 0 0 1-10 0v-1a1 1 0 1 0-2 0v1a7 7 0 0 0 6 6.92V21a1 1 0 1 0 2 0v-2.08A7 7 0 0 0 20 12v-1a1 1 0 0 0-1-1Z"/></svg>
                            <svg x-show="isVoiceTyping" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.793 5.793a1 1 0 0 1 1.414 0L12 10.586l4.793-4.793a1 1 0 1 1 1.414 1.414L13.414 12l4.793 4.793a1 1 0 0 1-1.414 1.414L12 13.414l-4.793 4.793a1 1 0 0 1-1.414-1.414L10.586 12 5.793 7.207a1 1 0 0 1 0-1.414Z"/></svg>
                            <span x-text="isVoiceTyping ? 'توقف' : 'صوتی'"></span>
                        </button>
                    </div>
                </div>
                <textarea x-model="aiQuery" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 resize-none" placeholder="مثلاً: یک آپارتمان دو خوابه در سعادت آباد با قیمت حدود ۵ میلیارد تومان..."></textarea>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/30 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700">
                <button type="button" @click="performAiSearch" :disabled="isAiSearching || aiQuery.length < 3"
                        class="inline-flex w-full justify-center rounded-xl border border-transparent bg-purple-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 sm:ml-3 sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isAiSearching">جستجو کن</span>
                    <span x-show="isAiSearching" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        در حال تحلیل...
                    </span>
                </button>
                <button type="button" @click="showAiModal = false"
                        class="mt-3 inline-flex w-full justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    انصراف
                </button>
            </div>
        </div>
    </div>
</div>
