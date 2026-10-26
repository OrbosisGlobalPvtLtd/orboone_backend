<?php

namespace App\Services\HRMS\Attendance;

use Carbon\Carbon;

/**
 * AttendancePunchOutClassifier
 *
 * Central authority for classifying punch-out times against attendance policy thresholds.
 *
 * Priority: Absent > Half Day > Early Logout.
 * Half Day and Early Logout NEVER apply on the same day.
 *
 * Rules:
 *   1. E > 300                   -> Absent / LWP
 *   2. N < absent_below_minutes -> Absent / LWP
 *   3. E > H                    -> Half Day only (is_early_out = false, early_out_minutes = 0)
 *   4. G < E <= H               -> Full Day + Early Logout (is_early_out = true, early_out_minutes = E)
 *   5. E <= G                   -> Full Day, no violation, early_out_minutes = 0
 *
 * Boundaries (default G=60, H=90):
 *   E = 60 -> Full Day (within grace)
 *   E = 61 -> Early Logout (61 min)
 *   E = 90 -> Early Logout (90 min)
 *   E = 91 -> Half Day only (no early logout)
 */
class AttendancePunchOutClassifier
{
    /**
     * Classify punch out against policy thresholds.
     *
     * @param Carbon|string|null $punchOut Actual punch-out time
     * @param Carbon|string|null $targetOut Target expected punch-out time from shift
     * @param object|array|null $policy Assigned attendance policy rules
     * @param int|float|null $netMinutes Net worked minutes (gross - break)
     * @param array $options Context options (has_approved_half_day_leave, has_approved_full_day_leave, is_flexible, etc.)
     * @return array
     */
    public static function classify(
        $punchOut,
        $targetOut,
        $policy = null,
        $netMinutes = null,
        array $options = []
    ): array {
        $hasApprovedFullDay = (bool) ($options['has_approved_full_day_leave'] ?? false);
        $hasApprovedHalfDay = (bool) ($options['has_approved_half_day_leave'] ?? false);
        $isHalfDayContext = $hasApprovedHalfDay
            || (bool) ($options['is_half_day'] ?? false)
            || (bool) ($options['is_half_day_punch'] ?? false);
        $isFlexible = (bool) ($options['is_flexible'] ?? false);

        if ($hasApprovedFullDay) {
            return [
                'status' => 'leave',
                'status_code' => 'leave',
                'is_half_day' => false,
                'is_lwp' => false,
                'is_absent' => false,
                'is_early_out' => false,
                'early_out_minutes' => 0,
                'early_minutes' => 0,
                'reason' => 'Approved Full Day Leave',
                'classification' => 'leave',
            ];
        }

        if ($isFlexible) {
            $required = is_array($policy)
                ? (int) ($policy['required_work_minutes'] ?? 0)
                : (int) ($policy?->required_work_minutes ?? 0);
            $halfDayMin = is_array($policy)
                ? (int) ($policy['half_day_min_minutes'] ?? 0)
                : (int) ($policy?->half_day_min_minutes ?? 0);
            $absentBelow = is_array($policy)
                ? (int) ($policy['absent_below_minutes'] ?? 0)
                : (int) ($policy?->absent_below_minutes ?? 0);

            $effHalfDay = $halfDayMin > 0 ? $halfDayMin : ($required > 0 ? (int)($required / 2) : 240);
            $effAbsent = ($absentBelow > 0 && $absentBelow < $effHalfDay) ? $absentBelow : (int)($effHalfDay / 2);

            $status = 'present';
            $isHalf = false;
            $isLwp = false;
            $reason = 'flexible_shift';

            if ($netMinutes !== null) {
                if ($effAbsent > 0 && $netMinutes < $effAbsent) {
                    $status = 'lwp';
                    $isLwp = true;
                    $reason = 'Insufficient worked minutes for attendance.';
                } elseif ($effHalfDay > 0 && $netMinutes < $effHalfDay) {
                    $status = 'half_day';
                    $isHalf = true;
                    $reason = 'Worked minutes below half-day threshold.';
                } elseif ($required > 0 && $netMinutes < $required) {
                    $status = 'half_day';
                    $isHalf = true;
                    $reason = 'Required worked minutes not completed.';
                }
            }

            return [
                'status' => $status,
                'status_code' => $status,
                'is_half_day' => $isHalf,
                'is_lwp' => $isLwp,
                'is_absent' => $isLwp,
                'is_early_out' => false,
                'early_out_minutes' => 0,
                'early_minutes' => 0,
                'reason' => $reason,
                'classification' => 'flexible',
            ];
        }

        if ($isHalfDayContext) {
            $absentBelow = is_array($policy)
                ? (int) ($policy['absent_below_minutes'] ?? 0)
                : (int) ($policy?->absent_below_minutes ?? 0);

            if ($netMinutes !== null && $absentBelow > 0 && $netMinutes < $absentBelow) {
                return [
                    'status' => 'lwp',
                    'status_code' => 'lwp',
                    'is_half_day' => false,
                    'is_lwp' => true,
                    'is_absent' => true,
                    'is_early_out' => false,
                    'early_out_minutes' => 0,
                    'early_minutes' => 0,
                    'reason' => 'Insufficient worked minutes for attendance.',
                    'classification' => 'lwp',
                ];
            }

            return [
                'status' => 'half_day',
                'status_code' => 'half_day',
                'is_half_day' => true,
                'is_lwp' => false,
                'is_absent' => false,
                'is_early_out' => false,
                'early_out_minutes' => 0,
                'early_minutes' => 0,
                'reason' => $hasApprovedHalfDay ? 'Approved Half Day Leave' : 'Half Day Punch',
                'classification' => 'half_day',
            ];
        }

        if (! $punchOut || ! $targetOut) {
            return [
                'status' => 'present',
                'status_code' => 'present',
                'is_half_day' => false,
                'is_lwp' => false,
                'is_absent' => false,
                'is_early_out' => false,
                'early_out_minutes' => 0,
                'early_minutes' => 0,
                'reason' => null,
                'classification' => 'full_day',
            ];
        }

        $out = $punchOut instanceof Carbon ? $punchOut : Carbon::parse($punchOut);
        $target = $targetOut instanceof Carbon ? $targetOut : Carbon::parse($targetOut);

        // Calculate early minutes before target
        $E = $out->lt($target) ? (int) $out->diffInMinutes($target) : 0;

        // Policy thresholds
        $grace = is_array($policy)
            ? (int) ($policy['early_out_grace_minutes'] ?? 60)
            : (int) ($policy?->early_out_grace_minutes ?? 60);

        $halfDayLimit = is_array($policy)
            ? (int) ($policy['early_out_half_day_minutes'] ?? 90)
            : (int) ($policy?->early_out_half_day_minutes ?? 90);

        $absentBelow = is_array($policy)
            ? (int) ($policy['absent_below_minutes'] ?? 0)
            : (int) ($policy?->absent_below_minutes ?? 0);

        // Business cutoff: a punch-out more than five hours before the target
        // is Absent/LWP. For a 7:00 PM shift this means before 2:00 PM.
        if ($E > 300) {
            return [
                'status' => 'lwp',
                'status_code' => 'lwp',
                'is_half_day' => false,
                'is_lwp' => true,
                'is_absent' => true,
                'is_early_out' => false,
                'early_out_minutes' => 0,
                'early_minutes' => $E,
                'reason' => 'Early out exceeds absent limit',
                'classification' => 'absent',
            ];
        }

        // Priority 1: Absent / LWP by net minutes
        if ($netMinutes !== null && $absentBelow > 0 && $netMinutes < $absentBelow) {
            return [
                'status' => 'lwp',
                'status_code' => 'lwp',
                'is_half_day' => false,
                'is_lwp' => true,
                'is_absent' => true,
                'is_early_out' => false,
                'early_out_minutes' => 0,
                'early_minutes' => $E,
                'reason' => 'Net minutes below absent',
                'classification' => 'absent',
            ];
        }

        // Priority 2: Half Day only (E > H)
        if ($E > $halfDayLimit) {
            return [
                'status' => 'half_day',
                'status_code' => 'half_day',
                'is_half_day' => true,
                'is_lwp' => false,
                'is_absent' => false,
                'is_early_out' => false,
                'early_out_minutes' => 0,
                'early_minutes' => $E,
                'reason' => 'Early out exceeds half day limit',
                'classification' => 'half_day',
            ];
        }

        // Priority 3: Full Day + Early Logout (G < E <= H)
        if ($E > $grace && $E <= $halfDayLimit) {
            return [
                'status' => 'present',
                'status_code' => 'present',
                'is_half_day' => false,
                'is_lwp' => false,
                'is_absent' => false,
                'is_early_out' => true,
                'early_out_minutes' => $E,
                'early_minutes' => $E,
                'reason' => 'Early logout violation',
                'classification' => 'early_logout',
            ];
        }

        // Priority 4: Full Day (E <= G)
        return [
            'status' => 'present',
            'status_code' => 'present',
            'is_half_day' => false,
            'is_lwp' => false,
            'is_absent' => false,
            'is_early_out' => false,
            'early_out_minutes' => 0,
            'early_minutes' => $E,
            'reason' => null,
            'classification' => 'full_day',
        ];
    }
}
