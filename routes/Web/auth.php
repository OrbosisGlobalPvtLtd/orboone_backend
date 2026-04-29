<?php

use App\Http\Controllers\RecruitmentCandidatesController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Web\Auth\LoginC;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

    Route::get('/login', [LoginC::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginC::class, 'login'])->name('login.submit');

    Route::get('/welcome/announcements', [WelcomeController::class, 'announcements'])->name('welcome.announcements');
    Route::get('/welcome/announcements/{announcement}', [WelcomeController::class, 'announcementShow'])->name('welcome.announcements.show');
    Route::get('/welcome/recruitments', [WelcomeController::class, 'recruitments'])->name('welcome.recruitments');
    Route::get('/welcome/recruitments/{recruitment}', [WelcomeController::class, 'recruitmentShow'])->name('welcome.recruitments.show');
});

Route::post('/logout', [LoginC::class, 'logout'])->middleware('auth')->name('logout');

Route::post('/recruitment-candidates', [RecruitmentCandidatesController::class, 'store'])
    ->name('recruitment-candidates.store');