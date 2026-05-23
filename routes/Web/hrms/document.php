<?php

use App\Http\Controllers\Web\HRMS\Document\CompanyDocumentC;
use App\Http\Controllers\Web\HRMS\Document\DocumentDashboardC;
use App\Http\Controllers\Web\HRMS\Document\DocumentTypeC;
use App\Http\Controllers\Web\HRMS\Document\EmployeeDocumentC;
use App\Http\Controllers\Web\HRMS\Document\EmployeeDocumentsC;
use App\Http\Controllers\Web\HRMS\Document\EmployeePolicyC;
use App\Http\Controllers\Web\HRMS\Document\HRDocumentC;
use App\Http\Controllers\Web\HRMS\Employee\EmployeeC;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Secure Private Document Preview
|--------------------------------------------------------------------------
| Ensure that both Employee and Admin can view documents in the browser.
*/

Route::middleware(['auth', 'module:hrms'])
    ->prefix('hrms')
    ->name('hrms.')
    ->group(function () {
        Route::get('/employee/file/{path}', function ($path) {
            $user = auth()->user();
            
            // Authorization: Parse employee_id from directory structure: "employee-documents/ID/file"
            if (preg_match('/employee-documents[\\/](\d+)/', $path, $matches)) {
                $employeeId = (int)$matches[1];
                $employee = DB::table('employees_new')->where('id', $employeeId)->first();
                
                if ($employee) {
                    $hasPermission = false;
                    
                    if ($employee->user_id == $user->id) {
                        $hasPermission = true;
                    } else {
                        $userRole = DB::table('roles')
                            ->join('user_roles', 'roles.id', '=', 'user_roles.role_id')
                            ->where('user_roles.user_id', $user->id)
                            ->whereIn('roles.slug', ['super_admin', 'admin', 'hr_admin', 'finance_admin', 'operations_admin'])
                            ->exists();
                        if ($userRole) {
                            $hasPermission = true;
                        }
                    }
                    
                    if (!$hasPermission) {
                        abort(403, 'Unauthorized access to employee document.');
                    }
                }
            }

            $filePath = storage_path('app/private/' . $path);

            if (! file_exists($filePath)) {
                abort(404);
            }

            $mime = mime_content_type($filePath) ?: 'application/octet-stream';

            return response()->file($filePath, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]);
        })
            ->where('path', '.*')
            ->name('documents.file');
    });


