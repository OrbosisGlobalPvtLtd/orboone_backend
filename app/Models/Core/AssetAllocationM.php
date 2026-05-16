<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;

class AssetAllocationM extends Model
{
    protected $table = 'asset_allocations'; 

    protected $guarded = [];

    protected $casts = [
        'allocated_date' => 'date',
        'return_date' => 'date',
        'returned_at' => 'datetime',
    ];

    /* =========================
       RELATIONS
    ========================= */

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function returnedTo()
    {
        return $this->belongsTo(UserM::class, 'returned_to_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(UserM::class, 'received_by_user_id');
    }

    /* =========================
       SCOPES
    ========================= */

    public function scopeAllocated($query)
    {
        return $query->where('handover_status', 'allocated');
    }

    public function scopePendingReturn($query)
    {
        return $query->where('handover_status', 'pending_return');
    }

    public function scopeReturned($query)
    {
        return $query->where('handover_status', 'returned');
    }

    public function scopeActiveAssets($query)
    {
        return $query->whereIn('handover_status', ['allocated', 'pending_return']);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /* =========================
       HELPERS
    ========================= */

    public function markReturned($userId = null, $remarks = null)
    {
        return $this->update([
            'handover_status' => 'returned',
            'returned_at' => now(),
            'returned_to_user_id' => $userId,
            'return_remarks' => $remarks,
        ]);
    }

    public function markPendingReturn()
    {
        return $this->update([
            'handover_status' => 'pending_return',
        ]);
    }

    public function markDamaged($remarks = null)
    {
        return $this->update([
            'handover_status' => 'damaged',
            'return_remarks' => $remarks,
        ]);
    }

    public function markLost($remarks = null)
    {
        return $this->update([
            'handover_status' => 'lost',
            'return_remarks' => $remarks,
        ]);
    }
}