<div class="leave-table-wrap">
    <div class="leave-table-responsive">
        <table id="leaveAllocationsTable" class="leave-table js-leave-allocations-table" style="width:100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 45px;">#</th>
                    <th class="text-left" style="min-width: 220px;">Employee</th>
                    <th class="text-center" style="min-width: 150px;">Stage & Policy</th>
                    <th class="text-center" style="min-width: 140px;">Total Allocated</th>
                    <th class="text-center" style="min-width: 140px;">Total Used</th>
                    <th class="text-center" style="min-width: 160px;">Remaining Balances</th>
                    <th class="text-center" style="min-width: 180px;">Monthly & Carry Forward</th>
                    <th class="text-center" style="min-width: 110px;">Status</th>
                    <th class="text-center" style="min-width: 90px; width: 90px;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($allocations as $allocation)
                    @include('hrms.leave.allocations.partials.allocation-row', ['allocation' => $allocation])
                @empty
                    <tr>
                        <td colspan="9">
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
