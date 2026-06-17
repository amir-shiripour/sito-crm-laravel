@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;

    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
@endphp

@section('title', 'اعلان‌های من')

@section('content')
    <div class="w-full mx-auto px-4 py-8 space-y-6" x-data="notificationsPage()">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </span>
                    اعلان‌های من
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    لیست پیام‌ها و اعلان‌های سیستم متناسب با فعالیت‌های شما
                </p>
            </div>

            @if($notifications->whereNull('read_at')->count() > 0 || $filter === 'unread')
                <div>
                    <button @click="markAllRead('{{ route('user.notifications.mark-all-read') }}')"
                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/30 border border-indigo-100/30 dark:border-indigo-900/30 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>همه به عنوان خوانده شده</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- تب‌ها و فیلترها --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-px">
            <div class="flex gap-2">
                <a href="{{ route('user.notifications.index', ['filter' => 'all']) }}"
                   class="px-4 py-2 text-sm font-semibold rounded-t-xl transition-all border-b-2 {{ $filter === 'all' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 hover:text-gray-700 border-transparent dark:text-gray-400 dark:hover:text-gray-300' }}">
                    همه اعلان‌ها
                </a>
                <a href="{{ route('user.notifications.index', ['filter' => 'unread']) }}"
                   class="px-4 py-2 text-sm font-semibold rounded-t-xl transition-all border-b-2 flex items-center gap-2 {{ $filter === 'unread' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 hover:text-gray-700 border-transparent dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <span>خوانده نشده</span>
                    @php
                        $unreadCount = \Modules\Notifications\Entities\Notification::where('notifiable_id', auth()->id())->whereNull('read_at')->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400 rounded-full">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('user.notifications.index', ['filter' => 'read']) }}"
                   class="px-4 py-2 text-sm font-semibold rounded-t-xl transition-all border-b-2 {{ $filter === 'read' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 hover:text-gray-700 border-transparent dark:text-gray-400 dark:hover:text-gray-300' }}">
                    خوانده شده
                </a>
            </div>
        </div>

        {{-- لیست اعلان‌ها --}}
        <div class="space-y-4">
            @forelse($notifications as $n)
                @php
                    $data = $n->formatted_data;
                    $isUnread = is_null($n->read_at);
                    
                    // بررسی آیکون و رنگ بر اساس نوع نوتیفیکیشن
                    $isEscalation = str_contains($n->type, 'SnoozeEscalation') || str_contains($n->type, 'escalation');
                    $bgColor = $isUnread 
                        ? 'bg-indigo-50/30 dark:bg-indigo-950/10 border-indigo-100 dark:border-indigo-900/30' 
                        : 'bg-white dark:bg-gray-800/40 border-gray-100 dark:border-gray-800/60';
                    
                    $iconClass = $isEscalation 
                        ? 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400 border border-red-100/50 dark:border-red-900/30' 
                        : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-900/30';
                @endphp

                <div id="notification-{{ $n->id }}"
                     class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4 rounded-2xl border transition-all duration-200 hover:shadow-sm {{ $bgColor }}">
                    
                    <div class="flex items-start gap-4 min-w-0 flex-1">
                        {{-- آیکون --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center {{ $iconClass }}">
                            @if($isEscalation)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>

                        {{-- متن پیام --}}
                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ $data['title'] ?? 'اعلان سیستم' }}
                                </span>
                                @if($isUnread)
                                    <span class="inline-flex w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ $data['message'] ?? $data['description'] ?? '' }}
                            </p>
                            @if(isset($data['action_url']))
                                <a href="{{ $data['action_url'] }}" class="inline-flex items-center gap-1 mt-1.5 text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <span>مشاهده مورد مرتبط</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- زمان و دکمه‌ها --}}
                    <div class="flex md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-3 pt-3 md:pt-0 border-t md:border-0 border-gray-100 dark:border-gray-800">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 font-medium whitespace-nowrap" dir="rtl">
                            {{ $n->created_at->diffForHumans() }}
                            <span class="opacity-75">({{ Jalalian::fromCarbon($n->created_at)->format('H:i - Y/m/d') }})</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($isUnread)
                                <button @click="markAsRead('{{ route('user.notifications.mark-read', $n->id) }}', '{{ $n->id }}', $el)"
                                        title="علامت‌گذاری به عنوان خوانده شده"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            @endif
                            <button @click="deleteNotification('{{ route('user.notifications.destroy', $n->id) }}', '{{ $n->id }}', $el)"
                                    title="حذف اعلان"
                                    class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            @empty
                <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-900/40 flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.5m16 3H4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-950 dark:text-white">هیچ اعلانی یافت نشد</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 max-w-sm">
                        در حال حاضر هیچ پیام یا اعلانی برای نمایش وجود ندارد.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- صفحه‌بندی --}}
        @if($notifications->hasPages())
            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
@endsection

@push('js')
<script>
function notificationsPage() {
    return {
        markAsRead(url, id, el) {
            el.disabled = true;
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`notification-${id}`);
                    if (card) {
                        card.classList.remove('bg-indigo-50/30', 'dark:bg-indigo-950/10', 'border-indigo-100', 'dark:border-indigo-900/30');
                        card.classList.add('bg-white', 'dark:bg-gray-800/40', 'border-gray-100', 'dark:border-gray-800/60');
                        const dot = card.querySelector('.rounded-full.bg-indigo-600');
                        if (dot) dot.remove();
                    }
                    el.remove();
                    showToast('success', data.message);
                } else {
                    el.disabled = false;
                }
            })
            .catch(err => {
                el.disabled = false;
                console.error(err);
            });
        },
        markAllRead(url) {
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        },
        deleteNotification(url, id, el) {
            if (!confirm('آیا از حذف این اعلان اطمینان دارید؟')) return;
            el.disabled = true;
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`notification-${id}`);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(10px)';
                        setTimeout(() => card.remove(), 300);
                    }
                    showToast('success', data.message);
                } else {
                    el.disabled = false;
                }
            })
            .catch(err => {
                el.disabled = false;
                console.error(err);
            });
        }
    }
}
</script>
@endpush
