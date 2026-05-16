<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Leave\HolidayM;
use App\Models\HRMS\Leave\WeekoffRuleM;
use Carbon\Carbon;

class WeekoffHolidayService
{
    public function dayInfo(Carbon|string $date): array
    {
        $date = $date instanceof Carbon ? $date->copy()->timezone('Asia/Kolkata') : Carbon::parse($date, 'Asia/Kolkata');
        $holiday = HolidayM::whereDate('holiday_date', $date->toDateString())
            ->where('is_active', true)
            ->first();

        if ($holiday && $holiday->is_working_day_override) {
            return [
                'date' => $date->toDateString(),
                'is_working_day' => true,
                'is_weekoff' => false,
                'is_holiday' => false,
                'holiday' => $holiday,
            ];
        }

        $weekoff = $this->weekoffRuleFor($date);
        $isHoliday = (bool) $holiday;
        $isWeekoff = $weekoff ? (bool) $weekoff->is_off : false;

        return [
            'date' => $date->toDateString(),
            'is_working_day' => ! $isHoliday && ! $isWeekoff,
            'is_weekoff' => $isWeekoff,
            'is_holiday' => $isHoliday,
            'holiday' => $holiday,
            'weekoff_rule' => $weekoff,
        ];
    }

    public function isWorkingDay(Carbon|string $date): bool
    {
        return (bool) $this->dayInfo($date)['is_working_day'];
    }

    private function weekoffRuleFor(Carbon $date): ?WeekoffRuleM
    {
        $weekday = (int) $date->isoWeekday();
        $weekNumber = (int) ceil($date->day / 7);

        return WeekoffRuleM::where('weekday', $weekday)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date->toDateString());
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->where(function ($query) use ($weekNumber) {
                $query->whereNull('week_number')->orWhere('week_number', $weekNumber);
            })
            ->orderByRaw('CASE WHEN week_number IS NULL THEN 1 ELSE 0 END')
            ->first();
    }
}
