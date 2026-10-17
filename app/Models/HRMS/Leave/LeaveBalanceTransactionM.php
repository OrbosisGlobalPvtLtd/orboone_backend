<?php

declare(strict_types=1);

namespace App\Models\HRMS\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveBalanceTransactionM extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'leave_balance_transactions';

    protected $guarded = [];

    protected $casts = [
        'period_year' => 'integer',
        'period_month' => 'integer',
        'effective_date' => 'date',
        'amount' => 'decimal:6',
        'balance_before' => 'decimal:6',
        'balance_after' => 'decimal:6',
    ];
}
