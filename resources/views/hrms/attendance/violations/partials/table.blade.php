<!-- Entries & Export Toolbar -->
<div class="orb-table-tools-bar eo-toolbar">
    <div class="orb-table-length-box eo-toolbar-left">
        <div class="eo-entries-wrapper">
            <label class="eo-entries-label">
                Show
                <select id="recordsPerPageSelect" class="eo-entries-select">
                    @foreach([10, 25, 50, 100, 250] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                    <option value="all" {{ (request('per_page') === 'all' || request('per_page') == -1) ? 'selected' : '' }}>All</option>
                </select>
                entries
            </label>
        </div>
    </div>

    <div id="violationExportButtons" class="orb-table-export-buttons eo-toolbar-right">
        <x-ui.export-buttons table="violationsDataTable" />
    </div>
</div>

<!-- Table Section -->
<div class="att-table-wrap">
    <table class="att-table table table-hover" id="violationsDataTable">
        <thead>
            <tr>
                <th style="width: 65px; min-width: 65px;">S.No.</th>
                <th style="min-width: 220px;">Employee</th>
                <th style="min-width: 110px;">Date</th>
                <th style="min-width: 150px;">Violation Type</th>
                <th style="min-width: 90px;">Minutes</th>
                <th style="min-width: 110px;">Active Counter</th>
                <th style="min-width: 140px;">Penalty Status</th>
                <th style="min-width: 140px;">Attendance Status</th>
                <th style="min-width: 150px;">Created At</th>
                <th class="text-right no-export" style="width: 70px; min-width: 70px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @php
                $startIdx = method_exists($rows, 'currentPage') ? (($rows->currentPage() - 1) * $rows->perPage()) : 0;
            @endphp
            @forelse($rows as $row)
            <tr>
                <td><strong>{{ $startIdx + $loop->iteration }}</strong></td>
                <td>
                    <div>
                        <button type="button" class="emp-name-btn js-open-emp-drawer font-weight-bold" data-emp-id="{{ $row->employee_id }}">
                            {{ $row->employee_display_name }}
                        </button>
                        <div class="text-muted small"><code>{{ $row->employee_code }}</code> &bull; {{ $row->designation_name ?? 'Employee' }}</div>
                    </div>
                </td>
                <td><span class="font-weight-bold text-dark">{{ $row->formatted_date }}</span></td>
                <td>
                    @php
                    $typeBadgeClass = match($row->type) {
                        'late_login', 'late_mark' => 'orb-badge-orange',
                        'early_logout' => 'orb-badge-blue',
                        'missed_punch' => 'orb-badge-red',
                        default => 'orb-badge-secondary'
                    };
                    @endphp
                    <span class="orb-badge {{ $typeBadgeClass }}">
                        {{ $row->human_type }}
                    </span>
                </td>
                <td><span class="font-weight-bold">{{ $row->minutes ? $row->minutes . ' mins' : '-' }}</span></td>
                <td>
                    <span class="counter-pill">
                        <i class="fas fa-sync-alt text-primary mr-1" style="font-size: 10px;"></i> {{ $row->active_counter }}
                    </span>
                </td>
                <td>
                    <span class="orb-badge {{ $row->penalty_badge_class }}">
                        {{ $row->penalty_status_label }}
                    </span>
                </td>
                <td>
                    <span class="font-weight-bold text-dark">{{ $row->attendance_status_label }}</span>
                </td>
                <td class="small text-muted">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') }}</td>
                <td class="text-right no-export">
                    <div class="dropdown">
                        <button class="orb-action-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm" style="border-radius: 12px; border: 1px solid var(--orb-border); padding: 6px;">
                            @if($row->attendance_id)
                            <button type="button" class="dropdown-item js-open-att-modal font-weight-bold" data-att-id="{{ $row->attendance_id }}" style="border-radius: 8px; padding: 8px 12px; font-size: 12.5px;">
                                <i class="fas fa-calendar-check mr-2 text-info"></i> View Attendance Audit
                            </button>
                            @endif
                            <button type="button" class="dropdown-item js-open-emp-drawer font-weight-bold" data-emp-id="{{ $row->employee_id }}" style="border-radius: 8px; padding: 8px 12px; font-size: 12.5px;">
                                <i class="fas fa-user-shield mr-2 text-primary"></i> View Employee Audit Drawer
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x d-block mb-3 opacity-50"></i>
                    <h6 class="font-weight-bold">No attendance violation logs found matching the criteria.</h6>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Server-Side Pagination using Orbo Theme Template -->
@if(method_exists($rows, 'links'))
    {{ $rows->appends(request()->query())->links('vendor.pagination.orbo') }}
@endif
