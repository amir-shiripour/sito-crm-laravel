@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">سرویس‌های نوبت‌دهی</h1>

            @php
                $user = auth()->user();
                $canCreateService =
                    $user &&
                    (
                        $user->can('booking.services.create')
                        || (($isProvider ?? false) && ($settings->allow_role_service_creation ?? false))
                    );
            @endphp

            @if($canCreateService)
                <a class="px-4 py-2 bg-blue-600 text-white rounded" href="{{ route('user.booking.services.create') }}">
                    ایجاد سرویس
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded border overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">نام</th>
                    <th class="p-3 text-right">دسته</th>
                    <th class="p-3 text-right">وضعیت</th>
                    <th class="p-3 text-right">قیمت</th>
                    <th class="p-3 text-right">فرم</th>
                    <th class="p-3 text-right">عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($services as $srv)
                    @php
                        $isPublic = is_null($srv->owner_user_id) || in_array((int)$srv->owner_user_id, $adminOwnerIds ?? [], true);

                        // چون در Controller eager-load کردیم با فیلتر provider_user_id، اگر وجود داشته باشد همین یکی است
                        $spRow = $srv->serviceProviders->first();
                        $isActiveForMe = (bool)($spRow?->is_active ?? false);
                        $canEditService = in_array($srv->id, $editableServiceIds ?? []);
                    @endphp
                    <tr class="border-t">
                        <td class="p-3">{{ $srv->id }}</td>
                        <td class="p-3 font-medium">{{ $srv->name }}</td>
                        <td class="p-3">{{ optional($srv->category)->name ?: '-' }}</td>
                        <td class="p-3">{{ $srv->status }}</td>
                        <td class="p-3">{{ number_format($srv->base_price) }}</td>
                        <td class="p-3">{{ optional($srv->appointmentForm)->name ?: '-' }}</td>
                        <td class="p-3 space-x-2 space-x-reverse">
                            @php
                                $showToggleForMe = (($isProvider ?? false) && $isPublic);
                            @endphp

                            {{-- لینک‌های ویرایش/برنامه زمانی (اگر اجازه دارد) --}}
                            @if($canEditService)
                                <a class="text-blue-600 hover:underline"
                                   href="{{ route('user.booking.services.edit', $srv) }}">
                                    ویرایش
                                </a>

                                <a href="{{ route('user.booking.services.availability.edit', $srv) }}"
                                   class="text-xs px-2 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                                    برنامه زمانی
                                </a>
                            @endif

                            {{-- Toggle همیشه برای Provider روی سرویس‌های عمومی نمایش داده شود --}}
                            @if($showToggleForMe)
                                <form method="POST" action="{{ route('user.booking.services.toggleForMe', $srv) }}" class="inline">
                                    @csrf
                                    <button class="text-xs px-3 py-1 rounded {{ $isActiveForMe ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        {{ $isActiveForMe ? 'غیرفعال برای من' : 'فعال‌سازی برای من' }}
                                    </button>
                                </form>

                                <span class="text-[11px] text-gray-500">
            {{ $isActiveForMe ? 'فعال' : 'غیرفعال' }}
        </span>
                            @endif

                            {{-- اگر هیچکدام نبود --}}
                            @if(! $canEditService && ! $showToggleForMe)
                                <span class="text-xs text-gray-400">فقط مشاهده</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $services->links() }}
    </div>
@endsection
