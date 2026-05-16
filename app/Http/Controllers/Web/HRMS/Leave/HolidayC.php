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
        $holidays = HolidayM::when($request->year, fn ($query) => $query->whereYear('holiday_date', $request->year))
            ->orderByDesc('holiday_date')
            ->paginate(30);
        $accesses = $this->accesses();

        return view('hrms.leave.holidays.index', compact('holidays', 'accesses'))->with('active', 'leave_management');
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

    public function destroy($id)
    {
        HolidayM::findOrFail($id)->delete();

        return back()->with('success', 'Holiday removed successfully.');
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
