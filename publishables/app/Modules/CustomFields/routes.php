<?php

use App\Models\User;
use App\Modules\CustomFields\Controllers\CustomFormController;

Route::middleware(['auth:sanctum', 'verified', 'roles:' . (new User())->adminUsers()->pluck('id')->join('|')])
    ->prefix('panel')->name('panel.')->group(callback: function () {

        Route::resource('customfields', CustomFormController::class);
    });
