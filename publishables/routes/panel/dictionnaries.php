<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\DictionnaryController;
use App\Http\Controllers\DictionnaryEntryController;
use App\Http\Controllers\ParticipationTypeController;
use App\MailTemplates\Controllers\MailTemplateController;

Route::get('dictionnaryentry/subentrty/{dictionnaryentry}', [DictionnaryEntryController::class, 'subentry'])->name('dictionnaryentry.subentry');

Route::resource('dictionnary', DictionnaryController::class);
Route::resource('dictionnary.entries', DictionnaryEntryController::class)->shallow();
Route::resource('dictionnaryentry', DictionnaryEntryController::class)->except(['create']);
Route::resource('bank', BankAccountController::class)->except(['show']);
Route::resource('participationtypes', ParticipationTypeController::class)->except(['show']);


Route::post('mailtemplates/duplicate/{template}', [MailTemplateController::class, 'duplicate'])->name('mailtemplates.duplicate');
Route::resource('mailtemplates', MailTemplateController::class);

Route::get('doc-json-api', [ApiController::class, "jsonDoc"])->name('doc-json-api');
