<div class="leave-table-wrap">
    <div class="leave-table-responsive">
        <table id="leaveAllocationsTable" class="leave-table js-leave-allocations-table" style="width:100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th class="text-left" style="min-width: 180px;">Employee</th>
                    <th class="text-center" style="min-width: 130px;">Stage & Policy</th>
                    <th class="text-center" style="min-width: 135px;">Annual Entitlement</th>
                    <th class="text-center" style="min-width: 140px;">Annual Remaining Balance</th>
                    <th class="text-center" style="min-width: 125px;">Accrued Till Date</th>
                    <th class="text-center" style="min-width: 135px;">Used (YTD)</th>
                    <th class="text-center" style="min-width: 140px;">Available This Month</th>
                    <th class="text-center" style="min-width: 135px;">Comp-Off</th>
                    <th class="text-center" style="min-width: 85px;">Status</th>
                    <th class="text-center" style="min-width: 110px; width: 110px;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($allocations as $allocation)
                    @include('hrms.leave.allocations.partials.allocation-row', ['allocation' => $allocation])
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <h6 class="font-weight-bold text-dark">No Leave Allocations Found</h6>
                                <p class="text-muted small mb-0">Generate allocation for year {{ $year }} above to get started.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="orb-table-footer p-0 border-0">
    {{ $allocations->links('vendor.pagination.orbo') }}
</div>
