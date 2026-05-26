<?php

namespace App\Services\HRMS\Document;

use App\Services\HRMS\Storage\HrmsStoragePathS;
use Illuminate\Http\UploadedFile;

class DocumentS
{
    public function __construct(private HrmsStoragePathS $paths)
    {
    }

    public function storeEmployeeDocumentFile(
        UploadedFile $file,
        int $employeeId,
        string $fieldKey
    ): string {
        $fileName = $fieldKey.'_'.time().'.'.$file->extension();

        return $file->storeAs($this->paths->mapEmployeeDocumentType($employeeId, $fieldKey), $fileName, 'private');
    }

    public function storePrivateEmployeeDocumentFile(
        UploadedFile $file,
        int $employeeId,
        string $fieldKey
    ): string {
        $fileName = $fieldKey.'_'.time().'.'.$file->extension();

        return $file->storeAs($this->paths->mapEmployeeDocumentType($employeeId, $fieldKey), $fileName, 'private');
    }

    public function storePublicUpload(UploadedFile $file): string
    {
        $fileName = 'doc_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        return $file->storeAs($this->paths->temp('previews'), $fileName, 'private');
    }
}