Route::middleware(['auth', 'check.access'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Document Management Dashboard
        |--------------------------------------------------------------------------
        */
        Route::prefix('documents')
            ->name('documents.')
            ->group(function () {

                Route::get('/dashboard', [DocumentDashboardC::class, 'index'])
                    ->middleware('permission:documents.compliance.view')
                    ->name('dashboard');

                Route::get('/pending-verification', [HRDocumentC::class, 'index'])
                    ->middleware('permission:documents.verification.view')
                    ->name('pending');

                Route::get('/verification', [HRDocumentC::class, 'index'])
                    ->middleware('permission:documents.verification.view')
                    ->name('verification.index');

                Route::post('/bulk-verify', [HRDocumentC::class, 'bulkVerify'])
                    ->middleware('permission:documents.verification.approve')
                    ->name('bulk_verify');

                Route::get('/expiring', [HRDocumentC::class, 'expiring'])
                    ->middleware('permission:employee_documents.view')
                    ->name('expiring');

                Route::get('/types', [DocumentTypeC::class, 'index'])
                    ->middleware('permission:documents.types.manage')
                    ->name('types.index');

                Route::post('/types', [DocumentTypeC::class, 'store'])
                    ->middleware('permission:company_documents.manage')
                    ->name('types.store');

                Route::put('/types/{type}', [DocumentTypeC::class, 'update'])
                    ->middleware('permission:company_documents.manage')
                    ->name('types.update');

                Route::delete('/types/{type}', [DocumentTypeC::class, 'destroy'])
                    ->middleware('permission:company_documents.manage')
                    ->name('types.destroy');

                Route::patch('/types/{type}/toggle-status', [DocumentTypeC::class, 'toggleStatus'])
                    ->name('types.toggle-status');

                Route::get('/policies', [CompanyDocumentC::class, 'index'])
                    ->middleware('permission:documents.company.view')
                    ->name('policies.index');

                Route::post('/policies', [CompanyDocumentC::class, 'store'])
                    ->middleware('permission:company_documents.manage')
                    ->name('policies.store');

                Route::put('/policies/{policy}', [CompanyDocumentC::class, 'update'])
                    ->middleware('permission:company_documents.manage')
                    ->name('policies.update');

                Route::delete('/policies/{policy}', [CompanyDocumentC::class, 'destroy'])
                    ->middleware('permission:company_documents.manage')
                    ->name('policies.destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Admin / HR Employee Documents
        |--------------------------------------------------------------------------
        */
        Route::prefix('employee-documents')
            ->name('documents.employee.')
            ->group(function () {

                Route::get('/', [EmployeeDocumentsC::class, 'index'])
                    ->middleware('permission:employee_documents.view')
                    ->name('index');

                Route::get('/pending-profiles', [EmployeeC::class, 'pendingProfiles'])
                    ->middleware('permission:employee.view')
                    ->name('pending_profiles');

                Route::post('/global', [EmployeeDocumentsC::class, 'storeGlobal'])
                    ->middleware('permission:company_documents.manage')
                    ->name('store_global');

                Route::get('/{employee}', [EmployeeDocumentsC::class, 'show'])
                    ->middleware('permission:employee_documents.view')
                    ->name('show');

                Route::post('/{employee}', [EmployeeDocumentsC::class, 'store'])
                    ->middleware('permission:company_documents.manage')
                    ->name('store');

                Route::post('/{document}/verify', [EmployeeDocumentsC::class, 'approve'])
                    ->middleware('permission:company_documents.manage')
                    ->name('verify');

                Route::post('/{document}/approve', [EmployeeDocumentsC::class, 'approve'])
                    ->middleware('permission:company_documents.manage')
                    ->name('approve');

                Route::post('/{employee}/{documentType}/upload-from-profile', [EmployeeDocumentsC::class, 'uploadFromProfile'])
                    ->middleware('permission:company_documents.manage')
                    ->name('upload_from_profile');

                Route::post('/{document}/reject', [EmployeeDocumentsC::class, 'reject'])
                    ->middleware('permission:company_documents.manage')
                    ->name('reject');

                Route::get('/{document}/download', [EmployeeDocumentsC::class, 'download'])
                    ->middleware('permission:documents.verification.view')
                    ->name('download');
            });

        /*
        |--------------------------------------------------------------------------
        | HR Verification Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('hr/documents')
            ->name('documents.hr.')
            ->group(function () {

                Route::get('/', [HRDocumentC::class, 'index'])
                    ->middleware('permission:documents.verification.view')
                    ->name('index');

                Route::get('/employees/{user}', [HRDocumentC::class, 'show'])
                    ->middleware('permission:employee_documents.view')
                    ->name('show');

                Route::post('/{id}/verify', [HRDocumentC::class, 'approve'])
                    ->middleware('permission:company_documents.manage')
                    ->name('verify');

                Route::post('/{id}/approve', [HRDocumentC::class, 'approve'])
                    ->middleware('permission:company_documents.manage')
                    ->name('approve');

                Route::post('/{id}/reject', [HRDocumentC::class, 'reject'])
                    ->middleware('permission:company_documents.manage')
                    ->name('reject');

                Route::post('/bulk-verify', [HRDocumentC::class, 'bulkVerify'])
                    ->middleware('permission:company_documents.manage')
                    ->name('bulk_verify');

                Route::post('/employee/{employee}/verify-all', [HRDocumentC::class, 'verifyEmployee'])
                    ->middleware('permission:company_documents.manage')
                    ->name('verify_employee');

                Route::get('/expiring/list', [HRDocumentC::class, 'expiring'])
                    ->middleware('permission:employee_documents.view')
                    ->name('expiring');
            });

        /*
        |--------------------------------------------------------------------------
        | Employee Company Policies
        |--------------------------------------------------------------------------
        */
        Route::get('/employee/policies', [EmployeePolicyC::class, 'index'])
            ->middleware('permission:documents.company.view|documents_self.view')
            ->name('documents.policies.self');

        Route::redirect('/employee/policie', '/employee/policies');
    });

/*
|--------------------------------------------------------------------------
| Employee Self Documents
|--------------------------------------------------------------------------
| Kept outside the Admin middleware because employees require access.
*/
Route::middleware(['auth', 'module:hrms'])
    ->prefix('hrms')
    ->name('hrms.')
    ->group(function () {

        Route::prefix('employee/documents')
            ->name('documents.self.')
            ->group(function () {

                Route::get('/', [EmployeeDocumentC::class, 'index'])
                    ->middleware('permission:documents.upload.self|documents_self.view')
                    ->name('index');

                Route::post('/upload', [EmployeeDocumentC::class, 'store'])
                    ->middleware('permission:documents.upload.self|documents_self.upload')
                    ->name('upload');

                Route::post('/submit-verification', [EmployeeDocumentC::class, 'submitForVerification'])
                    ->middleware('permission:documents_self.upload')
                    ->name('submit_verification');

                Route::post('/{id}/replace', [EmployeeDocumentC::class, 'replace'])
                    ->middleware('permission:documents_self.upload')
                    ->name('replace');

                Route::delete('/{id}', [EmployeeDocumentC::class, 'destroy'])
                    ->middleware('permission:documents_self.upload')
                    ->name('destroy');
            });

        Route::redirect('/employee/document', '/hrms/employee/documents');
    });
