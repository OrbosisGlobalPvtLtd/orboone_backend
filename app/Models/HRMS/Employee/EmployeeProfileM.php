<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;

class EmployeeProfileM extends Model
{
    use HasFactory;

    protected $table = 'employee_profiles';

    protected $fillable = [
        'employee_id',
        'emergency_contact_number',
        'profile_image',
        'date_of_birth',
        'gender',
        'address',
        'highest_qualification',
        'cgpa_percentage',
        'experience_type',
        'total_experience',
        'resume_file',

        'bank_account_no',
        'bank_account_type',
        'bank_holder_name',
        'ifsc_code',
        'bank_branch',

        'profile_status',
        'is_profile_completed',
        'profile_completed_at',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
        'is_profile_completed' => 'boolean',
        'profile_completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\EmployeeDetailFactory::new();
    }

    /* =========================
       RELATIONS
    ========================= */

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(UserM::class, 'approved_by_user_id');
    }

    /* =========================
       SCOPES
    ========================= */

    public function scopeApproved($query)
    {
        return $query->where('is_profile_completed', 1)
            ->where('profile_status', 'approved');
    }

    public function scopePendingForHr($query)
    {
        return $query->where(function ($q) {
            $q->where('is_profile_completed', 0)
                ->orWhereIn('profile_status', ['pending', 'submitted', 'rejected']);
        });
    }

    /* =========================
       ACCESSORS
    ========================= */

    public function getIsCompletedAttribute()
    {
        return (bool) $this->is_profile_completed;
    }

    public function getIsApprovedAttribute()
    {
        return $this->profile_status === 'approved'
            && (bool) $this->is_profile_completed;
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->profile_status) {
            'pending' => 'Pending',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending',
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->profile_status) {
            'pending' => 'warning',
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    /* =========================
       HELPERS
    ========================= */

    public function markSubmitted()
    {
        return $this->update([
            'profile_status' => 'submitted',
            'is_profile_completed' => 0,
            'approved_by_user_id' => null,
            'approved_at' => null,
        ]);
    }

    public function markApproved($approvedByUserId = null)
    {
        return $this->update([
            'profile_status' => 'approved',
            'is_profile_completed' => 1,
            'profile_completed_at' => now(),
            'approved_by_user_id' => $approvedByUserId,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function markRejected($reason = null)
    {
        return $this->update([
            'profile_status' => 'rejected',
            'is_profile_completed' => 0,
            'approved_by_user_id' => null,
            'approved_at' => null,
            'rejection_reason' => $reason,
        ]);
    }
}
