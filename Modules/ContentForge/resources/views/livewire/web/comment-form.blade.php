<div class="mt-8 space-y-6">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white">ارسال دیدگاه</h3>
    
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-4">
        @if(!auth()->check())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500">نام شما</label>
                    <input type="text" wire:model.live="authorName" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border rounded-xl text-sm focus:outline-none">
                    @error('authorName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500">ایمیل شما</label>
                    <input type="email" wire:model.live="authorEmail" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border rounded-xl text-sm focus:outline-none text-left font-mono">
                    @error('authorEmail') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        <div class="space-y-1">
            <label class="text-xs text-gray-500">متن دیدگاه</label>
            <textarea wire:model.live="body" rows="4" placeholder="بازخورد خود را بنویسید..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border rounded-xl text-sm focus:outline-none"></textarea>
            @error('body') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
            ارسال دیدگاه
        </button>
    </form>
</div>
