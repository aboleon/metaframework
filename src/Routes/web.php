<?php

use Illuminate\Support\Facades\Route;
use MetaFramework\Accessors\Routing;
use MetaFramework\Controllers\{
    AjaxController,
    MetaController,
    NavController,
    SettingsController,
    SiteOwnerController,
    VatController};


Route::prefix(Routing::backend())
    ->name('mfw.')
    ->middleware(['web'])->group(function () {

        // Ajax requests
        Route::post('ajax', [AjaxController::class, 'distribute'])->name('ajax');

        Route::resource('siteowner', SiteOwnerController::class);
        Route::resource('nav', NavController::class);
        Route::resource('vat', VatController::class);

        Route::prefix('meta')->name('meta.')->group(function () {
            Route::any('admin/create', [MetaController::class, 'createAdmin'])->name('create_admin');
            Route::get('create/{type}', [MetaController::class, 'create'])->name('create');
            Route::get('index/{type?}', [MetaController::class, 'index'])->name('list');
            Route::get('show/{type}/{id?}', [MetaController::class, 'show'])->name('show');
        });

        Route::patch('meta/{id}', [MetaController::class, 'patch']);
        Route::resource('meta', MetaController::class)->except(['create', 'index'])->except(['create','show']);

        // Settings
        Route::prefix('settings')->name('settings.')->group(function() {
            Route::get('show', [SettingsController::class, 'index'])->name('index');
            Route::post('update', [SettingsController::class, 'update'])->name('update');
        });

        include('dictionnaries.php');
    });