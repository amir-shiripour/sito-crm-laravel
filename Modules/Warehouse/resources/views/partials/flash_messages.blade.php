@if(session('success'))
    <div class="mb-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-xl flex items-center justify-between gap-3 transition-colors" role="alert">
        <div class="flex items-center gap-2.5">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-300">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="text-xs font-bold">{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.closest('[role=alert]').remove()" class="text-emerald-600 dark:text-emerald-400 hover:opacity-75">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 px-4 py-3 rounded-xl flex items-center justify-between gap-3 transition-colors" role="alert">
        <div class="flex items-center gap-2.5">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-rose-500/20 text-rose-600 dark:text-rose-300">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="text-xs font-bold">{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.closest('[role=alert]').remove()" class="text-rose-600 dark:text-rose-400 hover:opacity-75">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="mb-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 p-4 rounded-xl" role="alert">
        <div class="flex items-start gap-2.5">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-rose-500/20 text-rose-600 dark:text-rose-300 shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            <div>
                <h4 class="text-xs font-black">خطا در اعتبارسنجی اطلاعات:</h4>
                <ul class="mt-1.5 text-xs space-y-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
