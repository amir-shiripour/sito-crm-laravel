<?php

use Illuminate\Support\Facades\Route;

Route::prefix('user')->name('user.')->middleware(['web', 'auth'])->group(function () {
    Route::prefix('sales')->name('sales.')->group(function () {
        
        // Cockpit
        Route::get('cockpit', [\Modules\Sales\App\Http\Controllers\CockpitController::class, 'index'])->name('cockpit')->middleware('can:sales.cockpit.view');
        
        // Pipeline Kanban
        Route::get('pipeline', [\Modules\Sales\App\Http\Controllers\PipelineController::class, 'index'])->name('pipeline')->middleware('can:sales.pipelines.view');
        
        // Deals 360-View
        Route::get('deals/{deal}', [\Modules\Sales\App\Http\Controllers\DealController::class, 'show'])->name('deals.show')->middleware('can:sales.deals.view');
        
        // Lead Manager
        Route::get('leads', [\Modules\Sales\App\Http\Controllers\LeadManagerController::class, 'index'])->name('leads.index')->middleware('can:sales.leads.view');
        
        // Campaigns
        Route::get('campaigns', [\Modules\Sales\App\Http\Controllers\CampaignController::class, 'index'])->name('campaigns.index')->middleware('can:sales.campaigns.view');
        Route::get('campaigns/create', [\Modules\Sales\App\Http\Controllers\CampaignController::class, 'create'])->name('campaigns.create')->middleware('can:sales.campaigns.create');
        Route::post('campaigns', [\Modules\Sales\App\Http\Controllers\CampaignController::class, 'store'])->name('campaigns.store')->middleware('can:sales.campaigns.create');
        Route::get('campaigns/{campaign}', [\Modules\Sales\App\Http\Controllers\CampaignController::class, 'show'])->name('campaigns.show')->middleware('can:sales.campaigns.view');
        Route::get('campaigns/{campaign}/edit', [\Modules\Sales\App\Http\Controllers\CampaignController::class, 'edit'])->name('campaigns.edit')->middleware('can:sales.campaigns.edit');
        Route::put('campaigns/{campaign}', [\Modules\Sales\App\Http\Controllers\CampaignController::class, 'update'])->name('campaigns.update')->middleware('can:sales.campaigns.edit');
        Route::delete('campaigns/{campaign}', [\Modules\Sales\App\Http\Controllers\CampaignController::class, 'destroy'])->name('campaigns.destroy')->middleware('can:sales.campaigns.delete');
        
        Route::get('campaigns/{campaign}/report', [\Modules\Sales\App\Http\Controllers\CampaignReportController::class, 'show'])->name('campaigns.report')->middleware('can:sales.reports.view');
        
        // Reports
        Route::get('reports', [\Modules\Sales\App\Http\Controllers\CampaignReportController::class, 'index'])->name('reports.index')->middleware('can:sales.reports.view');

        // Settings
        Route::get('settings', [\Modules\Sales\App\Http\Controllers\SalesSettingsController::class, 'index'])->name('settings.index')->middleware('can:sales.manage');
    });
});
