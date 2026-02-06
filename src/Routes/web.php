<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MetaFramework\Accessors\Routing;
use MetaFramework\Controllers\AjaxController;
use MetaFramework\Controllers\SettingsController;
use MetaFramework\Controllers\SiteOwnerController;
use MetaFramework\Controllers\VatController;

Route::prefix(Routing::backend())
    ->name('mfw.')
    ->middleware(['web'])->group(function () {

        // Ajax requests
        Route::post('mfw-ajax', [AjaxController::class, 'distribute'])->name('ajax');

        Route::resource('siteowner', SiteOwnerController::class);
        Route::resource('vat', VatController::class);

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('show', [SettingsController::class, 'index'])->name('index');
            Route::post('update', [SettingsController::class, 'update'])->name('update');
        });

    });
