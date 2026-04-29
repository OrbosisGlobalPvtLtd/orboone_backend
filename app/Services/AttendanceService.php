<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;

class AttendanceService
{
    /**
     * Business Rules:
     * 1. Login window: 10:00 AM to 11:00 AM.
     * 2. Late: After 11:05 AM.
     * 3. Blocked: After 11:15 AM.
     * 4. Working Hours: 9 hours per day.
     * 5. Break Time: 1 hour.
     * 6. Leave Rules:
     *    - Working hours < 4.5h -> Full Day Leave
     *    - Working hours < 9h -> Half Day Leave
     * 7. Penalties:
     *    - 3 Late Marks = 0.5 Day Leave
     *    - 3 Early Punch-Outs = 0.5 Day Leave
     *    - 3 Missed Punches = LWP
     */

    public function checkPunchInStatus($userId, $now = null)
    {
        $now = $now ?: Carbon::now();
        $time = $now->format('H:i:s');
        $today = $now->format('Y-m-d');

        // Check if weekend or off
        if (!$this->isWorkingDay($now)) {
            return ['status' => 'off', 'message' => 'Today is a weekly off or holiday.'];
        }

        // Check if already blocked (admin can unlock)
        $existing = Attendance::where('user_id', $userId)->where('date', $today)->first();
        if ($existing && $existing->is_blocked) {
            return ['status' => 'blocked', 'message' => 'Punch-in blocked. Please contact HR to unlock.'];
        }

        // Login window rules: Block after 11:15 AM
        $blockTime = '11:15:00';
        if ($time > $blockTime && (!$existing || (!$existing->manual_unlock_by && !$existing->is_manual_entry))) {
            return ['status' => 'blocked', 'message' => 'Punch-in time passed (11:15 AM). Contact HR.'];
        }

        return ['status' => 'allowed'];
    }

