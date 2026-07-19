<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('user/smartbot')
    ->name('user.smartbot.')
    ->group(function () {
        Route::get('/dashboard', \Modules\SmartBot\App\Livewire\Admin\AnalyticsDashboard::class)
            ->name('dashboard')
            ->middleware('permission:smartbot.view');

        Route::get('/qna', \Modules\SmartBot\App\Livewire\Admin\QnAManager::class)
            ->name('qna')
            ->middleware('permission:smartbot.manage');

        Route::get('/settings', \Modules\SmartBot\App\Livewire\Admin\SettingsManager::class)
            ->name('settings')
            ->middleware('permission:smartbot.settings');
    });

// Public / client-facing full page chat
Route::middleware(['web'])
    ->get('/chat', function() {
        return view('smartbot::page.chat');
    })
    ->name('smartbot.chat');
