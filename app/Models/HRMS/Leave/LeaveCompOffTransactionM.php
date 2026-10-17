<?php

declare(strict_types=1);

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

final class LeaveCompOffTransactionM extends Model
{
    protected $table = 'leave_comp_off_transactions';

    protected $guarded = [];

    protected $casts = [
        'effective_date' => 'date',
        'amount' => 'decimal:6',
    ];

    public function compOff()
    {
        return $this->belongsTo(CompOffM::class, 'comp_off_id');
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequestM::class, 'leave_request_id');
    }
}
