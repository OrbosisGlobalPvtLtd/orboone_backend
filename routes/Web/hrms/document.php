<?php

use App\Http\Controllers\Web\HRMS\Document\CompanyDocumentC;
use App\Http\Controllers\Web\HRMS\Document\EmployeeDocumentC;
use App\Http\Controllers\Web\HRMS\Document\EmployeeDocumentsC;
use App\Http\Controllers\Web\HRMS\Document\EmployeePolicyC;
use App\Http\Controllers\Web\HRMS\Document\HRDocumentC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->name('hrms.')
    ->group(function () {
        Route::get('/employee/file/{path}', function ($path) {
            $filePath = storage_path('app/private/'.$path);

            if (! file_exists($filePath)) {
                abort(404);
            }

            return response()->file($filePath);
        })->where('path', '.*')->name('documents.file');

        Route::prefix('employee-documents')
            ->name('documents.employee.')
            ->group(function () {
                Route::get('/', [EmployeeDocumentsC::class, 'index'])
                    ->middleware('permission:employee_documents.view')
                    ->name('index');

                Route::post('/global', [EmployeeDocumentsC::class, 'storeGlobal'])
                    ->middleware('permission:company_documents.manage')
                    ->name('store_global');

                Route::get('/{employee}', [EmployeeDocumentsC::class, 'show'])
                    ->middleware('permission:employee_documents.view')
                    ->name('show');

                Route::post('/{employee}', [EmployeeDocumentsC::class, 'store'])
                    ->middleware('permission:company_documents.manage')
                    ->name('store');

                Route::post('/{document}/approve', [EmployeeDocumentsC::class, 'approve'])
                    ->middleware('permission:company_documents.manage')
                    ->name('approve');

                Route::post('/{document}/reject', [EmployeeDocumentsC::class, 'reject'])
                    ->middleware('permission:company_documents.manage')
                    ->name('reject');
            });

        Route::get('/employee/documents', [EmployeeDocumentC::class, 'index'])
            ->middleware('permission:documents_self.view')
            ->name('documents.self.index');

        Route::redirect('/employee/document', '/hrms/employee/documents');

        Route::post('/employee/documents/upload', [EmployeeDocumentC::class, 'store'])
            ->middleware('permission:documents_self.upload')
            ->name('documents.self.upload');

        Route::delete('/employee/documents/{id}', [EmployeeDocumentC::class, 'destroy'])
            ->middleware('permission:documents_self.upload')
            ->name('documents.self.destroy');

        Route::get('/hr/documents', [HRDocumentC::class, 'index'])
            ->middleware('permission:employee_documents.view')
            ->name('documents.hr.index');

        Route::get('/hr/employees/{user}/documents', [HRDocumentC::class, 'show'])
            ->middleware('permission:employee_documents.view')
            ->name('documents.hr.show');

        Route::post('/hr/documents/{id}/approve', [HRDocumentC::class, 'approve'])
            ->middleware('permission:company_documents.manage')
            ->name('documents.hr.approve');

        Route::post('/hr/documents/{id}/reject', [HRDocumentC::class, 'reject'])
            ->middleware('permission:company_documents.manage')
            ->name('documents.hr.reject');

        Route::resource('hr/policies', CompanyDocumentC::class)
            ->names('documents.policies')
            ->middleware('permission:company_documents.manage');

        Route::get('/employee/policies', [EmployeePolicyC::class, 'index'])
            ->middleware('permission:documents_self.view')
            ->name('documents.policies.self');

        Route::redirect('/employee/policie', '/hrms/employee/policies');
    });
