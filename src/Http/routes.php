<?php

use Illuminate\Support\Facades\Route;
use Seiger\sSeo\Controllers\sSeoController;

Route::middleware('mgr')->prefix('sseo')->name('sSeo.')->group(function () {
    Route::get('/redirects', [sSeoController::class, 'redirects'])->name('redirects');
    Route::get('/robots', [sSeoController::class, 'robots'])->name('robots');
    Route::get('/configure', [sSeoController::class, 'configure'])->name('configure');
    Route::post('/redirects', [sSeoController::class, 'updateRedirects'])->name('update-redirects');
    Route::post('/robots', [sSeoController::class, 'updateRobots'])->name('update-robots');
    Route::post('/configure', [sSeoController::class, 'updateConfigure'])->name('update-configure');
});
