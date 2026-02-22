<tr>
    <td colspan="{{ $canManageProperties ? '8' : '6' }}" class="py-12 text-center">
        <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
            <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            <p class="text-base font-medium text-gray-900 dark:text-white">
                @if($isTrash)
                    سطل زباله خالی است
                @elseif($isAiSearch)
                    هیچ ملکی با این مشخصات یافت نشد
                @else
                    هیچ ملکی یافت نشد
                @endif
            </p>
            @if(!$isTrash)
                <p class="text-sm mt-1">
                    @if($isAiSearch)
                        می‌توانید جستجوی خود را تغییر دهید یا فیلتر را حذف کنید.
                    @else
                        اولین ملک خود را ثبت کنید.
                    @endif
                </p>
                @if(!$isAiSearch)
                    <a href="{{ route('user.properties.create') }}" class="mt-4 text-indigo-600 hover:underline text-sm font-bold">افزودن ملک جدید</a>
                @endif
            @endif
        </div>
    </td>
</tr>
