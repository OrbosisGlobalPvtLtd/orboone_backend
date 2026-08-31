<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('routeActive')) {

    function routeActive($route)
    {
        return Request::routeIs($route) ? 'active' : '';
    }
}

if (!function_exists('convertNumberToWords')) {
    function convertNumberToWords($number)
    {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convertNumberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[(int) $hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . convertNumberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= convertNumberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}

if (!function_exists('resolveEmployeeAvatar')) {
    /**
     * Resolves the secure profile image URL for an employee or user.
     * Priority:
     * 1. Employee profile photo (via secure private route)
     * 2. User profile image (public storage / absolute path)
     * 3. Fallback to null
     *
     * @param mixed $entity
     * @return string|null
     */
    function resolveEmployeeAvatar($entity)
    {
        if (empty($entity)) {
            return null;
        }

        $employee = null;
        $user = null;

        // Determine if entity is employee, user, or has relations
        if ($entity instanceof \App\Models\HRMS\Employee\EmployeeM) {
            $employee = $entity;
            $user = $entity->user;
        } elseif ($entity instanceof \App\Models\Core\UserM) {
            $user = $entity;
            $employee = $entity->employee;
        } elseif (is_object($entity)) {
            // Check for common relations on general models (like Attendance, Leave request etc)
            if (isset($entity->employee) && $entity->employee instanceof \App\Models\HRMS\Employee\EmployeeM) {
                $employee = $entity->employee;
            }
            if (isset($entity->user) && $entity->user instanceof \App\Models\Core\UserM) {
                $user = $entity->user;
                if (!$employee && $user->employee) {
                    $employee = $user->employee;
                }
            }
            // Fallback for objects that might have user/employee properties but not Eloquent models
            if (!$employee && isset($entity->employee_id)) {
                try {
                    $employee = \App\Models\HRMS\Employee\EmployeeM::find($entity->employee_id);
                } catch (\Throwable $e) {}
            }
            if (!$user && isset($entity->user_id)) {
                try {
                    $user = \App\Models\Core\UserM::find($entity->user_id);
                } catch (\Throwable $e) {}
            }
        }

        // 1. Check Employee Profile Photo
        if ($employee) {
            // Ensure profile relation is loaded or queried
            try {
                $profile = $employee->profile ?: \App\Models\HRMS\Employee\EmployeeProfileM::where('employee_id', $employee->id)->first();
                $profileImage = $profile ? trim((string)$profile->profile_image) : '';
                if ($profileImage !== '') {
                    // Return secure private file server route
                    return route('employee.profile-image', ['employee' => $employee->id]);
                }
            } catch (\Throwable $e) {}
        }

        // 2. Check User Profile Photo
        if ($user) {
            try {
                $userImage = trim((string)data_get($user, 'profile_image'));
                if ($userImage !== '') {
                    if (preg_match('/^https?:\/\//i', $userImage) || substr($userImage, 0, 1) === '/') {
                        return $userImage;
                    } elseif (substr($userImage, 0, 8) === 'storage/') {
                        return asset($userImage);
                    } else {
                        return asset('storage/' . $userImage);
                    }
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }
}

if (!function_exists('resolveEmployeePassportPhoto')) {
    /**
     * Resolves the secure passport size photo document URL for an employee.
     * Matches "Passport Size Photo", "passport_size_photo", "Passport Photo", "passport_photo", "Photo", "Passport".
     *
     * @param mixed $employeeOrUser
     * @return string|null
     */
    function resolveEmployeePassportPhoto($employeeOrUser)
    {
        if (empty($employeeOrUser)) {
            return null;
        }

        global $preloadedPassportPhotos;
        if (isset($preloadedPassportPhotos)) {
            $empId = null;
            if (is_object($employeeOrUser) && isset($employeeOrUser->id)) {
                $empId = $employeeOrUser->id;
            } elseif (is_numeric($employeeOrUser)) {
                $empId = (int)$employeeOrUser;
            }
            if ($empId !== null && array_key_exists($empId, $preloadedPassportPhotos)) {
                return $preloadedPassportPhotos[$empId];
            }
        }

        $employee = null;
        if ($employeeOrUser instanceof \App\Models\HRMS\Employee\EmployeeM) {
            $employee = $employeeOrUser;
        } elseif ($employeeOrUser instanceof \App\Models\Core\UserM) {
            $employee = $employeeOrUser->employee;
        } elseif (is_numeric($employeeOrUser)) {
            try {
                $employee = \App\Models\HRMS\Employee\EmployeeM::find($employeeOrUser);
                if (!$employee) {
                    $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $employeeOrUser)->first();
                }
            } catch (\Throwable $e) {}
        } elseif (is_object($employeeOrUser)) {
            if (isset($employeeOrUser->employee) && $employeeOrUser->employee instanceof \App\Models\HRMS\Employee\EmployeeM) {
                $employee = $employeeOrUser->employee;
            } elseif (isset($employeeOrUser->employee_id) && !empty($employeeOrUser->employee_id)) {
                try {
                    $employee = \App\Models\HRMS\Employee\EmployeeM::find($employeeOrUser->employee_id);
                } catch (\Throwable $e) {}
            } elseif (isset($employeeOrUser->user_id) && !empty($employeeOrUser->user_id)) {
                try {
                    $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $employeeOrUser->user_id)->first();
                } catch (\Throwable $e) {}
            } elseif (isset($employeeOrUser->id) && !empty($employeeOrUser->id)) {
                try {
                    $employee = \App\Models\HRMS\Employee\EmployeeM::find($employeeOrUser->id);
                    if (!$employee) {
                        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $employeeOrUser->id)->first();
                    }
                } catch (\Throwable $e) {}
            }
        }

        if (!$employee) {
            return null;
        }

        static $passportPhotoCache = [];
        if (array_key_exists($employee->id, $passportPhotoCache)) {
            return $passportPhotoCache[$employee->id];
        }

        try {
            $document = \App\Models\HRMS\Document\EmployeeDocumentM::where('employee_id', $employee->id)
                ->whereHas('documentType', function ($query) {
                    $query->where(function ($q) {
                        $q->where('name', 'Passport Size Photo')
                          ->orWhere('code', 'passport_size_photo')
                          ->orWhere('name', 'Passport Photo')
                          ->orWhere('code', 'passport_photo')
                          ->orWhere('name', 'Photo')
                          ->orWhere('name', 'Passport')
                          ->orWhere('name', 'like', '%Passport%Photo%')
                          ->orWhere('name', 'like', '%Passport%Size%Photo%');
                    });
                })
                ->orderByRaw("CASE WHEN verification_status = 'verified' THEN 0 ELSE 1 END")
                ->orderBy('id', 'desc')
                ->first();

            if ($document && $document->file_path) {
                $url = route('hrms.documents.file', ['path' => $document->file_path]);
                $passportPhotoCache[$employee->id] = $url;
                return $url;
            }
        } catch (\Throwable $e) {}

        $passportPhotoCache[$employee->id] = null;
        return null;
    }
}

if (!function_exists('resolveEmployeeAdminAvatar')) {
    /**
     * Resolves the passport-size photo URL for admin-facing views.
     *
     * @param mixed $employeeOrUser
     * @return string|null
     */
    function resolveEmployeeAdminAvatar($employeeOrUser)
    {
        return resolveEmployeePassportPhoto($employeeOrUser);
    }
}

if (!function_exists('resolveEmployeeInitials')) {
    /**
     * Resolves fallback initials for a user or employee.
     *
     * @param mixed $entity
     * @return string
     */
    function resolveEmployeeInitials($entity)
    {
        if (empty($entity)) {
            return 'U';
        }

        $name = '';
        if ($entity instanceof \App\Models\HRMS\Employee\EmployeeM) {
            $name = $entity->display_name ?: ($entity->user ? $entity->user->name : '');
        } elseif ($entity instanceof \App\Models\Core\UserM) {
            $name = $entity->name;
        } elseif (is_object($entity)) {
            if (isset($entity->user) && $entity->user) {
                $name = $entity->user->name;
            } elseif (isset($entity->employee) && $entity->employee) {
                $name = $entity->employee->display_name;
            } elseif (isset($entity->name)) {
                $name = $entity->name;
            }
        }

        $name = trim($name);
        return $name !== '' ? strtoupper(substr($name, 0, 1)) : 'U';
    }
}

if (!function_exists('branding_name')) {
    /**
     * Get the dynamic company/portal branding name with config/hardcoded fallbacks.
     *
     * @return string
     */
    function branding_name()
    {
        try {
            $branding = \App\Services\Core\Branding\BrandingSettingsS::get();
            if (!empty($branding['company_name'])) {
                return $branding['company_name'];
            }
        } catch (\Throwable $e) {}

        return config('app.name') ?: 'OrboOne HRMS';
    }
}

if (!function_exists('branding_logo')) {
    /**
     * Get the dynamic company/portal branding logo URL with config/hardcoded fallbacks.
     *
     * @return string
     */
    function branding_logo()
    {
        try {
            $branding = \App\Services\Core\Branding\BrandingSettingsS::get();
            $logoUrl = !empty($branding['logo_url']) ? $branding['logo_url'] : asset('images/Picsart_26-04-02_12-19-10-396.png');
        } catch (\Throwable $e) {
            $logoUrl = asset('images/Picsart_26-04-02_12-19-10-396.png');
        }

        // If running locally (localhost / 127.0.0.1) and it is the default logo,
        // map it to the public domain so email clients like Gmail can load it.
        if (preg_match('/(localhost|127\.0\.0\.1|::1)/i', $logoUrl)) {
            if (str_contains($logoUrl, 'Picsart_26-04-02_12-19-10-396.png')) {
                return 'https://orboone.orbosis.in/public/images/Picsart_26-04-02_12-19-10-396.png';
            }
        }

        return $logoUrl;
    }
}

if (!function_exists('branding_logo_path')) {
    /**
     * Get the absolute local path to the company/portal branding logo.
     *
     * @return string
     */
    function branding_logo_path()
    {
        try {
            $settings = \App\Services\Core\Branding\BrandingSettingsS::cache();
            if (!empty($settings['branding.logo_path'])) {
                $logoPath = $settings['branding.logo_path'];
                if (!str_starts_with($logoPath, 'http://') && !str_starts_with($logoPath, 'https://')) {
                    $filename = basename($logoPath);
                    $path = storage_path("app/public/branding/logo/{$filename}");
                    if (file_exists($path) && is_file($path)) {
                        return $path;
                    }
                }
            }
        } catch (\Throwable $e) {}

        return public_path('images/Picsart_26-04-02_12-19-10-396.png');
    }
}

if (!function_exists('company_name')) {
    /**
     * Get the company name from company settings, with a fallback to branding name.
     *
     * @return string
     */
    function company_name()
    {
        try {
            $company = \Illuminate\Support\Facades\DB::table('company_settings')->first();
            if ($company && !empty($company->company_name)) {
                return $company->company_name;
            }
        } catch (\Throwable $e) {}

        return branding_name();
    }
}

if (!function_exists('branding_primary_color')) {
    /**
     * Get the dynamic branding primary color.
     *
     * @return string
     */
    function branding_primary_color()
    {
        try {
            $branding = \App\Services\Core\Branding\BrandingSettingsS::get();
            if (!empty($branding['primary_color'])) {
                return $branding['primary_color'];
            }
        } catch (\Throwable $e) {}

        return '#4B00E8';
    }
}

if (!function_exists('branding_secondary_color')) {
    /**
     * Get the dynamic branding secondary color.
     *
     * @return string
     */
    function branding_secondary_color()
    {
        try {
            $branding = \App\Services\Core\Branding\BrandingSettingsS::get();
            if (!empty($branding['secondary_color'])) {
                return $branding['secondary_color'];
            }
        } catch (\Throwable $e) {}

        return '#FF5252';
    }
}

if (!function_exists('branding_logo_url_or_embed')) {
    /**
     * Get the dynamic branding logo URL, or embed it if running in a local environment.
     *
     * @param mixed $message
     * @return string
     */
    function branding_logo_url_or_embed($message = null)
    {
        $logoUrl = branding_logo();

        // If in production or not on a local server, return the public URL directly
        // so that the email client loads it directly without attaching it as a file.
        $isLocal = app()->environment('local', 'testing') 
            || preg_match('/(localhost|127\.0\.0\.1|10\.\d+\.\d+\.\d+|::1)/i', $logoUrl);

        if ($isLocal && $message) {
            try {
                return $message->embed(branding_logo_path());
            } catch (\Throwable $e) {}
        }

        return $logoUrl;
    }
}

if (!function_exists('formatWorkReportRow')) {
    /**
     * Standardize and format a daily work report entry for table, print, PDF, and excel views.
     *
     * @param mixed $log
     * @return array
     */
    function formatWorkReportRow($log)
    {
        if (!$log) {
            return [];
        }

        // 1. Employee Name and Code
        $emp = is_object($log) && isset($log->employee) ? $log->employee : null;
        $user = is_object($log) && isset($log->user) ? $log->user : null;
        
        $empName = optional($user)->name 
            ?? (is_object($log) && isset($log->display_name) ? $log->display_name : null)
            ?? optional($emp)->name 
            ?? 'Employee';

        $empCode = (is_object($log) && isset($log->employee_code) ? $log->employee_code : null)
            ?? optional($emp)->employee_code 
            ?? 'N/A';

        $employeeDisplay = "{$empName} ({$empCode})";

        // 2. Date
        $workDateRaw = is_object($log) && isset($log->work_date) ? $log->work_date : null;
        $dateCarbon = null;
        if ($workDateRaw instanceof \Carbon\Carbon || $workDateRaw instanceof \DateTimeInterface) {
            $dateCarbon = $workDateRaw;
        } elseif (!empty($workDateRaw)) {
            try {
                $dateCarbon = \Carbon\Carbon::parse($workDateRaw);
            } catch (\Throwable $e) {}
        }
        $dateDisplay = $dateCarbon ? $dateCarbon->format('d M Y') : '-';
        $dayName = $dateCarbon ? $dateCarbon->format('l') : '';
        $dateRaw = $dateCarbon ? $dateCarbon->format('Y-m-d') : '';

        // Submitted time
        $submittedAtRaw = is_object($log) && isset($log->created_at) ? $log->created_at : null;
        $submittedTime = '-';
        if ($submittedAtRaw instanceof \Carbon\Carbon || $submittedAtRaw instanceof \DateTimeInterface) {
            $submittedTime = $submittedAtRaw->format('h:i A');
        } elseif (!empty($submittedAtRaw)) {
            try {
                $submittedTime = \Carbon\Carbon::parse($submittedAtRaw)->format('h:i A');
            } catch (\Throwable $e) {}
        }

        // 3. Work Mode
        $att = is_object($log) && isset($log->attendance) ? $log->attendance : null;
        $modeRaw = (is_object($att) && isset($att->work_mode) ? $att->work_mode : null)
            ?? (is_object($log) && isset($log->work_mode) ? $log->work_mode : 'wfo');
        $modeDisplay = strtoupper($modeRaw ?: 'WFO');

        // 4. Shift Context
        $shiftTime = is_object($att) && isset($att->attendanceTime) ? $att->attendanceTime : null;
        $shiftName = optional($shiftTime)->name 
            ?? (is_object($att) && isset($att->shift_name) ? $att->shift_name : null)
            ?? (is_object($log) && isset($log->shift_name) ? $log->shift_name : null)
            ?? 'General Shift';
        $shiftContext = $shiftName;

        // 5. Gross Work
        $grossRaw = is_object($att) && isset($att->gross_duration) ? $att->gross_duration : null;
        if (!$grossRaw && is_object($log) && !empty($log->duration_minutes)) {
            $h = floor($log->duration_minutes / 60);
            $m = $log->duration_minutes % 60;
            $grossRaw = ($h > 0 ? "{$h} hours " : "") . "{$m} mins";
        }
        if ($grossRaw && preg_match('/(?:(\d+)\s*h(?:ours?|rs?)?)?\s*(?:(\d+)\s*m(?:ins?|inutes?)?)?/i', $grossRaw, $m)) {
            $hrs = (int)($m[1] ?? 0);
            $mins = (int)($m[2] ?? 0);
            if ($hrs > 0 && $mins > 0) {
                $grossDisplay = "{$hrs} hours {$mins} mins";
            } elseif ($hrs > 0) {
                $grossDisplay = "{$hrs} hours";
            } else {
                $grossDisplay = "{$mins} mins";
            }
        } else {
            $grossDisplay = $grossRaw ?: '-';
        }

        // 6 & 7. Tasks & Summary parsing
        $tasks = is_object($log) && isset($log->work_summary_json) ? $log->work_summary_json : null;
        if (is_string($tasks)) {
            $tasks = json_decode($tasks, true);
        }

        $title = (is_object($log) && isset($log->project_name) ? $log->project_name : null);
        $description = null;
        $status = 'Completed';
        $structuredTasks = [];
        $projectsList = [];

        if (is_array($tasks)) {
            if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                $projectsList = $tasks['projects'];
                foreach ($tasks['projects'] as $p) {
                    $pName = $p['project_name'] ?? ($p['name'] ?? null);
                    if (!$title && $pName) $title = $pName;
                    if (isset($p['tasks']) && is_array($p['tasks'])) {
                        foreach ($p['tasks'] as $t) {
                            $tName = $t['task_name'] ?? ($t['description'] ?? ($t['task'] ?? ($t['title'] ?? 'Task')));
                            $tDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true || $t['is_completed'] === 'true') : (isset($t['completed']) ? ($t['completed'] == 1 || $t['completed'] === true || $t['completed'] === 'true') : true));
                            $structuredTasks[] = [
                                'text' => trim($tName),
                                'done' => (bool)$tDone,
                                'project' => $pName,
                                'formatted' => ($tDone ? '[Done] ' : '[Pending] ') . trim($tName)
                            ];
                        }
                    }
                }
            }

            if (empty($structuredTasks)) {
                $reqs = $tasks['requirements'] ?? ($tasks['tasks'] ?? []);
                if (is_array($reqs)) {
                    foreach ($reqs as $r) {
                        $rText = is_string($r) ? $r : ($r['text'] ?? ($r['task_name'] ?? ($r['task'] ?? ($r['title'] ?? ($r['description'] ?? '')))));
                        $rDone = is_array($r) ? (isset($r['done']) ? ($r['done'] === true || $r['done'] === 'true' || $r['done'] == 1) : true) : true;
                        if (!empty($rText)) {
                            $structuredTasks[] = [
                                'text' => trim($rText),
                                'done' => (bool)$rDone,
                                'project' => $title,
                                'formatted' => ($rDone ? '[Done] ' : '[Pending] ') . trim($rText)
                            ];
                        }
                    }
                }
            }

            if (!$title) {
                $title = $tasks['title'] ?? ($tasks['task_name'] ?? null);
            }

            $status = $tasks['today_work_status'] ?? ($tasks['current_status'] ?? ($tasks['status'] ?? 'Completed'));
            $description = $tasks['description'] ?? ($tasks['today_work_description'] ?? null);
        }

        if (!$description) {
            $description = is_object($log) && isset($log->work_summary) ? $log->work_summary : (is_object($log) && isset($log->work_description) ? $log->work_description : null);
        }

        // Clean description lines & separate into distinct paragraphs
        if ($description) {
            $rawLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $description));
            $cleanedParagraphs = [];
            $currentParagraph = [];

            foreach ($rawLines as $l) {
                $trimmed = trim($l);
                if (empty($trimmed)) {
                    if (!empty($currentParagraph)) {
                        $cleanedParagraphs[] = implode(' ', $currentParagraph);
                        $currentParagraph = [];
                    }
                    continue;
                }

                // Ignore boilerplate status/test markers
                if (stripos($trimmed, 'Project:') === 0) continue;
                if (stripos($trimmed, "Today's Work Status:") === 0 || stripos($trimmed, "Today Work Status:") === 0) continue;
                if (stripos($trimmed, "Status") === 0 && in_array(strtolower($trimmed), ['status', 'status:', 'status :'])) continue;
                if (in_array(strtolower($trimmed), ['completed', 'in-progress', 'in_progress', 'testing', 'done'])) continue;
                if (stripos($trimmed, "Requirements") === 0) continue;
                if (stripos($trimmed, "Test Status") === 0) continue;
                if (stripos($trimmed, "Tested:") === 0) continue;
                if (stripos($trimmed, "Completed:") === 0) continue;
                if (stripos($trimmed, "Issues") === 0) continue;
                if (stripos($trimmed, "Notes") === 0) continue;
                if (in_array(strtolower($trimmed), ['none', 'no issues'])) continue;

                // Strip leading checkboxes or bullet icons
                if (preg_match('/^[☑🗹☐✓✔•\-*]\s*/u', $trimmed)) {
                    $trimmed = preg_replace('/^[☑🗹☐✓✔•\-*]\s*/u', '', $trimmed);
                }

                if (!empty($trimmed)) {
                    $currentParagraph[] = $trimmed;
                }
            }
            if (!empty($currentParagraph)) {
                $cleanedParagraphs[] = implode(' ', $currentParagraph);
            }

            if (count($cleanedParagraphs) > 0) {
                // If the first line is the project title, merge it nicely with the next line or keep clean
                if (!empty($title) && strtolower(trim($cleanedParagraphs[0])) === strtolower(trim($title))) {
                    if (count($cleanedParagraphs) > 1) {
                        $cleanedParagraphs[1] = "{$title} - {$cleanedParagraphs[1]}";
                        array_shift($cleanedParagraphs);
                    }
                } elseif (!empty($title) && stripos($cleanedParagraphs[0], $title) === false && count($cleanedParagraphs) <= 2) {
                    $cleanedParagraphs[0] = "{$title} - {$cleanedParagraphs[0]}";
                }
                $description = implode("\n\n", $cleanedParagraphs);
            }
        }

        if (empty($description)) {
            if (!empty($title)) {
                $description = $title;
            } else {
                $description = 'Work report submitted.';
            }
        }

        $structuredTaskLines = array_column($structuredTasks, 'formatted');
        if (empty($structuredTaskLines)) {
            $structuredTaskLines = ['[Done] ' . ($title ?: 'Work completed')];
        }

        return [
            'id' => is_object($log) && isset($log->id) ? $log->id : null,
            'employee' => $employeeDisplay,
            'employee_name' => $empName,
            'employee_code' => $empCode,
            'department' => optional(optional($emp)->department)->name ?? (is_object($log) && isset($log->department_name) ? $log->department_name : 'Staff'),
            'designation' => optional(optional($emp)->designation)->name ?? (is_object($log) && isset($log->designation_name) ? $log->designation_name : 'Member'),
            'date' => $dateDisplay,
            'date_raw' => $dateRaw,
            'day_name' => $dayName,
            'submitted_time' => $submittedTime,
            'mode' => $modeDisplay,
            'shift_context' => $shiftContext,
            'gross_work' => $grossDisplay,
            'title' => $title,
            'status' => $status,
            'summary_desc' => $description,
            'summary_paragraphs' => explode("\n\n", $description),
            'structured_tasks' => $structuredTasks,
            'structured_task_lines' => $structuredTaskLines,
            'structured_tasks_text' => implode("\n", $structuredTaskLines),
        ];
    }
}


