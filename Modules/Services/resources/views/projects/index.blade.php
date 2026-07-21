@php use Modules\Services\App\Http\Models\Project; @endphp
@extends('layouts.user')
@section('title', 'پروژه‌ها')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-600 text-white shadow-lg shadow-purple-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </span>
                مدیریت پروژه‌ها
            </h1>
            @can('create', Project::class)
                <a href="{{ route('services.projects.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white font-bold text-sm shadow-md shadow-purple-500/30 hover:bg-purple-700 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    پروژه جدید
                </a>
            @endcan
        </div>

    @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET"
              class="bg-white dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو: نام پروژه..."
                       class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 pr-11 pl-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all dark:text-white">
            </div>
            <select name="service_id"
                    class="md:w-48 rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all dark:text-white">
                <option value="">همه سرویس‌ها</option>
                @foreach($services as $srv)
                    <option
                        value="{{ $srv->id }}" @selected(request('service_id') == $srv->id)>{{ $srv->name }}</option>
                @endforeach
            </select>
            <select name="status_id"
                    class="md:w-48 rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all dark:text-white">
                <option value="">همه وضعیت‌ها</option>
                @foreach($statuses as $st)
                    <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->name }}</option>
                @endforeach
            </select>
            <select name="priority"
                    class="md:w-44 rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all dark:text-white">
                <option value="">همه اولویت‌ها</option>
                <option value="low" @selected(request('priority') === 'low')>کم</option>
                <option value="medium" @selected(request('priority') === 'medium')>متوسط</option>
                <option value="high" @selected(request('priority') === 'high')>زیاد</option>
                <option value="urgent" @selected(request('priority') === 'urgent')>فوری</option>
            </select>
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 md:flex-none px-6 py-2.5 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400 text-sm font-bold hover:bg-purple-100 dark:hover:bg-purple-500/20 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    فیلتر
                </button>
                @if(request()->hasAny(['search', 'service_id', 'status_id', 'priority']))
                    <a href="{{ route('services.projects.index') }}"
                       class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div
            class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/30">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider">
                            نام پروژه
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider">
                            مشتری
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider text-center">
                            سرویس
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider text-center">
                            اولویت
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider text-center">
                            پیشرفت
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider text-center">
                            وضعیت
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider text-center">
                            تاریخ پایان
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider text-left">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($projects as $project)
                        @php
                            $priorityConfig = match($project->priority) {
                                'urgent' => ['label' => 'فوری',  'class' => 'bg-red-50 text-red-600 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20'],
                                'high'   => ['label' => 'زیاد',  'class' => 'bg-orange-50 text-orange-600 border-orange-100 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20'],
                                'medium' => ['label' => 'متوسط', 'class' => 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20'],
                                default  => ['label' => 'کم',    'class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/50 dark:text-gray-400 dark:border-gray-700'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $project->name }}</div>
                                @if($project->code)
                                    <div class="text-xs font-mono text-gray-400 mt-0.5">{{ $project->code }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="font-medium text-gray-700 dark:text-gray-300">{{ $project->customer->name ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($project->service)
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                            {{ $project->service->name }}
                                        </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $priorityConfig['class'] }}">
                                        {{ $priorityConfig['label'] }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-20 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-purple-500 rounded-full transition-all"
                                             style="width: {{ $project->progress }}%"></div>
                                    </div>
                                    <span class="text-xs font-mono text-gray-500">{{ $project->progress }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($project->status)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                                        style="background: {{ $project->status->color }}1a; color: {{ $project->status->color }}; border-color: {{ $project->status->color }}33">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                  style="background: {{ $project->status->color }}"></span>
                                            {{ $project->status->name }}
                                        </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-mono text-gray-500 dark:text-gray-400 dir-ltr whitespace-nowrap">
                                {{ $project->end_date?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-left whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('services.projects.show', $project) }}"
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-500/10 transition-colors"
                                       title="مشاهده">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @can('update', $project)
                                        <a href="{{ route('services.projects.edit', $project) }}"
                                           class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors"
                                           title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endcan
                                    @can('delete', $project)
                                        <form method="POST" action="{{ route('services.projects.destroy', $project) }}"
                                              onsubmit="return confirm('پروژه حذف شود؟')">
                                            @csrf @method('DELETE')
                                            <button
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
                                                title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <div class="max-w-sm mx-auto">
                                    <div
                                        class="mx-auto w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <p class="font-bold text-gray-900 dark:text-white mb-1">هیچ پروژه‌ای یافت نشد</p>
                                    <p class="text-sm text-gray-400 mb-4">می‌توانید با کلیک بر روی دکمه زیر یک پروژه
                                        جدید ایجاد کنید.</p>
                                    @can('create', Project::class)
                                        <a href="{{ route('services.projects.create') }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 text-white font-bold text-sm hover:bg-purple-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 4v16m8-8H4"/>
                                            </svg>
                                            ثبت اولین پروژه
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($projects->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50">{{ $projects->links() }}</div>
            @endif
        </div>
    </div>
@endsection
