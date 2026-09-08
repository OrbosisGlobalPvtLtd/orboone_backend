                <div class="em-card em-card-full animate__animated animate__fadeInUp" style="margin-bottom: 25px;">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-clock mr-2"></i>Shift Assignment History</h5>
                            <div class="em-card-sub">Complete record of shift schedules assigned to this employee.</div>
                        </div>
                    </div>

                    <div class="em-card-body" style="padding: 0;">
                        <div class="salary-table-wrap">
                            @if (isset($shiftHistory) && $shiftHistory->count())
                            <table class="salary-table">
                                <thead>
                                    <tr>
                                        <th>Effective Period</th>
                                        <th>Shift Name</th>
                                        <th>Shift Type</th>
                                        <th>Timing Details</th>
                                        <th style="text-align: right;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shiftHistory as $history)
                                    @php
                                        $typeLabel = (empty($history->punch_allowed_from) && empty($history->shift_start_time)) || stripos($history->shift_name ?? '', 'flexible') !== false ? 'Flexible' : 'Fixed';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold" style="color: var(--orb-text);">{{ !empty($history->effective_from) ? \Carbon\Carbon::parse($history->effective_from)->format('d M Y') : '-' }}</span>
                                                @if(!empty($history->effective_to))
                                                <span class="text-muted" style="font-size: 11px;">to {{ \Carbon\Carbon::parse($history->effective_to)->format('d M Y') }}</span>
                                                @else
                                                <span class="text-success" style="font-size: 11px; font-weight: 750;"><i class="fas fa-dot-circle" style="font-size: 8px;"></i> Active / Present</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ $history->shift_name ?: 'Custom/Flexible Shift' }}</span>
                                        </td>
                                        <td>
                                            <span class="ev-pill {{ $typeLabel === 'Flexible' ? 'ev-pill-warning' : 'ev-pill-default' }}">
                                                {{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($typeLabel === 'Flexible')
                                                <span class="text-muted" style="font-size: 12px;">
                                                    Punch From: Flexible | Shift End: Dynamic
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size: 12px;">
                                                    {{ $history->shift_start_time ? \Carbon\Carbon::parse($history->shift_start_time)->format('h:i A') : '--:--' }} - {{ $history->shift_end_time ? \Carbon\Carbon::parse($history->shift_end_time)->format('h:i A') : '--:--' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            @if($history->is_active)
                                                <span class="att-badge badge-active">Active</span>
                                            @else
                                                <span class="att-badge badge-muted">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="text-center text-muted py-4">No shift history found.</div>
                            @endif
                        </div>
                    </div>
                </div>
