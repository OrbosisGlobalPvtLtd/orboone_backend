<?php

namespace App\Services\HRMS\Birthday;

use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class BirthdayShareService
{
    
    public function generateToken(EmployeeM $employee): string
    {
        $id = $employee->id ?? $employee->employee_id ?? 0;
        $code = $employee->employee_code ?? '';
        $today = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d');
        $secretKey = config('app.key', 'orbosis_bday_sec_salt');

        $hash = substr(hash_hmac('sha256', "bday_card_{$id}_{$code}_{$today}", $secretKey), 0, 10);
        return 'card-' . $hash;
    }

    
    public function resolveToken(string $token): ?array
    {
        try {
            $tokenClean = trim($token);
            $secretKey = config('app.key', 'orbosis_bday_sec_salt');
            $tz = config('app.timezone', 'Asia/Kolkata');
            $now = Carbon::now($tz);

            $validDates = [
                $now->format('Y-m-d'),
                $now->copy()->subDay()->format('Y-m-d'),
            ];

            $employees = EmployeeM::with(['user', 'profile', 'department', 'designation'])->get();
            $matchedEmployee = null;

            foreach ($employees as $emp) {
                $id = $emp->id ?? $emp->employee_id ?? 0;
                $code = $emp->employee_code ?? '';

                foreach ($validDates as $dateStr) {
                    $expectedHash = 'card-' . substr(hash_hmac('sha256', "bday_card_{$id}_{$code}_{$dateStr}", $secretKey), 0, 10);
                    $expectedWishHash = 'wish-' . substr(hash_hmac('sha256', "bday_card_{$id}_{$code}_{$dateStr}", $secretKey), 0, 10);

                    if (hash_equals($expectedHash, $tokenClean) || hash_equals($expectedWishHash, $tokenClean)) {
                        $matchedEmployee = $emp;
                        break 2;
                    }
                }

                $legacyHash = 'card-' . substr(hash_hmac('sha256', "bday_card_{$id}_{$code}", $secretKey), 0, 10);
                if (hash_equals($legacyHash, $tokenClean)) {
                    if ($emp->profile && $emp->profile->date_of_birth) {
                        $dob = Carbon::parse($emp->profile->date_of_birth);
                        if ($dob->month === $now->month && $dob->day === $now->day) {
                            $matchedEmployee = $emp;
                            break;
                        }
                    }
                }
            }

            if (!$matchedEmployee || !$matchedEmployee->user) {
                return null;
            }

            $imagePath = $matchedEmployee->profile?->profile_image ?? null;
            $imageUrl = null;
            if ($imagePath) {
                try {
                    $imageUrl = route('hrms.documents.file', ['path' => $imagePath]);
                } catch (\Throwable $te) {
                    $imageUrl = url("/hrms/employee/file/" . ltrim($imagePath, '/'));
                }
            }

            return [
                'employee_id' => $matchedEmployee->id,
                'name'        => $matchedEmployee->user->name ?? 'Team Member',
                'department'  => $matchedEmployee->department->name ?? 'Orbosis Global Pvt. Ltd.',
                'designation' => $matchedEmployee->designation->name ?? '',
                'image_url'   => $imageUrl,
                'is_valid_24h'=> true,
            ];
        } catch (\Throwable $e) {
            Log::warning('Birthday share token resolution failed: ' . $e->getMessage());
            return null;
        }
    }
}
