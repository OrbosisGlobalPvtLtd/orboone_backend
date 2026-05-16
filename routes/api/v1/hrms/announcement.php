<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HRMS\Announcement\AnnouncementApiC;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/announcements', [AnnouncementApiC::class, 'index']);
    Route::get('/announcements/{announcement}', [AnnouncementApiC::class, 'show']);
});
