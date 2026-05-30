<?php

use App\Http\Controllers\Web\HRMS\Employee\RecruitmentCandidatesController;
use App\Http\Controllers\Web\Auth\EmployeePasswordSetupController;
use App\Http\Controllers\Web\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Auth\WelcomeController;
use App\Http\Controllers\Web\Auth\LoginC;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

    Route::get('/login', [LoginC::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginC::class, 'login'])->name('login.submit');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.otp.send');
    Route::get('/forgot-password/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.otp.form');
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset.update');

    Route::get('/welcome/announcements', [WelcomeController::class, 'announcements'])->name('welcome.announcements');
    Route::get('/welcome/announcements/{announcement}', [WelcomeController::class, 'announcementShow'])->name('welcome.announcements.show');
    Route::get('/welcome/recruitments', [WelcomeController::class, 'recruitments'])->name('welcome.recruitments');
    Route::get('/welcome/recruitments/{recruitment}', [WelcomeController::class, 'recruitmentShow'])->name('welcome.recruitments.show');
});

Route::get('/employee-password/setup/{token}', [EmployeePasswordSetupController::class, 'show'])
    ->middleware('guest')
    ->name('employee.password.setup');
Route::post('/employee-password/setup/{token}', [EmployeePasswordSetupController::class, 'update'])
    ->middleware('guest')
    ->name('employee.password.setup.update');

Route::post('/logout', [LoginC::class, 'logout'])->middleware('auth')->name('logout');

Route::post('/recruitment-candidates', [RecruitmentCandidatesController::class, 'store'])
    ->name('recruitment-candidates.store');
