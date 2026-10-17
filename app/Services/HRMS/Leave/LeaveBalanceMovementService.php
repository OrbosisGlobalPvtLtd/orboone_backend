<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Enums\LeaveBucket;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceTransactionM;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class LeaveBalanceMovementService
{
    public function __construct(private LeaveBalanceService $balanceService)
    {
    }

    public function accrue(
        LeaveAllocationM $allocation,
        LeaveBucket $bucket,
        int $year,
        int $month,
        string $amount,
        ?int $actorId = null,
        ?int $policyId = null
    ): LeaveBalanceTransactionM {
        return $this->move($allocation, $bucket, 'monthly_accrual', $amount, $year, $month,
            sprintf('%04d-%02d-01', $year, $month),
            sprintf('accrual:%d:%s:%04d-%02d', $allocation->employee_id, $bucket->value, $year, $month),
            null, null, 'Monthly leave accrual', $actorId, 'system', $policyId);
    }

    public function debit(
        LeaveAllocationM $allocation,
        LeaveBucket $bucket,
        int $requestId,
        int $year,
        int $month,
        string $amount,
        string $effectiveDate,
        ?int $actorId = null,
        string $source = 'system',
        string $reason = 'Approved leave debit'
    ): LeaveBalanceTransactionM {
        if (bccomp($amount, '0', 6) <= 0) {
            throw new InvalidArgumentException('Debit amount must be positive.');
        }
        return $this->move($allocation, $bucket, 'leave_debit', bcsub('0', $amount, 6), $year, $month,
            $effectiveDate,
            sprintf('debit:%d:%04d:%s', $requestId, $year, $bucket->value),
            $requestId, null, $reason, $actorId, $source);
    }

    public function reverse(int $debitTransactionId, ?int $actorId = null, ?string $reason = null): LeaveBalanceTransactionM
    {
        $debit = LeaveBalanceTransactionM::query()->findOrFail($debitTransactionId);
        if ($debit->transaction_type !== 'leave_debit') {
            throw new InvalidArgumentException('Only a leave debit can be reversed.');
        }
        $allocation = LeaveAllocationM::query()->findOrFail($debit->leave_allocation_id);

        return DB::transaction(function () use ($allocation, $debit, $actorId, $reason): LeaveBalanceTransactionM {
            $locked = LeaveAllocationM::query()->lockForUpdate()->findOrFail($allocation->id);
            $this->assertLedgerBacked($locked);
            $key = 'reversal:' . $debit->id;
            $existing = LeaveBalanceTransactionM::query()->where('idempotency_key', $key)->first();
            if ($existing) {
                return $existing;
            }

            $snapshot = $this->balanceService->getLedgerSnapshot($locked);
            $bucket = LeaveBucket::from($debit->bucket);
            $before = $snapshot[$bucket->value]['available'];
            $reversalAmount = bcsub('0', (string) $debit->amount, 6);
            $after = bcadd($before, $reversalAmount, 6);
            $reversal = LeaveBalanceTransactionM::query()->create([
                'employee_id' => $locked->employee_id,
                'leave_allocation_id' => $locked->id,
                'policy_id' => $locked->policy_id,
                'bucket' => $bucket->value,
                'transaction_type' => 'reversal',
                'period_year' => $debit->period_year,
                'period_month' => $debit->period_month,
                'effective_date' => $debit->effective_date,
                'amount' => $reversalAmount,
                'balance_before' => $before,
                'balance_after' => $after,
                'leave_request_id' => $debit->leave_request_id,
                'reverses_transaction_id' => $debit->id,
                'idempotency_key' => $key,
                'reason' => $reason ?? 'Leave debit reversed',
                'created_by' => $actorId,
                'source' => 'system',
            ]);
            $this->recalculateRunningBalances($locked, $bucket, (int) $reversal->id);
            $this->writeSnapshot($locked);
            return $reversal->fresh();
        });
    }

    public function lapse(LeaveAllocationM $allocation, LeaveBucket $bucket, int $year, string $effectiveDate, ?int $policyId = null): LeaveBalanceTransactionM
    {
        return DB::transaction(function () use ($allocation, $bucket, $year, $effectiveDate, $policyId): LeaveBalanceTransactionM {
            $locked = LeaveAllocationM::query()->lockForUpdate()->findOrFail($allocation->id);
            $this->assertLedgerBacked($locked);
            $key = sprintf('lapse:%d:%s:%04d', $locked->employee_id, $bucket->value, $year);
            $existing = LeaveBalanceTransactionM::query()->where('idempotency_key', $key)->first();
            if ($existing) {
                return $existing;
            }

            $balance = $this->balanceService->getLedgerBucketBalance($locked, $bucket);
            $amount = bcsub('0', $balance, 6);
            $transaction = LeaveBalanceTransactionM::query()->create([
                'employee_id' => $locked->employee_id,
                'leave_allocation_id' => $locked->id,
                'policy_id' => $policyId ?? $locked->policy_id,
                'bucket' => $bucket->value,
                'transaction_type' => 'lapse',
                'period_year' => $year,
                'period_month' => null,
                'effective_date' => $effectiveDate,
                'amount' => $amount,
                'balance_before' => $balance,
                'balance_after' => '0.000000',
                'leave_request_id' => null,
                'reverses_transaction_id' => null,
                'idempotency_key' => $key,
                'reason' => 'Year-end permanent leave lapse',
                'created_by' => null,
                'source' => 'system',
            ]);
            $this->recalculateRunningBalances($locked, $bucket, (int) $transaction->id);
            $this->writeSnapshot($locked);
            return $transaction->fresh();
        });
    }

    public function opening(
        LeaveAllocationM $allocation,
        LeaveBucket $bucket,
        int $year,
        string $amount,
        string $effectiveDate,
        string $reason,
        ?int $actorId = null,
        string $source = 'import'
    ): LeaveBalanceTransactionM {
        if (bccomp($amount, '0', 6) < 0) {
            throw new InvalidArgumentException('Opening balance cannot be negative.');
        }
        return $this->move($allocation, $bucket, 'opening_balance', $amount, $year, null, $effectiveDate,
            sprintf('opening:%d:%s:%04d', $allocation->employee_id, $bucket->value, $year),
            null, null, $reason, $actorId, $source);
    }

    public function adjustment(
        LeaveAllocationM $allocation,
        LeaveBucket $bucket,
        int $year,
        string $amount,
        string $effectiveDate,
        string $uuid,
        string $reason,
        ?int $actorId = null,
        string $source = 'manual',
        ?int $requestId = null
    ): LeaveBalanceTransactionM {
        if (! Str::isUuid($uuid)) {
            throw new InvalidArgumentException('Adjustment idempotency UUID must be valid.');
        }
        return $this->move($allocation, $bucket, 'adjustment', $amount, $year, null, $effectiveDate,
            'adjustment:' . $uuid, $requestId, null, $reason, $actorId, $source);
    }

    public function manualUsedAdjustment(
        LeaveAllocationM $allocation,
        LeaveBucket $bucket,
        int $year,
        string $deltaUsed,
        string $effectiveDate,
        string $uuid,
        string $reason,
        ?int $actorId = null
    ): LeaveBalanceTransactionM {
        if (! Str::isUuid($uuid)) {
            throw new InvalidArgumentException('Adjustment idempotency UUID must be valid.');
        }

        $cmp = bccomp($deltaUsed, '0', 6);
        if ($cmp === 0) {
            throw new InvalidArgumentException('Delta used cannot be zero.');
        }

        if ($cmp > 0) {
            $type = 'leave_debit';
            $amount = bcsub('0', $deltaUsed, 6);
            $key = 'manual_debit:' . $uuid . ':' . $bucket->value;
        } else {
            $type = 'reversal';
            $amount = bcsub('0', $deltaUsed, 6);
            $key = 'manual_reversal:' . $uuid . ':' . $bucket->value;
        }

        $month = (int) Carbon::parse($effectiveDate, 'Asia/Kolkata')->month;

        return $this->move(
            $allocation,
            $bucket,
            $type,
            $amount,
            $year,
            $month,
            $effectiveDate,
            $key,
            null,
            null,
            $reason,
            $actorId,
            'manual'
        );
    }

    /** Recompute the stored snapshot without adding a ledger movement. */
    public function refreshSnapshot(LeaveAllocationM $allocation): LeaveAllocationM
    {
        return DB::transaction(function () use ($allocation): LeaveAllocationM {
            $locked = LeaveAllocationM::query()->lockForUpdate()->findOrFail($allocation->id);
            $this->assertLedgerBacked($locked);
            $this->writeSnapshot($locked);

            return $locked;
        });
    }

    private function move(
        LeaveAllocationM $allocation,
        LeaveBucket $bucket,
        string $type,
        string $amount,
        int $year,
        ?int $month,
        string $effectiveDate,
        string $key,
        ?int $requestId,
        ?int $reversesId,
        string $reason,
        ?int $actorId,
        string $source = 'system',
        ?int $policyId = null
    ): LeaveBalanceTransactionM {
        if (! preg_match('/^-?\d+(?:\.\d{1,6})?$/', $amount)) {
            throw new InvalidArgumentException('Ledger amounts must be decimal strings with up to six fractional digits.');
        }

        if (! in_array($source, ['system', 'manual', 'import'], true)) {
            throw new InvalidArgumentException('Unsupported leave transaction source.');
        }

        return DB::transaction(function () use ($allocation, $bucket, $type, $amount, $year, $month, $effectiveDate, $key, $requestId, $reversesId, $reason, $actorId, $source, $policyId): LeaveBalanceTransactionM {
            $locked = LeaveAllocationM::query()->lockForUpdate()->findOrFail($allocation->id);
            $this->assertLedgerBacked($locked);
            $existing = LeaveBalanceTransactionM::query()->where('idempotency_key', $key)->first();
            if ($existing) {
                return $existing;
            }

            $snapshot = $this->balanceService->getLedgerSnapshot($locked);
            $before = $snapshot[$bucket->value]['available'];
            $normalizedAmount = bcadd($amount, '0', 6);
            $after = bcadd($before, $normalizedAmount, 6);
            if (bccomp($after, '0', 6) < 0) {
                throw new DomainException('Leave ledger balance cannot become negative.');
            }

            $transaction = LeaveBalanceTransactionM::query()->create([
                'employee_id' => $locked->employee_id,
                'leave_allocation_id' => $locked->id,
                'policy_id' => $policyId ?? $locked->policy_id,
                'bucket' => $bucket->value,
                'transaction_type' => $type,
                'period_year' => $year,
                'period_month' => $month,
                'effective_date' => $effectiveDate,
                'amount' => $normalizedAmount,
                'balance_before' => $before,
                'balance_after' => $after,
                'leave_request_id' => $requestId,
                'reverses_transaction_id' => $reversesId,
                'idempotency_key' => $key,
                'reason' => $reason,
                'created_by' => $actorId,
                'source' => $source,
            ]);
            $this->recalculateRunningBalances($locked, $bucket, (int) $transaction->id);
            $this->writeSnapshot($locked);
            return $transaction->fresh();
        });
    }

    private function writeSnapshot(LeaveAllocationM $allocation): void
    {
        $snapshot = $this->balanceService->getLedgerSnapshot($allocation);
        $plUsed = $snapshot['PL']['used'];
        $slUsed = $snapshot['SL']['used'];
        $plRemaining = $snapshot['PL']['available'];
        $slRemaining = $snapshot['SL']['available'];
        $paidAllocated = bcadd($plUsed, $plRemaining, 6);
        $sickAllocated = bcadd($slUsed, $slRemaining, 6);
        $compOffRemaining = (string) ($allocation->comp_off_remaining ?? '0');

        $allocation->forceFill([
            'paid_used' => $this->roundTo2($plUsed),
            'sick_used' => $this->roundTo2($slUsed),
            'paid_remaining' => $this->roundTo2($plRemaining),
            'sick_remaining' => $this->roundTo2($slRemaining),
            'paid_allocated' => $this->roundTo2($paidAllocated),
            'sick_allocated' => $this->roundTo2($sickAllocated),
            'total_allocated' => $this->roundTo2(bcadd($paidAllocated, $sickAllocated, 6)),
            'total_used' => $this->roundTo2(bcadd(
                bcadd(bcadd($plUsed, $slUsed, 6), (string) ($allocation->comp_off_used ?? '0'), 6),
                (string) ($allocation->lwp_used ?? '0'),
                6
            )),
            'total_remaining' => $this->roundTo2(bcadd(bcadd($plRemaining, $slRemaining, 6), $compOffRemaining, 6)),
        ])->save();
    }

    private function roundTo2(string $amount): string
    {
        return bcadd($amount, bccomp($amount, '0', 6) < 0 ? '-0.005' : '0.005', 2);
    }

    public function recalculateRunningBalances(
        LeaveAllocationM $allocation,
        ?LeaveBucket $bucket = null,
        ?int $fromTransactionId = null
    ): void
    {
        $buckets = $bucket !== null ? [$bucket] : LeaveBucket::cases();
        foreach ($buckets as $b) {
            $query = LeaveBalanceTransactionM::query()
                ->where('leave_allocation_id', $allocation->id)
                ->where('bucket', $b->value);

            if ($fromTransactionId !== null) {
                $changed = LeaveBalanceTransactionM::query()
                    ->where('leave_allocation_id', $allocation->id)
                    ->where('bucket', $b->value)
                    ->find($fromTransactionId);
                if (! $changed) {
                    continue;
                }

                $previous = LeaveBalanceTransactionM::query()
                    ->where('leave_allocation_id', $allocation->id)
                    ->where('bucket', $b->value)
                    ->where(static function ($q) use ($changed): void {
                        $q->whereDate('effective_date', '<', $changed->effective_date)
                            ->orWhere(static function ($sameDate) use ($changed): void {
                                $sameDate->whereDate('effective_date', '=', $changed->effective_date)
                                    ->where('id', '<', $changed->id);
                            });
                    })
                    ->orderByDesc('effective_date')
                    ->orderByDesc('id')
                    ->first();
                $running = (string) ($previous?->balance_after ?? '0.000000');

                $query->where(static function ($q) use ($changed): void {
                    $q->whereDate('effective_date', '>', $changed->effective_date)
                        ->orWhere(static function ($sameDate) use ($changed): void {
                            $sameDate->whereDate('effective_date', '=', $changed->effective_date)
                                ->where('id', '>=', $changed->id);
                        });
                });
            } else {
                $running = '0.000000';
            }

            $transactions = $query->orderBy('effective_date', 'asc')->orderBy('id', 'asc')->get();

            foreach ($transactions as $t) {
                $before = $running;
                $running = bcadd($running, (string) $t->amount, 6);
                if (bccomp((string) $t->balance_before, $before, 6) !== 0 || bccomp((string) $t->balance_after, $running, 6) !== 0) {
                    $t->forceFill([
                        'balance_before' => $before,
                        'balance_after' => $running,
                    ])->saveQuietly();
                }
            }
        }
    }

    private function assertLedgerBacked(LeaveAllocationM $allocation): void
    {
        if (! $allocation->isLedgerBacked()) {
            throw new DomainException('Leave ledger movement requires an opted-in permanent allocation.');
        }
    }
}
