<?php

namespace App\Models\HRMS\Document;

use App\Models\Core\UserM as User;
use App\Models\HRMS\Document\DocumentTypeM as DocumentTypeModal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentM extends Model
{
     use HasFactory;

    protected $table = 'employee_documents';

    protected $fillable = [
        'user_id',
        // 'emp_id',
        'document_type_id',
        'document_type',
        'passport_photo',
        'aadhar_card',
        'pan_card',
        'bank_proof',
        'educational_documents',
        'offer_letter',
        'experience_letter',
        'salary_slip_3_months',
        'relieving_letter',
        'nda_agreement_mou',

        'image', // Keeping legacy fields if needed
        'aadharcard',
        'pancard',
        'bankproof',
        'experiencelatter',
        'file_path',

        'expiry_date',
        'status',
        'uploaded_by',
        'rejection_reason',
    ];

    public function type()
    {
        return $this->belongsTo(DocumentTypeModal::class, 'document_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploaded_by_user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}