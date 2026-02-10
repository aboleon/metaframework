<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MetaFramework\Accessors\Routing;
use MetaFramework\Controllers\AjaxController;
use MetaFramework\Controllers\RoleController;
use MetaFramework\Controllers\SettingsController;
use MetaFramework\Controllers\SiteOwnerController;
use MetaFramework\Controllers\UserController;
use MetaFramework\Controllers\VatController;

Route::prefix(Routing::backend())
    ->name('mfw.')
    ->middleware(['web', 'auth'])->group(function () {

        // Ajax requests
        Route::post('mfw-ajax', [AjaxController::class, 'distribute'])->name('ajax');

        Route::resource('siteowner', SiteOwnerController::class);
        Route::resource('vat', VatController::class);
        Route::resource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('users/oftype/{role}', [UserController::class, 'index'])->name('users.index');
        Route::put('users/oftype/{role}', [UserController::class, 'index'])->name('users.index_update');
        Route::get('users/create/{role?}', [UserController::class, 'create'])->name('users.create_type');
        Route::get('users/oftype/{role}/archived', [UserController::class, 'index'])->name('users.archived');
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::resource('users', UserController::class)->except(['index', 'create', 'show']);

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('show', [SettingsController::class, 'index'])->name('index');
            Route::post('update', [SettingsController::class, 'update'])->name('update');
        });

    });
