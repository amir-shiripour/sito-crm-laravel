@extends('layouts.user')

@section('content')
    <div class="space-y-5">
        @php
            $user = auth()->user();
            $canCreateService =
            $user &&
            (
            $user->can('booking.services.create')
            || (($isProvider ?? false) && ($settings->allow_role_service_creation ?? false))
            );
        @endphp

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">سرویس‌های نوبت‌دهی</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت و تنظیم سرویس‌ها</p>
            </div>
            @if($canCreateService)
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200"
                   href="{{ route('user.booking.services.create') }}">
                    ایجاد سرویس
                </a>
            @endif
        </div>

        @if(session('success'))
            <div
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نام</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">دسته</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">قیمت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">فرم</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($services as $srv)
                        @php
                            $isPublic = is_null($srv->owner_user_id) || in_array((int)$srv->owner_user_id, $adminOwnerIds ?? [],
                            true);
                            $spRow = $srv->serviceProviders->first();
                            $isActiveForMe = (bool)($spRow?->is_active ?? false);
                            $canEditService = in_array($srv->id, $editableServiceIds ?? []);
                            $showToggleForMe = (($isProvider ?? false) && $isPublic);
                        @endphp
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                {{ $srv->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $srv->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $srv->status === 'ACTIVE' ? 'فعال' : 'غیرفعال' }}
                                </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ optional($srv->category)->name ?: '-' }}
                            </td>
                            <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $srv->status === 'ACTIVE'
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                {{ $srv->status === 'ACTIVE' ? 'فعال' : 'غیرفعال' }}
                            </span>
                            </td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-100">
                                {{ number_format($srv->base_price) }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ optional($srv->appointmentForm)->name ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2 justify-end">
                                    @if($canEditService)
                                        <a class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-300 hover:text-indigo-700 text-sm font-medium"
                                           href="{{ route('user.booking.services.edit', $srv) }}">
                                            ویرایش
                                        </a>

                                        <a href="{{ route('user.booking.services.availability.edit', $srv) }}"
                                           class="text-xs px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:hover:bg-indigo-900/50 transition">
                                            برنامه زمانی
                                        </a>
                                    @endif

                                    @if($showToggleForMe)
                                        <form method="POST" action="{{ route('user.booking.services.toggleForMe', $srv) }}"
                                              class="inline">
                                            @csrf
                                            <button
                                                class="text-xs px-3 py-1.5 rounded-full transition
                                            {{ $isActiveForMe
                                                ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-200 dark:hover:bg-rose-900/50'
                                                : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-200 dark:hover:bg-emerald-900/50' }}">
                                                {{ $isActiveForMe ? 'غیرفعال برای من' : 'فعال‌سازی برای من' }}
                                            </button>
                                        </form>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $isActiveForMe ? 'فعال' : 'غیرفعال' }}
                                </span>
                                    @endif

                                    @if(! $canEditService && ! $showToggleForMe)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">فقط مشاهده</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            {{ $services->links() }}
        </div>
    </div>
@endsection
