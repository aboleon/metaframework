<?php
/**
 * Utilisateurs
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AccountCardController,
    AccountDocumentController,
    AccountMailController,
    AccountPhoneController,
    AccountController,
    GroupAddressController,
    GroupController,
    RoleController,
    AccountAddressController,
    UserController};

Route::get('role', [RoleController::class, 'index'])->name('roles');

Route::get('users/oftype/{role}', [UserController::class, 'index'])->name('users.index');
Route::put('users/oftype/{role}', [UserController::class, 'index'])->name('users.index_update');
Route::get('users/create/{role}', [UserController::class, 'create'])->name('users.create_type');
Route::get('users/oftype/{role}/archived', [UserController::class, 'index'])->name('users.archived');
Route::post('restore/{account}', [UserController::class, 'restore'])->name('users.restore');
Route::resource('users', UserController::class)->except(['index']);

Route::prefix('accounts')->name('accounts.')->group(function () {
    Route::get('oftype/{role}', [AccountController::class, 'index'])->name('index');
    Route::get('oftype/{role}/archived', [AccountController::class, 'index'])->name('archived');
    Route::post('restore/{account}', [AccountController::class, 'restore'])->name('restore');
    Route::post('replicate/{account}', [AccountController::class, 'replicate'])->name('replicate');
});


Route::resource('accounts', AccountController::class)->except(['index','show']);

Route::resource('accounts.addresses', AccountAddressController::class);
Route::resource('accounts.phone', AccountPhoneController::class);
Route::resource('accounts.mail', AccountMailController::class);
Route::resource('accounts.documents', AccountDocumentController::class);
Route::resource('accounts.cards', AccountCardController::class);
//Route::resource('useraddresses', UserAddressController::class);
//Route::any('accountSearch', [AccountSearchController::class, 'index'])->name('accounts.search');

Route::resource('groups', GroupController::class);
Route::resource('groups.addresses', GroupAddressController::class);






