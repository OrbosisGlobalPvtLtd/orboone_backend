<?php

namespace App\Console;

use App\Console\Commands\AttendanceAbsent;
use App\Console\Commands\AttendanceLeave;
use App\Console\Commands\HRMS\AutoBlockMissedPunchIns;
use App\Console\Commands\HRMS\AutoCloseBlockedAttendance;
use App\Console\Commands\HRMS\ExpireCompOffs;
use App\Console\Commands\HRMS\GenerateLeaveAllocations;
use App\Console\Commands\HRMS\GenerateMonthlyAttendanceSummary;
use App\Console\Commands\HRMS\LapseYearEndLeaves;
use App\Console\Commands\HRMS\ProcessMissedPunches as HRMSProcessMissedPunches;
use App\Console\Commands\HRMS\RecalculateLeaveBalances;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        AttendanceAbsent::class,
        AttendanceLeave::class,
        AutoBlockMissedPunchIns::class,
        AutoCloseBlockedAttendance::class,
        HRMSProcessMissedPunches::class,
        GenerateLeaveAllocations::class,
        ExpireCompOffs::class,
        LapseYearEndLeaves::class,
        RecalculateLeaveBalances::class,
        GenerateMonthlyAttendanceSummary::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('attendance:leave')->dailyAt('08:30');
        $schedule->command('attendance:absent')->dailyAt('16:00');
        $schedule->command('attendance:mark-holidays')->dailyAt('08:00')->timezone('Asia/Kolkata');
        $schedule->command('attendance:mark-weekoffs')->dailyAt('08:05')->timezone('Asia/Kolkata');
        $schedule->command('hrms:auto-block-missed-punchins')->everyMinute()->timezone('Asia/Kolkata')->withoutOverlapping();
        $schedule->command('attendance:process-late-marks')->dailyAt('11:30')->timezone('Asia/Kolkata');
        $schedule->command('attendance:process-working-hours')->hourly()->timezone('Asia/Kolkata');
        $schedule->command('attendance:process-half-days')->dailyAt('21:30')->timezone('Asia/Kolkata');
        $schedule->command('hrms:process-missed-punches')->everyMinute()->timezone('Asia/Kolkata')->withoutOverlapping();
        $schedule->command('attendance:process-lwp')->dailyAt('23:55')->timezone('Asia/Kolkata');
        $schedule->command('hrms:auto-close-blocked-attendance')->everyMinute()->timezone('Asia/Kolkata')->withoutOverlapping();
        $schedule->command('hrms:leave-generate-allocations')->yearlyOn(1, 1, '00:05')->timezone('Asia/Kolkata');
        $schedule->command('hrms:comp-offs-expire')->dailyAt('00:20')->timezone('Asia/Kolkata');
        $schedule->command('hrms:leave-lapse-year-end')->yearlyOn(12, 31, '23:50')->timezone('Asia/Kolkata');
        $schedule->command('hrms:attendance-monthly-summary')->monthlyOn(1, '01:00')->timezone('Asia/Kolkata');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
