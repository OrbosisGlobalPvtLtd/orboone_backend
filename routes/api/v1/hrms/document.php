<?php

use App\Http\Controllers\Api\V1\HRMS\Document\MyDocumentController;
use App\Http\Controllers\Api\V1\HRMS\Document\PolicyDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])
    ->prefix('hrms/documents')
    ->name('api.hrms.documents.')
    ->group(function () {

        Route::get('/required', [MyDocumentController::class, 'requiredDocuments'])
            ->name('required');

        Route::get('/my', [MyDocumentController::class, 'myDocuments'])
            ->name('my');

        Route::get('/status', [MyDocumentController::class, 'status'])
            ->name('status');

        Route::get('/policies', [PolicyDocumentController::class, 'index'])
            ->name('policies');

        Route::post('/upload', [MyDocumentController::class, 'upload'])
            ->name('upload');

        Route::post('/submit-for-verification', [MyDocumentController::class, 'submitForVerification'])
            ->name('submit_verification');

        Route::post('/submit-verification', [MyDocumentController::class, 'submitVerification'])
            ->name('submit_verification_alt');

        Route::post('/{document}/replace', [MyDocumentController::class, 'replace'])
            ->whereNumber('document')
            ->name('replace');

        Route::get('/generated/{id}/download', [MyDocumentController::class, 'downloadGeneratedDocument'])
            ->name('generated.download')
            ->whereNumber('id');

        Route::delete('/{document}', [MyDocumentController::class, 'destroy'])
            ->whereNumber('document')
            ->name('destroy');
    });
