<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\Core\AccessM;
use App\Models\HRMS\Leave\HolidayM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayC extends Controller
{
    public function index(Request $request)
    {
        $currentYear = (int) date('Y');
        $selectedYear = $request->filled('year') ? (int) $request->year : $currentYear;

        $dbYears = HolidayM::selectRaw('DISTINCT YEAR(holiday_date) as y')
            ->pluck('y')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->toArray();

        $availableYears = array_unique(array_merge(
            $dbYears,
            [$currentYear - 1, $currentYear, $currentYear + 1, $currentYear + 2]
        ));
        sort($availableYears);

        $holidays = HolidayM::whereYear('holiday_date', $selectedYear)
            ->orderBy('holiday_date', 'asc')
            ->get();

        $holidaysByMonth = $holidays->groupBy(function ($h) {
            return (int) optional($h->holiday_date)->format('n');
        });

        $today = now()->toDateString();
        $nextHoliday = $holidays->first(fn ($h) => optional($h->holiday_date)->format('Y-m-d') >= $today);

        $accesses = $this->accesses();

        return view('hrms.leave.holidays.index', compact(
            'holidays',
            'selectedYear',
            'availableYears',
            'holidaysByMonth',
            'nextHoliday',
            'accesses'
        ))->with('active', 'leave_management');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:180',
            'holiday_date' => 'required|date',
            'holiday_type' => 'nullable|string|max:80',
            'is_national' => 'nullable|boolean',
            'is_optional' => 'nullable|boolean',
            'is_working_day_override' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        HolidayM::updateOrCreate(
            ['holiday_date' => $data['holiday_date'], 'title' => $data['title']],
            array_merge($data, [
                'created_by_user_id' => Auth::id(),
                'is_national' => $request->boolean('is_national'),
                'is_optional' => $request->boolean('is_optional'),
                'is_working_day_override' => $request->boolean('is_working_day_override'),
                'is_active' => $request->boolean('is_active', true),
            ])
        );

        return back()->with('success', 'Holiday saved successfully.');
    }

    public function update(Request $request, $id)
    {
        $holiday = HolidayM::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:180',
            'holiday_date' => 'required|date',
            'holiday_type' => 'nullable|string|max:80',
            'is_national' => 'nullable|boolean',
            'is_optional' => 'nullable|boolean',
            'is_working_day_override' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $holiday->update([
            'title' => $data['title'],
            'holiday_date' => $data['holiday_date'],
            'holiday_type' => $data['holiday_type'] ?? 'company',
            'is_national' => $request->boolean('is_national'),
            'is_optional' => $request->boolean('is_optional'),
            'is_working_day_override' => $request->boolean('is_working_day_override'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Holiday updated successfully.');
    }

    public function destroy($id)
    {
        HolidayM::findOrFail($id)->delete();

        return back()->with('success', 'Holiday removed successfully.');
    }

    public function cloneYear(Request $request)
    {
        $request->validate([
            'source_year' => 'required|integer',
            'target_year' => 'required|integer|different:source_year',
        ]);

        $sourceYear = (int) $request->source_year;
        $targetYear = (int) $request->target_year;

        $sourceHolidays = HolidayM::whereYear('holiday_date', $sourceYear)->get();

        if ($sourceHolidays->isEmpty()) {
            return back()->with('error', "No holidays found in {$sourceYear} to copy.");
        }

        $created = 0;
        foreach ($sourceHolidays as $holiday) {
            $date = \Carbon\Carbon::parse($holiday->holiday_date);
            $newDate = $date->copy()->year($targetYear)->format('Y-m-d');

            $exists = HolidayM::where('holiday_date', $newDate)
                ->where('title', $holiday->title)
                ->exists();

            if (!$exists) {
                HolidayM::create([
                    'title' => $holiday->title,
                    'holiday_date' => $newDate,
                    'holiday_type' => $holiday->holiday_type,
                    'is_national' => (bool) $holiday->is_national,
                    'is_optional' => (bool) $holiday->is_optional,
                    'is_working_day_override' => (bool) $holiday->is_working_day_override,
                    'is_active' => (bool) $holiday->is_active,
                    'created_by_user_id' => Auth::id(),
                ]);
                $created++;
            }
        }

        return redirect()->route('hrms.holidays.index', ['year' => $targetYear])
            ->with('success', "Copied {$created} holidays from {$sourceYear} to {$targetYear}. You can now review and adjust festival dates.");
    }

    private function accesses()
    {
        $roleId = Auth::user()->role_id ?? Auth::user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
