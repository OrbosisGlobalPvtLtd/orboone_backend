<?php

namespace App\Models\HRMS\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTypeM extends Model
{
    use HasFactory;

    protected $table = 'document_types';

    protected $fillable = [
        'name',
        'code',
        'scope',
        'applies_to',
        'is_mandatory',
        'has_expiry',
        'is_active',

        // NEW
        'allowed_extensions',
        'max_file_size_mb',
        'allow_multiple',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',

        // NEW
        'allowed_extensions' => 'array',
        'allow_multiple' => 'boolean',
        'max_file_size_mb' => 'integer',
    ];

    public function employeeDocuments()
    {
        return $this->hasMany(
            EmployeeDocumentM::class,
            'document_type_id',
            'id',
        );
    }
}
