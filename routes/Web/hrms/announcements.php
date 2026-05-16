<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HRMS\Announcement\AnnouncementsC;

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Custom Announcement Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/announcements/print', [AnnouncementsC::class, 'print'])
        ->name('announcements.print');

    Route::patch('/announcements/{announcement}/toggle-status', [AnnouncementsC::class, 'toggleStatus'])
        ->name('announcements.toggle-status');


    /*
    |--------------------------------------------------------------------------
    | Resource Announcement Routes
    |--------------------------------------------------------------------------
    */



    Route::get('/announcements', [AnnouncementsC::class, 'index'])
        ->name('announcements.index');

    Route::middleware(['employee.user'])->group(function () {
        Route::get('/my-announcements', [AnnouncementsC::class, 'employeeIndex'])
            ->name('employee.announcements.index');
        Route::get('/my-announcements/{announcement}', [AnnouncementsC::class, 'employeeShow'])
            ->name('employee.announcements.show');
    });
    Route::get('/announcements/create', [AnnouncementsC::class, 'create'])
        ->name('announcements.create');

    Route::post('/announcements', [AnnouncementsC::class, 'store'])
        ->name('announcements.store');

    Route::get('/announcements/{announcement}', [AnnouncementsC::class, 'show'])
        ->name('announcements.show');

    Route::get('/announcements/{announcement}/edit', [AnnouncementsC::class, 'edit'])
        ->name('announcements.edit');

    Route::put('/announcements/{announcement}', [AnnouncementsC::class, 'update'])
        ->name('announcements.update');

    Route::delete('/announcements/{announcement}', [AnnouncementsC::class, 'destroy'])
        ->name('announcements.destroy');
});
