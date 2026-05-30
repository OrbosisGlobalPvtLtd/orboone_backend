<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Services\HRMS\Document\HrmsFileStorageS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HrmsFileStorageTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $user;
    private EmployeeM $employee;
    private DocumentTypeM $docType;
    private HrmsFileStorageS $storageService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $employeeRole = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);

        $this->user = UserM::create([
            'name' => 'Test File Storage User',
            'email' => 'storage_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);

        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-STG-999',
            'employment_type' => 'full_time',
            'work_mode' => 'office',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        $this->employee->profile()->create([
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        $this->docType = DocumentTypeM::firstOrCreate(
            ['code' => 'BANK_PROOF'],
            [
                'name' => 'Bank Proof',
                'scope' => 'employee',
                'is_mandatory' => 1,
                'is_active' => 1,
            ]
        );

        $this->storageService = new HrmsFileStorageS();
    }

    public function test_standardized_employee_code_trimming()
    {
        $this->employee->update(['employee_code' => '  emp-stg-999  ']);
        $code = $this->storageService->buildEmployeeCode($this->employee);
        $this->assertEquals('EMP-STG-999', $code);
    }

    public function test_document_type_slugification()
    {
        $slug1 = $this->storageService->slugDocumentType($this->docType);
        $this->assertEquals('bank-proof', $slug1);

        $slug2 = $this->storageService->slugDocumentType('Medical Certificate & Records!');
        $this->assertEquals('medical-certificate-records', $slug2);
    }

    public function test_employee_document_path_building()
    {
        $file = UploadedFile::fake()->create('my_pass_book.pdf', 500, 'application/pdf');
        $path = $this->storageService->buildEmployeeDocumentPath($this->employee, $this->docType, $file);

        // Pattern: hrms/employees/EMP-STG-999/documents/bank-proof/EMP-STG-999_BANK-PROOF_\d{8}_\d{6}\.pdf
        $this->assertMatchesRegularExpression(
            '#^hrms/employees/EMP-STG-999/documents/bank-proof/EMP-STG-999_BANK-PROOF_\d{8}_\d{6}\.pdf$#',
            $path
        );
    }

    public function test_profile_avatar_path_building()
    {
        $file = UploadedFile::fake()->image('me.png');
        $path = $this->storageService->buildProfileAvatarPath($this->employee, $file);

        $this->assertEquals('hrms/employees/EMP-STG-999/profile/avatar/EMP-STG-999_PROFILE.png', $path);
    }

    public function test_profile_avatar_replacement_eliminates_orphans()
    {
        // 1. Save dummy files in the avatar directory
        Storage::disk('private')->put('hrms/employees/EMP-STG-999/profile/avatar/EMP-STG-999_PROFILE.jpg', 'old');
        Storage::disk('private')->put('hrms/employees/EMP-STG-999/profile/avatar/some_orphan.png', 'orphan');

        $file = UploadedFile::fake()->image('new_me.png');
        $newPath = $this->storageService->replaceProfileAvatar($this->employee, $file);

        $this->assertEquals('hrms/employees/EMP-STG-999/profile/avatar/EMP-STG-999_PROFILE.png', $newPath);
        
        // Old files should be deleted
        Storage::disk('private')->assertMissing('hrms/employees/EMP-STG-999/profile/avatar/EMP-STG-999_PROFILE.jpg');
        Storage::disk('private')->assertMissing('hrms/employees/EMP-STG-999/profile/avatar/some_orphan.png');

        // New file should exist
        Storage::disk('private')->assertExists($newPath);

        // Employee profile should be updated
        $this->assertEquals($newPath, $this->employee->profile->fresh()->profile_image);
    }

    public function test_archive_or_replace_for_pending_document_deletes_old_file()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldMeta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $oldFile);

        $document = EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $oldMeta['file_path'],
            'verification_status' => 'pending',
        ]);

        Storage::disk('private')->assertExists($oldMeta['file_path']);

        sleep(1);
        $newFile = UploadedFile::fake()->create('new.pdf', 150);
        $newMeta = $this->storageService->archiveOrReplaceEmployeeDocument($this->employee, $this->docType, $newFile);

        // Physical old file should be deleted
        Storage::disk('private')->assertMissing($oldMeta['file_path']);
        // Physical new file should exist
        Storage::disk('private')->assertExists($newMeta['file_path']);
    }

    public function test_archive_or_replace_for_rejected_document_deletes_old_file()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldMeta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $oldFile);

        $document = EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $oldMeta['file_path'],
            'verification_status' => 'rejected',
        ]);

        Storage::disk('private')->assertExists($oldMeta['file_path']);

        sleep(1);
        $newFile = UploadedFile::fake()->create('new.pdf', 150);
        $newMeta = $this->storageService->archiveOrReplaceEmployeeDocument($this->employee, $this->docType, $newFile);

        // Physical old file should be deleted
        Storage::disk('private')->assertMissing($oldMeta['file_path']);
        Storage::disk('private')->assertExists($newMeta['file_path']);
    }

    public function test_archive_or_replace_for_approved_document_preserves_old_file()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldMeta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $oldFile);

        $document = EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $oldMeta['file_path'],
            'verification_status' => 'verified',
        ]);

        Storage::disk('private')->assertExists($oldMeta['file_path']);

        sleep(1);
        $newFile = UploadedFile::fake()->create('new.pdf', 150);
        $newMeta = $this->storageService->archiveOrReplaceEmployeeDocument($this->employee, $this->docType, $newFile);

        // Physical old file must NOT be deleted (archived safely in storage)
        Storage::disk('private')->assertExists($oldMeta['file_path']);
        Storage::disk('private')->assertExists($newMeta['file_path']);
    }

    public function test_pending_document_reupload()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldMeta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $oldFile);

        $document = EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $oldMeta['file_path'],
            'verification_status' => 'pending',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        sleep(1);
        $newFile = UploadedFile::fake()->create('new.pdf', 150);
        
        // Simulating the controller request
        $response = $this->post(route('api.hrms.documents.upload'), [
            'document_type_id' => $this->docType->id,
            'file' => $newFile,
        ]);

        $response->assertStatus(201);
        
        // Assert old file deleted
        Storage::disk('private')->assertMissing($oldMeta['file_path']);
        
        // Active doc remains pending
        $activeDoc = EmployeeDocumentM::where('employee_id', $this->employee->id)
            ->where('document_type_id', $this->docType->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activeDoc);
        $this->assertEquals('pending', $activeDoc->verification_status);
    }

    public function test_rejected_document_reupload()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldMeta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $oldFile);

        $document = EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $oldMeta['file_path'],
            'verification_status' => 'rejected',
            'rejection_reason' => 'bad photo',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        sleep(1);
        $newFile = UploadedFile::fake()->create('new.pdf', 150);
        
        $response = $this->post(route('api.hrms.documents.upload'), [
            'document_type_id' => $this->docType->id,
            'file' => $newFile,
        ]);

        $response->assertStatus(201);
        
        Storage::disk('private')->assertMissing($oldMeta['file_path']);
        
        $activeDoc = EmployeeDocumentM::where('employee_id', $this->employee->id)
            ->where('document_type_id', $this->docType->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activeDoc);
        $this->assertEquals('pending', $activeDoc->verification_status);
        $this->assertNull($activeDoc->rejection_reason);
    }

    public function test_approved_document_reupload()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldMeta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $oldFile);

        $document = EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $oldMeta['file_path'],
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        $newFile = UploadedFile::fake()->create('new.pdf', 150);
        
        $response = $this->post(route('api.hrms.documents.upload'), [
            'document_type_id' => $this->docType->id,
            'file' => $newFile,
        ]);

        $response->assertStatus(201);
        
        // Old physical file not deleted
        Storage::disk('private')->assertExists($oldMeta['file_path']);
        
        // Old record is_active=false, archived_at set
        $archivedDoc = EmployeeDocumentM::where('id', $document->id)->first();
        $this->assertFalse($archivedDoc->is_active);
        $this->assertNotNull($archivedDoc->archived_at);

        // New record is_active=true, verification_status pending
        $activeDoc = EmployeeDocumentM::where('employee_id', $this->employee->id)
            ->where('document_type_id', $this->docType->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activeDoc);
        $this->assertNotEquals($document->id, $activeDoc->id);
        $this->assertEquals('pending', $activeDoc->verification_status);
        
        // Only 1 active document for that type
        $activeCount = EmployeeDocumentM::where('employee_id', $this->employee->id)
            ->where('document_type_id', $this->docType->id)
            ->where('is_active', true)
            ->count();
        $this->assertEquals(1, $activeCount);
    }

    public function test_required_document_verification()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldMeta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $oldFile);

        $document = EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $oldMeta['file_path'],
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        // Prior to re-upload, completion says it's verified
        $completionS = app(\App\Services\HRMS\Document\EmployeeDocumentCompletionS::class);
        $statusBefore = $completionS->completion($this->employee);
        $this->assertEquals(1, $statusBefore['verified_count']);

        $this->actingAs($this->user);

        // Re-uploading approved doc makes it pending, meaning it no longer satisfies required verified count
        $newFile = UploadedFile::fake()->create('new.pdf', 150);
        $this->post(route('api.hrms.documents.upload'), [
            'document_type_id' => $this->docType->id,
            'file' => $newFile,
        ]);

        $statusAfter = $completionS->completion($this->employee);
        $this->assertEquals(0, $statusAfter['verified_count']);
        $this->assertEquals(1, $statusAfter['pending_count']);
    }

    public function test_mobile_api_upload_compatibility()
    {
        $this->actingAs($this->user);

        $newFile = UploadedFile::fake()->create('test.pdf', 150);
        $response = $this->post(route('api.hrms.documents.upload'), [
            'document_type_id' => $this->docType->id,
            'file' => $newFile,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'employee_id',
                    'document_type_id',
                    'title',
                    'file_path',
                    'file_url',
                    'verification_status',
                ]
            ]);
    }

    public function test_web_download_old_path_resolves()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('mydoc.pdf', 100);
        $meta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $file);

        EmployeeDocumentM::create([
            'employee_id' => $this->employee->id,
            'document_type_id' => $this->docType->id,
            'title' => 'Bank Proof',
            'file_path' => $meta['file_path'],
            'verification_status' => 'pending',
            'is_active' => true,
        ]);

        // Create actual physical file so HrmsFileResolverS can resolve it
        $absolutePath = storage_path('app/private/' . $meta['file_path']);
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($absolutePath, 'dummy file content');

        try {
            $response = $this->get('/hrms/employee/file/' . urlencode($meta['file_path']));
            $response->assertStatus(200);
        } finally {
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }

    public function test_unauthorized_access_protected()
    {
        // Unauthenticated access
        $file = UploadedFile::fake()->create('private.pdf', 100);
        $meta = $this->storageService->storeEmployeeDocument($this->employee, $this->docType, $file);

        $response = $this->get('/hrms/employee/file/' . urlencode($meta['file_path']));
        $response->assertStatus(302); // Redirect to login
    }
}
