<?php

namespace App\Services\HRMS\Payroll;

use Carbon\Carbon;

class PayrollS
{
    public function parseMonthInput(string $monthInput): Carbon
    {
        $normalized = preg_match('/^\d{4}-\d{2}$/', $monthInput)
            ? $monthInput.'-01'
            : $monthInput;

        return Carbon::parse($normalized)->startOfMonth();
    }

    public function monthDateRange(string $monthInput): array
    {
        $month = $this->parseMonthInput($monthInput);

        return [
            'start' => $month->copy()->startOfMonth(),
            'end' => $month->copy()->endOfMonth(),
            'month' => (int) $month->month,
            'year' => (int) $month->year,
            'key' => $month->format('Y-m'),
        ];
    }
}
