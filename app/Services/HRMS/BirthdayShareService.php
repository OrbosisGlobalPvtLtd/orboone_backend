<?php

namespace App\Services\HRMS;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class BirthdayShareService
{
    /**
     * Generate a secure, non-guessable share token for an employee.
     */
    public function generateToken(EmployeeM $employee): string
    {
        $payload = [
            'emp_id' => $employee->employee_id,
            'code' => $employee->employee_code,
            'created' => time(),
        ];

        return base64_encode(Crypt::encryptString(json_encode($payload)));
    }

    /**
     * Resolve employee data from a secure share token.
     */
    public function resolveToken(string $token): ?array
    {
        try {
            $decodedToken = base64_decode($token);
            if (!$decodedToken) {
                return null;
            }

            $decrypted = Crypt::decryptString($decodedToken);
            $payload = json_decode($decrypted, true);

            if (!isset($payload['emp_id'])) {
                return null;
            }

            $employee = EmployeeM::with(['user', 'employeeDetail', 'department', 'designation'])
                ->where('employee_id', $payload['emp_id'])
                ->first();

            if (!$employee || !$employee->user) {
                return null;
            }

            $imageUrl = null;
            if ($employee->employeeDetail && $employee->employeeDetail->image) {
                $imagePath = $employee->employeeDetail->image;
                $imageUrl = url("/api/v1/file?path=" . urlencode($imagePath));
            }

            return [
                'employee_id' => $employee->employee_id,
                'name' => $employee->user->name ?? 'Team Member',
                'department' => $employee->department->name ?? 'Orbosis Global',
                'designation' => $employee->designation->name ?? '',
                'image_url' => $imageUrl,
            ];
        } catch (\Exception $e) {
            Log::warning('Birthday share token resolution failed: ' . $e->getMessage());
            return null;
        }
    }
}
