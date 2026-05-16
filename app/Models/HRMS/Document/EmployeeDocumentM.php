<?php

namespace App\Models\HRMS\Document;

use App\Models\Core\UserM as User;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentM extends Model
{
    use HasFactory;

    protected $table = 'employee_documents_new';

    protected $fillable = [
        'employee_id',
        'document_type_id',
        'title',
        'file_path',
        'file_original_name',
        'file_mime_type',
        'file_size',
        'verification_status',
        'uploaded_by_user_id',
        'verified_by_user_id',
        'verified_at',
        'rejection_reason',
        'expiry_date',
        'uploaded_at',
        'is_required',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'expiry_date' => 'date',
        'is_required' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    /**
     * Main relationship
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentTypeM::class, 'document_type_id', 'id');
    }

    /**
     * Alias relation
     * Required because API/ProfileController using: documents.type
     */
    public function type()
    {
        return $this->belongsTo(DocumentTypeM::class, 'document_type_id', 'id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getIsVerifiedAttribute()
    {
        return $this->verification_status === 'verified';
    }

    public function getIsRejectedAttribute()
    {
        return $this->verification_status === 'rejected';
    }

    public function getIsPendingAttribute()
    {
        return $this->verification_status === 'pending';
    }
}
