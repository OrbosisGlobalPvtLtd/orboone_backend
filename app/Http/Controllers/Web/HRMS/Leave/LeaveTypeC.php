<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Leave\StoreLeaveTypeRequest;
use App\Models\Core\AccessM;
use App\Models\HRMS\Leave\LeaveTypeM;

class LeaveTypeC extends Controller
{
    public function index()
    {
        $types = LeaveTypeM::orderBy('name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.types.index', compact('types', 'accesses'))->with('active', 'leave_management');
    }

    public function store(StoreLeaveTypeRequest $request)
    {
        LeaveTypeM::create($this->payload($request));

        return back()->with('success', 'Leave type saved successfully.');
    }

    public function update(StoreLeaveTypeRequest $request, $id)
    {
        LeaveTypeM::findOrFail($id)->update($this->payload($request));

        return back()->with('success', 'Leave type updated successfully.');
    }

    private function payload(StoreLeaveTypeRequest $request): array
    {
        return array_merge($request->validated(), [
            'is_paid' => $request->boolean('is_paid'),
            'is_sick' => $request->boolean('is_sick'),
            'is_lwp' => $request->boolean('is_lwp'),
            'is_comp_off' => $request->boolean('is_comp_off'),
            'requires_attachment' => $request->boolean('requires_attachment'),
            'allow_half_day' => $request->boolean('allow_half_day'),
            'applicable_after_confirmation' => $request->boolean('applicable_after_confirmation'),
            'is_active' => $request->boolean('is_active', true),
        ]);
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
