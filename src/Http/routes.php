<?php

use Illuminate\Support\Facades\Route;
use Seiger\sSeo\Controllers\sSeoController;

Route::middleware('mgr')->prefix('sseo')->name('sSeo.')->group(function () {
    Route::get('/', [sSeoController::class, 'redirects'])->name('redirects');
    Route::get('/configure', [sSeoController::class, 'index'])->name('index');
    Route::post('/redirects', [sSeoController::class, 'updateRedirects'])->name('update-redirects');
    Route::post('/configure', [sSeoController::class, 'updateConfigure'])->name('update-configure');
});
