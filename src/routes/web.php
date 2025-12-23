<?php

use Illuminate\Support\Facades\Route;
use KadirGulec\Newsletter\Http\Controllers\NewsletterController;

Route::group(['middleware' => ['web']], function () {

    Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe'])
        ->name('newsletter.subscribe');

    // Signed Route for Unsubscribe
    Route::get('newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'unsubscribe'])
        ->name('newsletter.unsubscribe');

});