    public function processPunchIn($userId, $workType = 'WFO', $note = null, $lat = null, $lng = null, $customTime = null)
    {
        $now = $customTime ? Carbon::parse($customTime) : Carbon::now();
        $timeToSave = $now->format('h:i A');
        $today = $now->format('Y-m-d');

        $check = $this->checkPunchInStatus($userId, $now);
        if ($check['status'] == 'blocked') {
            // return $check;
            $status='blocked';
        } else {
            $status='Present';
        }

        $isLate = false;
        $lateThreshold = '11:05:00';
        if ($now->format('H:i:s') > $lateThreshold) {
            $isLate = true;
        }

        Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $today],
            [
                'clock_in' => $timeToSave,
                'is_late' => $isLate,
                'work_type' => $workType,
                'punch_in_note' => $note,
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => $status
            ]
        );
        if ($check['status'] == 'blocked') {
            return $check;
        } else {
             return [
            'status' => true, 
            'message' => $isLate ? 'Punch-in recorded (Late Mark applied).' : 'Punch-in recorded successfully.'
        ];
        }
       
    }

    public function processPunchOut($userId, $note = null, $lat = null, $lng = null, $customTime = null)
    {
        $today = Carbon::now()->format('Y-m-d');
        $attendance = Attendance::where('user_id', $userId)->where('date', $today)->first();

        if (!$attendance || !$attendance->clock_in) {
            return ['status' => 'error', 'message' => 'No active punch-in found for today.'];
        }

        if ($attendance->clock_out) {
            return ['status' => 'error', 'message' => 'You have already punched out.'];
        }

        // Parse custom time intelligently
        $now = Carbon::now();
        if ($customTime) {
            // If user sends "7:00", and clock_in was "10:00", it likely means 19:00 (7 PM)
            $parsedTime = Carbon::parse($customTime);
            $inTime = Carbon::parse($attendance->clock_in);
            
            if ($parsedTime->lt($inTime) && $parsedTime->hour < 12) {
                $parsedTime->addHours(12);
            }
            $now = $parsedTime;
        }

        $timeToSave = $now->format('h:i A');
        $attendance->clock_out = $timeToSave;
        $attendance->punch_out_note = $note;
            $attendance->latitude = $lat ?? $attendance->latitude;
            $attendance->longitude = $lng ?? $attendance->longitude;
        $attendance->save();

        $this->calculateWorkingHours($attendance);

        // Format Human Readable Message
        $in = Carbon::parse($attendance->clock_in);
        $out = Carbon::parse($attendance->clock_out);
        $totalMinutes = $in->diffInMinutes($out);
        $h = floor($totalMinutes / 60);
        $m = $totalMinutes % 60;

        $netH = floor((float)$attendance->working_hours);
        $netM = round(((float)$attendance->working_hours - $netH) * 60);

        return [
            'status' => true, 
            'message' => "Punch-out recorded. Working hours: {$h} hours {$m} mins"
        ];
    }

    public function calculateWorkingHours(Attendance $attendance)
    {
        if (!$attendance->clock_in || !$attendance->clock_out) return;

        $in = Carbon::parse($attendance->clock_in);
        $out = Carbon::parse($attendance->clock_out);
        
        $totalMinutes = $in->diffInMinutes($out);
        $breakMinutes = 60; // 1 hour break
        
        $netMinutes = max(0, $totalMinutes - $breakMinutes);
        $netHours = round($netMinutes / 60, 2);
        $grossHours = round($totalMinutes / 60, 2);
        
        $attendance->working_hours = $netHours; // Store net working hours
        $attendance->total_break_time = $breakMinutes;

        // Corrected Thresholds based on Gross Time (including break)
        // 9h Gross = 8h Net -> Present
        // 4.5h Gross = 3.5h Net -> Half Day threshold
        
        $status = 'Present';
        $leaveMarking = null;

        if ($grossHours < 4.5) {
            $status = 'Full Day Leave';
            $leaveMarking = 'Full Day';
            $attendance->is_early_out = true;
        } elseif ($grossHours < 9.0) {
            $status = 'Half Day Leave';
            $leaveMarking = 'Half Day';
            $attendance->is_early_out = true;
        } else {
            $status = 'Present';
            $leaveMarking = null;
            $attendance->is_early_out = false;
        }

        // Penalty Logic: 3 Late Marks = 0.5 Day Leave, 3 Early Outs = 0.5 Day Leave
        if ($status == 'Present') {
            $month = Carbon::parse($attendance->date)->month;
            $year = Carbon::parse($attendance->date)->year;

            $lateCount = Attendance::where('user_id', $attendance->user_id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('is_late', true)
                ->count();

            $earlyCount = Attendance::where('user_id', $attendance->user_id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('is_early_out', true)
                ->count();

            if (($lateCount > 0 && $lateCount % 3 == 0) || ($earlyCount > 0 && $earlyCount % 3 == 0)) {
                $status = 'Half Day (Penalty)';
                $leaveMarking = 'Half Day';
            }
        }

        $attendance->status = $status;
        $attendance->leave_marking = $leaveMarking;
        $attendance->save();
    }

    public function isWorkingDay($date)
    {
        $carbonDate = Carbon::parse($date);
        
        // Sunday
        if ($carbonDate->dayOfWeek == Carbon::SUNDAY) return false;

        // Saturday
        if ($carbonDate->dayOfWeek == Carbon::SATURDAY) {
            $dayOfMonth = $carbonDate->day;
            $weekNumber = ceil($dayOfMonth / 7);
            
            // 2nd and 4th Saturday Off
            if ($weekNumber == 2 || $weekNumber == 4) return false;
        }

        return true;
    }

    public function applyMonthlyRules($userId, $month, $year)
    {
        // This function would run at the end of the month or on-demand
        $attendances = Attendance::where('user_id', $userId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $lateCount = $attendances->where('is_late', true)->count();
        $earlyOutCount = $attendances->where('is_early_out', true)->count();
        $missedPunchCount = 0; // Calculate based on missing clock_out on working days
        
        // Penalties logic here if needed for monthly calculation
    }
}
