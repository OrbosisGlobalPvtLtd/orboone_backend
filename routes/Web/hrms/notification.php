<?php

use App\Http\Controllers\Web\NotificationC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationC::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationC::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::post('/notifications/{id}/mark-as-read', [NotificationC::class, 'markAsRead'])->name('notifications.mark_as_read');
    Route::delete('/notifications/{id}', [NotificationC::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/{notification}/open', [NotificationC::class, 'open'])->name('notifications.open');
});
