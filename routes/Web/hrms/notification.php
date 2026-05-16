<?php

use App\Http\Controllers\Web\NotificationC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationC::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationC::class, 'open'])->name('notifications.open');
});
