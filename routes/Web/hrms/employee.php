<?php

use App\Http\Controllers\AssetAllocationController;
use App\Http\Controllers\CompanyDocumentController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmployeeDocumentsController;
use App\Http\Controllers\EmployeePolicyController;
use App\Http\Controllers\EmployeeScoresController;
use App\Http\Controllers\HRDocumentController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\RecruitmentsController;
use App\Http\Controllers\TaskmanagementController;
use App\Http\Controllers\Web\HRMS\DepartmentC;
use App\Http\Controllers\Web\HRMS\DesignationC;
use App\Http\Controllers\Web\HRMS\Employee\EmployeeC;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRMS Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->group(function () {
        Route::get('/employee/file/{path}', function ($path) {
            $filePath = storage_path('app/private/'.$path);

            if (! file_exists($filePath)) {
                abort(404);
            }

            return response()->file($filePath);
        })->where('path', '.*')->name('employee.file');
        /*
        |--------------------------------------------------------------------------
        | Employee Management - New Clean HRMS Flow
        |--------------------------------------------------------------------------
        */

        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeC::class, 'index'])
                ->middleware('permission:employees.view')
                ->name('index');

            Route::get('/create', [EmployeeC::class, 'create'])
                ->middleware('permission:employees.create')
                ->name('create');

            Route::post('/', [EmployeeC::class, 'store'])
                ->middleware('permission:employees.create')
                ->name('store');

            Route::get('/pending-profiles', [EmployeeC::class, 'pendingProfiles'])
                ->middleware('permission:employees.view')
                ->name('pending-profiles');

            Route::get('/probation-internship', [EmployeeC::class, 'probationInternship'])
                ->middleware('permission:employees.view')
                ->name('probation-internship');

            Route::get('/exit', [EmployeeC::class, 'exitEmployees'])
                ->middleware('permission:employees.view')
                ->name('exit');

            Route::get('/reporting-structure', [EmployeeC::class, 'reportingStructure'])
                ->middleware('permission:employees.view')
                ->name('reporting-structure');

            Route::get('/export', [EmployeeC::class, 'print'])
                ->middleware('permission:employees.view')
                ->name('export');

            Route::get('/{employee}/edit', [EmployeeC::class, 'edit'])
                ->middleware('permission:employees.update')
                ->name('edit');

            Route::put('/{employee}', [EmployeeC::class, 'update'])
                ->middleware('permission:employees.update')
                ->name('update');

            Route::get('/{employee}/complete-profile', [EmployeeC::class, 'completeProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.complete');

            Route::post('/{employee}/complete-profile', [EmployeeC::class, 'storeProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.store');

            Route::get('/{employee}/profile-view', [EmployeeC::class, 'viewProfile'])
                ->middleware('permission:employees.view')
                ->name('profile.view');

            Route::get('/{employee}/profile-edit', [EmployeeC::class, 'editProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.edit');

            Route::post('/{employee}/profile-update', [EmployeeC::class, 'updateProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.update');

            Route::post('/{employee}/profile-approve', [EmployeeC::class, 'approveProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.approve');

            Route::post('/{employee}/profile-reject', [EmployeeC::class, 'rejectProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.reject');
        });

        /*
        |--------------------------------------------------------------------------
        | Legacy Employee Route Names
        | Old blade/menu compatibility ke liye rakhe hain.
        |--------------------------------------------------------------------------
        */

        Route::get('/employees-data', [EmployeeC::class, 'index'])
            ->middleware('permission:employees.view')
            ->name('employees-data');

        Route::get('/employees-data/create', [EmployeeC::class, 'create'])
            ->middleware('permission:employees.create')
            ->name('employees-data.create');

        Route::post('/employees-data', [EmployeeC::class, 'store'])
            ->middleware('permission:employees.create')
            ->name('employees-data.store');

        Route::get('/employees-data/pending-profiles', [EmployeeC::class, 'pendingProfiles'])
            ->middleware('permission:employees.view')
            ->name('employees-data.pending-profiles');

        Route::get('/employees-data/probation-internship', [EmployeeC::class, 'probationInternship'])
            ->middleware('permission:employees.view')
            ->name('employees-data.probation-internship');

        Route::get('/employees-data/exit-employees', [EmployeeC::class, 'exitEmployees'])
            ->middleware('permission:employees.view')
            ->name('employees-data.exit');

        Route::get('/employees-data/reporting-structure', [EmployeeC::class, 'reportingStructure'])
            ->middleware('permission:employees.view')
            ->name('employees-data.reporting-structure');

        Route::get('/employees-data/print', [EmployeeC::class, 'print'])
            ->middleware('permission:employees.view')
            ->name('employees-data.print');

        Route::get('/employees-data/{employee}/edit', [EmployeeC::class, 'edit'])
            ->middleware('permission:employees.update')
            ->name('employees-data.edit');

        Route::put('/employees-data/{employee}', [EmployeeC::class, 'update'])
            ->middleware('permission:employees.update')
            ->name('employees-data.update');

        Route::get('/employees-data/{employee}/complete-profile', [EmployeeC::class, 'completeProfile'])
            ->middleware('permission:employees.update')
            ->name('employees.profile.complete');

        Route::post('/employees-data/{employee}/complete-profile', [EmployeeC::class, 'storeProfile'])
            ->middleware('permission:employees.update')
            ->name('employees.profile.store');

        /*
        |--------------------------------------------------------------------------
        | Department & Designation - New Routes
        |--------------------------------------------------------------------------
        */

        Route::get('/departments', [DepartmentC::class, 'index'])
            ->middleware('permission:departments.manage')
            ->name('departments.index');

        Route::get('/designations', [DesignationC::class, 'index'])
            ->middleware('permission:designations.manage')
            ->name('designations.index');

        /*
        |--------------------------------------------------------------------------
        | Old Department Routes
        |--------------------------------------------------------------------------
        */

        Route::get('/departments-data', [DepartmentsController::class, 'index'])
            ->middleware('permission:departments.manage')
            ->name('departments-data');

        Route::get('/departments-data/create', [DepartmentsController::class, 'create'])
            ->middleware('permission:departments.manage')
            ->name('departments-data.create');

        Route::post('/departments-data', [DepartmentsController::class, 'store'])
            ->middleware('permission:departments.manage')
            ->name('departments-data.store');

        Route::get('/departments-data/print', [DepartmentsController::class, 'print'])
            ->middleware('permission:departments.manage')
            ->name('departments-data.print');

        Route::get('/departments-data/{department}', [DepartmentsController::class, 'show'])
            ->middleware('permission:departments.manage')
            ->name('departments-data.show');

        Route::get('/departments-data/{department}/edit', [DepartmentsController::class, 'edit'])
            ->middleware('permission:departments.manage')
            ->name('departments-data.edit');

        Route::put('/departments-data/{department}', [DepartmentsController::class, 'update'])
            ->middleware('permission:departments.manage')
            ->name('departments-data.update');

        Route::delete('/departments-data/{department}', [DepartmentsController::class, 'destroy'])
            ->middleware('permission:departments.manage')
            ->name('departments-data.destroy');

        /*
        |--------------------------------------------------------------------------
        | Positions / Designations Old Routes
        |--------------------------------------------------------------------------
        */

        Route::get('/positions-data', [PositionsController::class, 'index'])
            ->middleware('permission:designations.manage')
            ->name('positions-data');

        Route::get('/positions-data/create', [PositionsController::class, 'create'])
            ->middleware('permission:designations.manage')
            ->name('positions-data.create');

        Route::post('/positions-data', [PositionsController::class, 'store'])
            ->middleware('permission:designations.manage')
            ->name('positions-data.store');

        Route::get('/positions-data/print', [PositionsController::class, 'print'])
            ->middleware('permission:designations.manage')
            ->name('positions-data.print');

        Route::get('/positions-data/{position}', [PositionsController::class, 'show'])
            ->middleware('permission:designations.manage')
            ->name('positions-data.show');

        Route::get('/positions-data/{position}/edit', [PositionsController::class, 'edit'])
            ->middleware('permission:designations.manage')
            ->name('positions-data.edit');

        Route::put('/positions-data/{position}', [PositionsController::class, 'update'])
            ->middleware('permission:designations.manage')
            ->name('positions-data.update');

        Route::delete('/positions-data/{position}', [PositionsController::class, 'destroy'])
            ->middleware('permission:designations.manage')
            ->name('positions-data.destroy');

        /*
        |--------------------------------------------------------------------------
        | Asset Allocation
        |--------------------------------------------------------------------------
        */

        Route::resource('asset-allocations', AssetAllocationController::class)
            ->middleware('permission:asset_allocation.manage');

        /*
        |--------------------------------------------------------------------------
        | Employee Performance Score
        |--------------------------------------------------------------------------
        */

        Route::get('/employees-performance-score', [EmployeeScoresController::class, 'index'])
            ->middleware('permission:employees.view')
            ->name('employees-performance-score');

        Route::get('/employees-performance-score/create', [EmployeeScoresController::class, 'create'])
            ->middleware('permission:employees.update')
            ->name('employees-performance-score.create');

        Route::post('/employees-performance-score', [EmployeeScoresController::class, 'store'])
            ->middleware('permission:employees.update')
            ->name('employees-performance-score.store');

        Route::get('/employees-performance-score/print', [EmployeeScoresController::class, 'print'])
            ->middleware('permission:employees.view')
            ->name('employees-performance-score.print');

        Route::get('/employees-performance-score/{employeeScore}', [EmployeeScoresController::class, 'show'])
            ->middleware('permission:employees.view')
            ->name('employees-performance-score.show');

        Route::get('/employees-performance-score/{employeeScore}/edit', [EmployeeScoresController::class, 'edit'])
            ->middleware('permission:employees.update')
            ->name('employees-performance-score.edit');

        Route::put('/employees-performance-score/{employeeScore}', [EmployeeScoresController::class, 'update'])
            ->middleware('permission:employees.update')
            ->name('employees-performance-score.update');

        Route::delete('/employees-performance-score/{employeeScore}', [EmployeeScoresController::class, 'destroy'])
            ->middleware('permission:employees.update')
            ->name('employees-performance-score.destroy');

        /*
        |--------------------------------------------------------------------------
        | Recruitment
        |--------------------------------------------------------------------------
        */

        Route::get('/recruitments', [RecruitmentsController::class, 'index'])
            ->name('recruitments');

        Route::get('/recruitments/create', [RecruitmentsController::class, 'create'])
            ->name('recruitments.create');

        Route::post('/recruitments', [RecruitmentsController::class, 'store'])
            ->name('recruitments.store');

        Route::get('/recruitments/print', [RecruitmentsController::class, 'print'])
            ->name('recruitments.print');

        Route::get('/recruitments/{recruitment}', [RecruitmentsController::class, 'show'])
            ->name('recruitments.show');

        Route::get('/recruitments/{recruitment}/edit', [RecruitmentsController::class, 'edit'])
            ->name('recruitments.edit');

        Route::put('/recruitments/{recruitment}', [RecruitmentsController::class, 'update'])
            ->name('recruitments.update');

        Route::delete('/recruitments/{recruitment}', [RecruitmentsController::class, 'destroy'])
            ->name('recruitments.destroy');

        /*
        |--------------------------------------------------------------------------
        | Task Management
        |--------------------------------------------------------------------------
        */

        Route::get('/task_management', [TaskmanagementController::class, 'task_management'])
            ->name('task_management');

        Route::get('/add_task', [TaskmanagementController::class, 'store_task'])
            ->name('pages.add_task');

        Route::post('/add_task', [TaskmanagementController::class, 'add_task'])
            ->name('pages.add_task.store');

        Route::get('/edit_task/{id}', [TaskmanagementController::class, 'edit_task'])
            ->name('pages.edit_task');

        Route::post('/update_task/{id}', [TaskmanagementController::class, 'update'])
            ->name('pages.update_task');

        Route::delete('/delete_task/{id}', [TaskmanagementController::class, 'destroy'])
            ->name('pages.delete_task');

        Route::get('/task_print', [TaskmanagementController::class, 'task_print'])
            ->name('pages.task_print');

        Route::get('/my-tasks', [TaskmanagementController::class, 'myTasks'])
            ->name('employee.my_tasks');

        /*
        |--------------------------------------------------------------------------
        | Employee Documents - Admin Side
        |--------------------------------------------------------------------------
        */

        Route::get('/employee-documents', [EmployeeDocumentsController::class, 'index'])
            ->middleware('permission:employee_documents.view')
            ->name('employee-documents.index');

        Route::post('/employee-documents/global', [EmployeeDocumentsController::class, 'storeGlobal'])
            ->middleware('permission:company_documents.manage')
            ->name('employee-documents.store-global');

        Route::get('/employee-documents/{employee}', [EmployeeDocumentsController::class, 'show'])
            ->middleware('permission:employee_documents.view')
            ->name('employee-documents.show');

        Route::post('/employee-documents/{employee}', [EmployeeDocumentsController::class, 'store'])
            ->middleware('permission:company_documents.manage')
            ->name('employee-documents.store');

        Route::post('/employee-documents/{document}/approve', [EmployeeDocumentsController::class, 'approve'])
            ->middleware('permission:company_documents.manage')
            ->name('employee-documents.approve');

        Route::post('/employee-documents/{document}/reject', [EmployeeDocumentsController::class, 'reject'])
            ->middleware('permission:company_documents.manage')
            ->name('employee-documents.reject');

        /*
        |--------------------------------------------------------------------------
        | Employee Documents - Employee Side
        |--------------------------------------------------------------------------
        */

        Route::get('/employee/document', [EmployeeDocumentController::class, 'index'])
            ->middleware('permission:documents_self.view')
            ->name('employee.hr-documents');

        Route::get('/employee/documents', [EmployeeDocumentController::class, 'index'])
            ->middleware('permission:documents_self.view')
            ->name('employee.documents-index');

        Route::post('/employee/documents/upload', [EmployeeDocumentController::class, 'store'])
            ->middleware('permission:documents_self.upload')
            ->name('employee.documents.upload');

        Route::delete('/employee/documents/{id}', [EmployeeDocumentController::class, 'destroy'])
            ->middleware('permission:documents_self.upload')
            ->name('employee.documents.destroy');

        /*
        |--------------------------------------------------------------------------
        | HR Documents
        |--------------------------------------------------------------------------
        */

        Route::get('/hr/documents', [HRDocumentController::class, 'index'])
            ->middleware('permission:employee_documents.view')
            ->name('hr.documents.index');

        Route::get('/hr/employees/{user}/documents', [HRDocumentController::class, 'show'])
            ->middleware('permission:employee_documents.view')
            ->name('hr.documents.show');

        Route::post('/hr/documents/{id}/approve', [HRDocumentController::class, 'approve'])
            ->middleware('permission:company_documents.manage')
            ->name('hr.documents.approve');

        Route::post('/hr/documents/{id}/reject', [HRDocumentController::class, 'reject'])
            ->middleware('permission:company_documents.manage')
            ->name('hr.documents.reject');

        /*
        |--------------------------------------------------------------------------
        | Policies / Company Documents
        |--------------------------------------------------------------------------
        */

        Route::resource('hr/policies', CompanyDocumentController::class, [
            'names' => 'hr.policies',
        ])->middleware('permission:company_documents.manage');

        Route::get('/employee/policies', [EmployeePolicyController::class, 'index'])
            ->middleware('permission:documents_self.view')
            ->name('employee.policies-index');

        Route::get('/employee/policie', [EmployeePolicyController::class, 'indexx'])
            ->middleware('permission:documents_self.view')
            ->name('employee.hr-policies');
    });
