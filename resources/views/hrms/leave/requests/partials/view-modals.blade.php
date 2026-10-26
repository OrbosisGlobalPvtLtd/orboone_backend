<!-- ==================================================
     VIEW DETAILS MODALS (Same Rich UI as Leave Approvals)
     ================================================== -->
@foreach($requests as $lr)
    @php
        $ltName = $lr->leave_type_name ?? ($lr->leaveType?->name ?? 'Leave');
        $ltLower = strtolower($ltName);
        $ltStyle = match(true) {
            str_contains($ltLower, 'sick') => 'background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;',
            str_contains($ltLower, 'casual') => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;',
            str_contains($ltLower, 'comp') => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
            str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD;',
            default => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;'
        };

        $stLower = strtolower(trim($lr->status ?? 'pending'));
        $startDateFormatted = !empty($lr->start_date) ? \Carbon\Carbon::parse($lr->start_date)->format('d M Y') : '—';
        $endDateFormatted = !empty($lr->end_date) ? \Carbon\Carbon::parse($lr->end_date)->format('d M Y') : '—';
        $isSingleDay = (!empty($lr->start_date) && !empty($lr->end_date) && $lr->start_date === $lr->end_date);

        $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
        $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

        $managerEmpId = $lr->current_reporting_manager_id ?? ($lr->employee?->reporting_manager_employee_id ?? null);
        $hasManager = !empty($managerEmpId);

        $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
        $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
        $hrApproved = ($stLower === 'approved' && (!empty($lr->hr_approved_by) || !empty($lr->hr_approved_at)));
        $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));

        $canApprove = false;
        $canReject = false;
        $isAssignedManager = false;
    @endphp

    @include('hrms.leave.approvals.partials.view-modal', compact(
        'lr',
        'ltName',
        'ltStyle',
        'stLower',
        'startDateFormatted',
        'endDateFormatted',
        'isSingleDay',
        'daysText',
        'hasManager',
        'isAssignedManager',
        'isSuperAdminUser',
        'isHrAdminUser',
        'mgrApproved',
        'mgrRejected',
        'hrApproved',
        'hrRejected',
        'canApprove',
        'canReject'
    ))
@endforeach
