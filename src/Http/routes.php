<?php

use Illuminate\Support\Facades\Route;
use Seiger\sSeo\Controllers\sSeoController;

Route::middleware('mgr')->prefix('sseo/')->name('sSeo.')->group(function () {
    Route::get('dashboard', [sSeoController::class, 'dashboard'])->name('dashboard');
    Route::get('redirects', [sSeoController::class, 'redirects'])->name('redirects');
    Route::post('aredirect', [sSeoController::class, 'addRedirect'])->name('aredirect');
    Route::delete('dredirect', [sSeoController::class, 'delRedirect'])->name('dredirect');
    Route::get('templates', [sSeoController::class, 'templates'])->name('templates');
    Route::post('templates', [sSeoController::class, 'updateTemplates'])->name('utemplates');
    Route::get('robots', [sSeoController::class, 'robots'])->name('robots');
    Route::post('robots', [sSeoController::class, 'updateRobots'])->name('urobots');
    Route::get('configure', [sSeoController::class, 'configure'])->name('configure');
    Route::post('configure', [sSeoController::class, 'updateConfigure'])->name('uconfigure');
    Route::post('modulesave', [sSeoController::class, 'updateModuleFields'])->name('modulesave');
});
