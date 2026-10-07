                @if ($canSeeSalary)
                <div class="em-card em-card-full animate__animated animate__fadeInUp">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-history mr-2"></i>Salary History</h5>
                            <div class="em-card-sub">Date-wise salary/stipend records. Old records are preserved.</div>
                        </div>
                    </div>

                    <div class="em-card-body" style="padding: 0;">
                        <div class="salary-table-wrap">
                            @if (isset($salaryHistories) && $salaryHistories->count())
                            <table class="salary-table">
                                <thead>
                                    <tr>
                                        <th>Effective Date</th>
                                        <th>Lifecycle Stage</th>
                                        <th>Salary Type</th>
                                        <th>Authorized By</th>
                                        <th>Reason / Revision Type</th>
                                        <th style="text-align: right;">Gross Salary / CTC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salaryHistories as $history)
                                    @php
                                    $historyStage = $history->employment_stage ?? ($history->stage ?? '-');
                                    $historyType = $history->salary_type ?? ((float) ($history->salary_amount ?? 0) <= 0 ? 'unpaid' : ($historyStage==='internship' ? 'stipend' : 'salary' ));
                                        $active=isset($history->is_active) ? (int) $history->is_active === 1 : empty($history->effective_to);
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
                                                <span class="ev-pill ev-pill-default">
                                                    {{ ucfirst(str_replace('_', ' ', $historyStage)) }}
                                                </span>
                                            </td>
                                            <td><span class="salary-pill salary-type">{{ ucfirst($historyType) }}</span></td>
                                            <td>
                                                <span class="text-muted" style="font-size: 12px; font-weight: 700;">
                                                    <i class="far fa-user-circle"></i> {{ $history->creator_name ?? 'System' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-medium" style="color: var(--orb-text); font-size: 13px;">
                                                    {{ $history->reason ?: 'Regular revision' }}
                                                </span>
                                            </td>
                                            <td style="text-align: right; font-size: 14px; font-weight: 900; color: var(--orb-primary);">
                                                ₹{{ number_format((float) ($history->salary_amount ?? 0), 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="empty-history">
                                <i class="fas fa-info-circle mr-1"></i> No salary history found.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
