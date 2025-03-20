<?php

use App\Http\Controllers\{DashboardController,
    ForceDeleteController,
    NavController,
    RestoreController,
    SearchController};
use App\Models\User;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', 'verified', 'roles:' . (new User())->adminUsers()->pluck('id')->join('|')])
    ->prefix('panel')->name('panel.')->group(callback: function () {

        Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');

        include('users.php');
        include('dictionnaries.php');

        # Dynamic modals
        Route::get('modal/{requested}', [ModalController::class, 'distribute'])->name('modal');


        Route::any('mail/{type}/{identifier}', [MailController::class, 'distribute'])->name('mailer');

        // NAV
        Route::resource('nav', NavController::class);

        // Recherche
        Route::get('search', [SearchController::class, 'parse'])->name('search');

        // Generic
        Route::delete('forceDelete', [ForceDeleteController::class, 'process'])->name('forcedelete');
        Route::post('restore', [RestoreController::class, 'process'])->name('restore');
    });



