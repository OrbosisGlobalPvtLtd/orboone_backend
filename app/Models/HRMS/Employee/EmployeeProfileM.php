<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Employee\EmployeeM;

class EmployeeProfileM extends Model
{
    protected $table = 'employee_profiles';

    protected $fillable = [
        'employee_id',
        'profile_image',
        'date_of_birth',
        'gender',
        'address',
        'highest_qualification',
        'cgpa_percentage',
        'total_experience',
        'resume_file',

        'bank_account_no',
        'bank_account_type',
        'bank_holder_name',
        'ifsc_code',
        'bank_branch',

        // 🔥 NEW FIELDS
        'profile_status',
        'is_profile_completed',
        'profile_completed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_profile_completed' => 'boolean',
        'profile_completed_at' => 'datetime',
    ];

    /* =========================
       RELATION
    ========================= */
    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    /* =========================
       ACCESSORS (SMART LOGIC)
    ========================= */

    // ✔ Simple completed check
    public function getIsCompletedAttribute()
    {
        return (bool) $this->is_profile_completed;
    }

    // ✔ Profile status badge helper
    public function getStatusLabelAttribute()
    {
        return match ($this->profile_status) {
            'pending' => 'Pending',
            'submitted' => 'Submitted',
            'approved' => 'Completed',
            'rejected' => 'Rejected',
            default => 'Pending',
        };
    }

    // ✔ Color helper (UI ke liye)
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
       HELPERS (IMPORTANT)
    ========================= */

    // 👉 Employee submits profile
    public function markSubmitted()
    {
        $this->update([
            'profile_status' => 'submitted',
            'is_profile_completed' => 0,
        ]);
    }

    // 👉 HR approves profile
    public function markApproved()
    {
        $this->update([
            'profile_status' => 'approved',
            'is_profile_completed' => 1,
            'profile_completed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    // 👉 HR rejects profile
    public function markRejected($reason = null)
    {
        $this->update([
            'profile_status' => 'rejected',
            'is_profile_completed' => 0,
            'rejection_reason' => $reason,
        ]);
    }
}