<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use KadirGulec\Newsletter\Http\Controllers\NewsletterController;

Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('web')
    ->name('newsletter.subscribe');

// GET shows a confirmation page so link prefetchers / antivirus scanners
// cannot silently unsubscribe a user by following the email link.
Route::get('newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'showUnsubscribe'])
    ->middleware([ValidateSignature::class, SubstituteBindings::class])
    ->name('newsletter.unsubscribe');

// POST performs the actual unsubscribe. Also serves RFC 8058
// "One-Click" requests from mailbox providers (Gmail, Outlook, ...).
// No CSRF: authentication comes from the signed URL.
Route::post('newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'unsubscribe'])
    ->middleware([ValidateSignature::class, SubstituteBindings::class])
    ->name('newsletter.unsubscribe.post');
