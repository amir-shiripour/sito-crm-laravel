@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">داشبورد نوبت‌دهی</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">آمار ۳۰ روز اخیر سیستم شما</p>
            </div>
            <div class="flex flex-wrap gap-2 justify-end">
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200"
                   href="{{ route('user.booking.services.index') }}">
                   <i class="fi fi-rr-apps text-lg leading-none"></i>
                    سرویس‌ها
                </a>
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-200"
                   href="{{ route('user.booking.appointments.index') }}">
                   <i class="fi fi-rr-calendar text-lg leading-none"></i>
                    نوبت‌ها
                </a>
                @if(auth()->user()?->can('booking.forms.view'))
                    <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-medium hover:bg-sky-700 hover:shadow-lg hover:shadow-sky-500/30 transition-all duration-200"
                       href="{{ route('user.booking.forms.index') }}">
                       <i class="fi fi-rr-document text-lg leading-none"></i>
                        فرم‌ها
                    </a>
                @endif
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-900 hover:shadow-lg hover:shadow-gray-700/30 transition-all duration-200"
                   href="{{ route('user.booking.settings.edit') }}">
                   <i class="fi fi-rr-settings text-lg leading-none"></i>
                    تنظیمات
                </a>
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">کل نوبت‌ها</div>
                    <div class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                        <i class="fi fi-rr-calendar-lines text-xl leading-none"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ number_format($total) }}</div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">نوبت‌های امروز</div>
                    <div class="p-2 bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 rounded-lg">
                        <i class="fi fi-rr-calendar-day text-xl leading-none"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-cyan-600 dark:text-cyan-400">{{ number_format($todaysAppointmentsCount) }}</div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">در انتظار</div>
                    <div class="p-2 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg">
                        <i class="fi fi-rr-time-fast text-xl leading-none"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ number_format($pending) }}</div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">تایید شده</div>
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                        <i class="fi fi-rr-check-circle text-xl leading-none"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ number_format($confirmed) }}</div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">لغو/عدم حضور</div>
                    <div class="p-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-lg">
                        <i class="fi fi-rr-cross-circle text-xl leading-none"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{{ number_format($canceled + $noShow) }}</div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">درآمد قطعی</div>
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg">
                        <i class="fi fi-rr-money-bill-wave text-xl leading-none"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format($revenue) }}</div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Charts Area --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Line Chart --}}
                <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">روند نوبت‌دهی (۳۰ روز اخیر)</h3>
                    <div class="relative h-72 w-full">
                        <canvas id="appointmentsChart"></canvas>
                    </div>
                </div>

                {{-- Upcoming Appointments List --}}
                <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">نوبت‌های پیش‌رو</h3>
                        <a href="{{ route('user.booking.appointments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">مشاهده همه &larr;</a>
                    </div>

                    @if($upcomingAppointments->count() > 0)
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($upcomingAppointments as $app)
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold shrink-0">
                                            {{ mb_substr($app->client->full_name ?? 'م', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $app->client->full_name ?? 'بدون نام' }}
                                                @if($app->client->phone)
                                                    <span class="text-xs text-gray-500 mr-2 dir-ltr inline-block">{{ $app->client->phone }}</span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $app->service->name ?? 'سرویس نامشخص' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-left flex flex-col items-start sm:items-end gap-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 dir-ltr">
                                            @php
                                                $startLocal = $app->start_at_utc ? $app->start_at_utc->copy()->setTimezone(auth()->user()->timezone ?? config('app.timezone')) : null;
                                            @endphp
                                            {{ $startLocal && class_exists('\Morilog\Jalali\Jalalian') ? \Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('Y/m/d H:i') : ($startLocal ? $startLocal->format('Y-m-d H:i') : '') }}
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                            پیش‌رو
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                            <i class="fi fi-rr-calendar-xmark text-3xl mb-2 text-gray-400"></i>
                            <p>نوبتی برای آینده ثبت نشده است.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar Area --}}
            <div class="space-y-6">
                {{-- Pie Chart --}}
                <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">وضعیت نوبت‌ها</h3>
                    @if(array_sum($statusDistribution) > 0)
                        <div class="relative h-64 w-full flex justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    @else
                        <div class="h-64 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                            <i class="fi fi-rr-chart-pie-alt text-4xl mb-2 text-gray-300"></i>
                            <p>دیتایی برای نمایش وجود ندارد</p>
                        </div>
                    @endif
                </div>

                {{-- Top Services --}}
                <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">سرویس‌های پرطرفدار</h3>
                    @if($topServices->count() > 0)
                        <div class="space-y-4">
                            @foreach($topServices as $ts)
                                <div>
                                    <div class="flex justify-between items-end mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $ts->name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $ts->appointments_count }} نوبت</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        @php
                                            $maxCount = $topServices->first()->appointments_count ?: 1;
                                            $percent = ($ts->appointments_count / $maxCount) * 100;
                                        @endphp
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                            سرویسی استفاده نشده است.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Line Chart: Appointments per day
            const ctxLine = document.getElementById('appointmentsChart');
            if (ctxLine) {
                // @ts-ignore
                new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'تعداد نوبت‌ها',
                            data: @json($chartData),
                            borderColor: '#4f46e5', // indigo-600
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#4f46e5',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                rtl: true,
                                titleFont: { family: 'Vazirmatn' },
                                bodyFont: { family: 'Vazirmatn' },
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { family: 'Vazirmatn' } },
                                grid: { borderDash: [4, 4], color: '#e5e7eb' } // gray-200
                            },
                            x: {
                                ticks: { font: { family: 'Vazirmatn' }, maxTicksLimit: 15 },
                                grid: { display: false }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                    }
                });
            }

            // Pie Chart: Status Distribution
            const ctxPie = document.getElementById('statusChart');
            if (ctxPie) {
                const statusLabels = @json(array_keys($statusDistribution));
                const statusData = @json(array_values($statusDistribution));

                // @ts-ignore
                new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: [
                                '#10b981', // emerald-500 (تایید شده)
                                '#f43f5e', // rose-500 (لغو شده)
                                '#f97316', // orange-500 (عدم حضور)
                                '#f59e0b', // amber-500 (در انتظار)
                                '#3b82f6', // blue-500 (انجام شده)
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                rtl: true,
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { family: 'Vazirmatn' }
                                }
                            },
                            tooltip: {
                                rtl: true,
                                titleFont: { family: 'Vazirmatn' },
                                bodyFont: { family: 'Vazirmatn' },
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
