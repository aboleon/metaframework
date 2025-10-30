<?php

use App\Http\Controllers\DictionnaryController;
use App\Http\Controllers\DictionnaryEntryController;

Route::get('dictionnaryentry/subentrty/{dictionnaryentry}', [DictionnaryEntryController::class, 'subentry'])->name('dictionnaryentry.subentry');

Route::resource('dictionnary', DictionnaryController::class);
Route::resource('dictionnary.entries', DictionnaryEntryController::class)->shallow();
Route::resource('dictionnaryentry', DictionnaryEntryController::class)->except(['create']);
