<?php

namespace App\Console;

use App\Console\Commands\AttendanceAbsent;
use App\Console\Commands\AttendanceLeave;
use App\Console\Commands\AutoCloseMissedAttendance;
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
        AutoCloseMissedAttendance::class,
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
        $schedule->command('attendance:auto-close-missed-punchouts')->dailyAt('00:10');
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
