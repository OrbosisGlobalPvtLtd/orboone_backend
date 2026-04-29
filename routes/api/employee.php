<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Employee\EmployeeDocumentController;
use App\Http\Controllers\Api\Document\MyDocumentController;
use App\Http\Controllers\Api\Document\PolicyDocumentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/employees/{id}/documents', [EmployeeDocumentController::class, 'uploadEmployeeDocument']);
    Route::get('/employees/{id}/documents', [EmployeeDocumentController::class, 'listEmployeeDocuments']);
    Route::post('/employees/{id}/documents/{docId}/verify', [EmployeeDocumentController::class, 'verifyEmployeeDocument']);
    Route::delete('/employees/{id}/documents/{docId}', [EmployeeDocumentController::class, 'deleteEmployeeDocument']);

    Route::get('/my/documents', [MyDocumentController::class, 'myDocuments']);
    Route::post('/uploaded/documents', [MyDocumentController::class, 'uploadMyDocument']);

    Route::get('/policies', [PolicyDocumentController::class, 'listPolicyDocuments']);
    Route::post('/policies', [PolicyDocumentController::class, 'uploadPolicyDocument']);
});