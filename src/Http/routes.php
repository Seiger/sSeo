<?php

use Illuminate\Support\Facades\Route;
use Seiger\sSeo\Controllers\sSeoController;

Route::middleware('mgr')->prefix('sseo/')->name('sSeo.')->group(function () {
    Route::get('/', [sSeoController::class, 'index'])->name('index');
});
