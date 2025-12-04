@extends('layouts.user')

@php
    use App\Support\WidgetRegistry;
    use App\Models\WidgetSetting;

    $title = 'داشبورد';

    // همه ویجت‌های ثبت‌شده از هسته + ماژول‌ها
    $allWidgets = WidgetRegistry::all();

    $userWidgetsKeys = [];

    if(auth()->check()) {
        $user = auth()->user();

        // اگر User از Spatie\HasRoles استفاده می‌کند:
        $roleIds = method_exists($user, 'roles')
            ? $user->roles()->pluck('id')
            : collect();

        if ($roleIds->isNotEmpty()) {
            $userWidgetsKeys = WidgetSetting::whereIn('role_id', $roleIds)
                ->where('is_active', true)
                ->pluck('widget_key')
                ->unique()
                ->toArray();
        }
    }
@endphp

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($allWidgets as $widget)
            @php
                // چک کردن فعال بودن برای نقش
                $enabledForRole = in_array($widget['key'], $userWidgetsKeys, true);

                // چک permission (اگر ست شده)
                $hasPermission = true;
                if (!empty($widget['permission']) && auth()->check()) {
                    $hasPermission = auth()->user()->can($widget['permission']);
                }
            @endphp

            @if($enabledForRole && $hasPermission)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    @include($widget['view'])
                </div>
            @endif
        @endforeach
    </div>
@endsection
