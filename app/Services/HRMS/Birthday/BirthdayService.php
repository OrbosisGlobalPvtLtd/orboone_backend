<?php

namespace App\Services\HRMS\Birthday;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class BirthdayService
{
    protected HrmsFileResolverS $fileResolver;
    protected BirthdayShareService $shareService;

    public function __construct(HrmsFileResolverS $fileResolver, BirthdayShareService $shareService)
    {
        $this->fileResolver = $fileResolver;
        $this->shareService = $shareService;
    }

    /**
     * Get active employees celebrating their birthday today.
     *
     * @param Carbon|null $date
     * @return array
     */
    public function getTodayBirthdays(?Carbon $date = null): array
    {
        $today = $date ?: Carbon::now(config('app.timezone', 'Asia/Kolkata'));
        $month = $today->month;
        $day = $today->day;

        $employees = EmployeeM::query()
            ->active()
            ->whereHas('profile', function ($query) use ($month, $day) {
                $query->whereMonth('date_of_birth', $month)
                      ->whereDay('date_of_birth', $day);
            })
            ->with([
                'user:id,name',
                'profile:id,employee_id,profile_image,date_of_birth',
                'department:id,name',
                'designation:id,name',
            ])
            ->get();

        return $employees->map(fn($emp) => $this->transformEmployee($emp, true))->values()->toArray();
    }

    /**
     * Get active employees celebrating their birthday within the next N days (excluding today).
     *
     * @param int $days
     * @param Carbon|null $date
     * @return array
     */
    public function getUpcomingBirthdays(int $days = 7, ?Carbon $date = null): array
    {
        $today = $date ?: Carbon::now(config('app.timezone', 'Asia/Kolkata'));
        $upcomingList = [];

        for ($i = 1; $i <= $days; $i++) {
            $targetDate = $today->copy()->addDays($i);
            $month = $targetDate->month;
            $day = $targetDate->day;

            $employees = EmployeeM::query()
                ->active()
                ->whereHas('profile', function ($query) use ($month, $day) {
                    $query->whereMonth('date_of_birth', $month)
                          ->whereDay('date_of_birth', $day);
                })
                ->with([
                    'user:id,name',
                    'profile:id,employee_id,profile_image,date_of_birth',
                    'department:id,name',
                    'designation:id,name',
                ])
                ->get();

            foreach ($employees as $emp) {
                $data = $this->transformEmployee($emp, false);
                $data['date_of_birth'] = $targetDate->format('d M');
                $data['days_left'] = $i;
                $upcomingList[] = $data;
            }
        }

        return $upcomingList;
    }

    /**
     * Check if the given logged-in user is an active employee whose birthday is today.
     * Returns celebration info and secure share URL if true.
     *
     * @param mixed $user
     * @return array|null
     */
    public function getOwnBirthdayStatus($user): ?array
    {
        if (!$user) {
            return null;
        }

        $employee = EmployeeM::query()
            ->active()
            ->where('user_id', $user->id)
            ->with(['user', 'profile', 'department', 'designation'])
            ->first();

        if (!$employee || !$employee->profile || !$employee->profile->date_of_birth) {
            return null;
        }

        $today = Carbon::now(config('app.timezone', 'Asia/Kolkata'));
        $dob = Carbon::parse($employee->profile->date_of_birth);

        if ($dob->month === $today->month && $dob->day === $today->day) {
            $shareToken = $this->shareService->generateToken($employee);
            $shareUrl = Route::has('public.birthday.share')
                ? route('public.birthday.share', ['token' => $shareToken])
                : url("/birthday/{$shareToken}");

            $data = $this->transformEmployee($employee, true);
            $data['is_birthday_today'] = true;
            $data['share_token']       = $shareToken;
            $data['share_url']         = $shareUrl;

            return $data;
        }

        return null;
    }

    /**
     * Standardized employee array formatter.
     */
    protected function transformEmployee(EmployeeM $emp, bool $isToday = false): array
    {
        $imagePath = $emp->profile?->profile_image ?? null;
        $imageUrl = $this->resolvePhotoUrl($imagePath);

        $shareToken = $this->shareService->generateToken($emp);
        $shareUrl = Route::has('public.birthday.share')
            ? route('public.birthday.share', ['token' => $shareToken])
            : url("/birthday/{$shareToken}");

        return [
            'id'            => $emp->id,
            'employee_id'   => $emp->employee_id ?? $emp->id,
            'employee_code' => $emp->employee_code,
            'name'          => $emp->user->name ?? $emp->display_name ?? 'Team Member',
            'department'    => $emp->department->name ?? 'Orbosis Global',
            'designation'   => $emp->designation->name ?? '',
            'image_url'     => $imageUrl,
            'date_of_birth' => $emp->profile?->date_of_birth ? Carbon::parse($emp->profile->date_of_birth)->format('d M') : null,
            'is_today'      => $isToday,
            'share_token'   => $shareToken,
            'share_url'     => $shareUrl,
        ];
    }

    /**
     * Helper to resolve profile photo URL using standard web route if available.
     */
    protected function resolvePhotoUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (Route::has('hrms.documents.file')) {
            return route('hrms.documents.file', ['path' => $path]);
        }

        return $this->fileResolver->secureFileUrl($path);
    }
}
