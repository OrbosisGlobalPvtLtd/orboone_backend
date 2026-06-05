<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HRMS\DocumentGeneration\DocumentTemplateC;
use App\Http\Controllers\Web\HRMS\DocumentGeneration\GeneratedDocumentC;

Route::middleware(['auth'])->group(function () {
    Route::prefix('hrms/document-generation')->name('hrms.document-generation.')->group(function () {

        // Dashboard
        Route::get('/', [GeneratedDocumentC::class, 'dashboard'])->name('dashboard')->middleware('permission:document_generation.view');

        // Employee Document Data for Autofill
        Route::get('/employees/{employee}/document-data', [GeneratedDocumentC::class, 'employeeDocumentData'])->name('employee-document-data')->middleware('permission:document_generation.view');

        // Templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [DocumentTemplateC::class, 'index'])->name('index')->middleware('permission:document_generation.view');
            Route::post('/', [DocumentTemplateC::class, 'store'])->name('store')->middleware('permission:document_generation.template_create');
            Route::put('/{id}', [DocumentTemplateC::class, 'update'])->name('update')->middleware('permission:document_generation.template_edit');
            Route::post('/{id}/clone', [DocumentTemplateC::class, 'clone'])->name('clone')->middleware('permission:document_generation.template_create');
            Route::post('/{id}/toggle-archive', [DocumentTemplateC::class, 'toggleArchive'])->name('toggle-archive')->middleware('permission:document_generation.template_edit');
            Route::delete('/{id}', [DocumentTemplateC::class, 'destroy'])->name('destroy')->middleware('permission:document_generation.delete');
        });

        // Generated Documents
        Route::prefix('generated')->name('generated.')->group(function () {
            Route::get('/', [GeneratedDocumentC::class, 'index'])->name('index')->middleware('permission:document_generation.view');
            Route::get('/create', [GeneratedDocumentC::class, 'create'])->name('create')->middleware('permission:document_generation.generate');
            Route::post('/live-preview', [GeneratedDocumentC::class, 'preview'])->name('preview')->middleware('permission:document_generation.preview');
            Route::post('/', [GeneratedDocumentC::class, 'store'])->name('store')->middleware('permission:document_generation.generate');
            Route::get('/{id}', [GeneratedDocumentC::class, 'show'])->name('show')->middleware('permission:document_generation.view');
            Route::get('/{id}/download', [GeneratedDocumentC::class, 'download'])->name('download')->middleware('permission:document_generation.download');
            Route::get('/{id}/stream', [GeneratedDocumentC::class, 'streamPdf'])->name('stream')->middleware('permission:document_generation.preview');
            Route::post('/{id}/email', [GeneratedDocumentC::class, 'email'])->name('email')->middleware('permission:document_generation.email');
            Route::post('/{id}/review', [GeneratedDocumentC::class, 'review'])->name('review')->middleware('permission:document_generation.review');
            Route::post('/{id}/cancel', [GeneratedDocumentC::class, 'cancel'])->name('cancel')->middleware('permission:document_generation.delete');
            Route::post('/{id}/delete', [GeneratedDocumentC::class, 'destroy'])->name('delete')->middleware('permission:document_generation.delete');
            Route::post('/{id}/regenerate', [GeneratedDocumentC::class, 'regenerate'])->name('regenerate')->middleware('permission:document_generation.generate');
        });

        // Employee Self
        Route::prefix('self')->name('self.')->group(function () {
            Route::get('/', [GeneratedDocumentC::class, 'selfIndex'])->name('index');
            Route::get('/{id}/download', [GeneratedDocumentC::class, 'selfDownload'])->name('download');
            Route::get('/{id}/view', [GeneratedDocumentC::class, 'selfView'])->name('view');
        });
    });
});
