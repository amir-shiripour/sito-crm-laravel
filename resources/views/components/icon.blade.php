@props([
    'name',
    'class' => 'w-5 h-5',
])

@php
    $map = [
        'home' => 'components.icons.home',
        'user-circle' => 'components.icons.user-circle',
        'bell' => 'components.icons.bell',
        'document' => 'components.icons.document',
        'chart' => 'components.icons.chart',
        'users' => 'components.icons.users',
        'shield' => 'components.icons.shield',
        'cube' => 'components.icons.cube',
        'cog' => 'components.icons.cog',
        'activity' => 'components.icons.activity',
        'user-plus' => 'components.icons.user-plus',
    ];

    $view = $map[$name] ?? null;
@endphp

@if ($view)
    @include($view, ['class' => $class])
@endif
