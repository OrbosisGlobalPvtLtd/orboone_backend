<?php

use App\Http\Controllers\Web\HRMS\Employee\EmployeeSelfC;
use App\Http\Controllers\Web\HRMS\Document\EmployeeSelfDocumentC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'module:hrms'])->prefix('hrms/employee')->name('hrms.employee.')->group(function () {
    Route::get('complete-profile', [EmployeeSelfC::class, 'completeProfile'])->name('complete_profile');
    Route::post('complete-profile', [EmployeeSelfC::class, 'storeProfile'])->name('store_profile');
    
    Route::post('ajax-documents/upload', [EmployeeSelfDocumentC::class, 'upload'])->name('documents.upload');
    Route::post('ajax-documents/{id}/replace', [EmployeeSelfDocumentC::class, 'replace'])->name('documents.replace');
    Route::delete('ajax-documents/{id}', [EmployeeSelfDocumentC::class, 'destroy'])->name('documents.destroy');
    
    Route::post('profile/submit-verification', [EmployeeSelfC::class, 'submitVerification'])->name('submit_verification');
    
    Route::get('my-profile', [EmployeeSelfC::class, 'myProfile'])->name('my_profile');
});
