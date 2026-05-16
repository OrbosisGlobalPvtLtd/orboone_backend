<?php

namespace App\Services\HRMS\Document;

use Illuminate\Http\UploadedFile;

class DocumentS
{
    public function storeEmployeeDocumentFile(
        UploadedFile $file,
        int $employeeId,
        string $fieldKey
    ): string {
        $fileName = $fieldKey.'_'.time().'.'.$file->extension();

        return $file->storeAs(
            'employee_documents/'.$employeeId,
            $fileName,
            'public'
        );
    }

    public function storePrivateEmployeeDocumentFile(
        UploadedFile $file,
        int $employeeId,
        string $fieldKey
    ): string {
        $fileName = $fieldKey.'_'.time().'.'.$file->extension();

        return $file->storeAs(
            'employee_documents/'.$employeeId,
            $fileName,
            'private'
        );
    }

    public function storePublicUpload(UploadedFile $file): string
    {
        $fileName = 'doc_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $destination = public_path('uploads/employee_docs');

        if (! file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        $file->move($destination, $fileName);

        return 'uploads/employee_docs/'.$fileName;
    }
}
