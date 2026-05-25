@php
    use Illuminate\Support\Facades\Route;

    $meta = data_get($dashboard, 'meta', []);
    $cards = data_get($dashboard, 'cards', []);
    $actions = data_get($dashboard, 'quick_actions', []);
    $required = data_get($dashboard, 'action_required', []);
    $activities = data_get($dashboard, 'recent_activities', []);
    $tables = data_get($dashboard, 'tables', []);
@endphp

<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body { background: var(--orb-bg); overflow-x: hidden; }
    .orb-role-page { min-height: calc(100vh - 80px); background: var(--orb-bg); padding: 24px; overflow-x: hidden; }
    .orb-role-wrap { max-width: 1480px; margin: 0 auto; }
    .orb-role-hero {
        display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap;
        border-radius: 26px; padding: 24px; color: #fff;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        box-shadow: var(--orb-shadow); margin-bottom: 18px; overflow: hidden; position: relative;
    }
    .orb-role-hero:before { content: ""; position: absolute; width: 240px; height: 240px; right: -80px; top: -100px; border-radius: 50%; background: rgba(255,255,255,.11); }
    .orb-role-hero:after { content: ""; position: absolute; width: 170px; height: 170px; right: 140px; bottom: -105px; border-radius: 50%; background: rgba(255,255,255,.08); }
    .orb-hero-main, .orb-hero-actions { position: relative; z-index: 1; }
    .orb-eyebrow { font-size: 11px; font-weight: 900; letter-spacing: .8px; text-transform: uppercase; color: rgba(255,255,255,.78); margin-bottom: 7px; }
    .orb-title { margin: 0; color: #fff; font-size: 28px; line-height: 1.18; font-weight: 900; letter-spacing: 0; }
    .orb-subtitle { margin: 8px 0 0; color: rgba(255,255,255,.84); font-weight: 650; max-width: 720px; }
    .orb-date { display: inline-flex; margin-top: 14px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.2); font-size: 12px; font-weight: 800; }
    .orb-hero-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; max-width: 520px; }
    .orb-hero-action {
        display: inline-flex; align-items: center; gap: 7px; min-height: 36px; padding: 0 13px;
        border-radius: 999px; background: #fff; color: var(--orb-primary); font-size: 12px; font-weight: 900;
        text-decoration: none !important; box-shadow: 0 10px 22px rgba(16,24,40,.12);
    }
    .orb-hero-action:hover { color: var(--orb-secondary); transform: translateY(-1px); }

    .orb-card-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .orb-stat-card { position: relative; min-height: 132px; padding: 16px; border: 1px solid var(--orb-border); border-radius: 18px; background: var(--orb-card); box-shadow: var(--orb-shadow); overflow: hidden; }
    .orb-stat-card:after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 3px; background: linear-gradient(90deg, var(--orb-primary), var(--orb-secondary)); }
    .orb-stat-top { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
    .orb-stat-label { color: var(--orb-muted); font-size: 11px; line-height: 1.35; text-transform: uppercase; font-weight: 900; }
    .orb-stat-value { margin-top: 8px; color: var(--orb-text); font-size: 26px; line-height: 1.15; font-weight: 900; word-break: break-word; }
    .orb-stat-icon { width: 46px; height: 46px; border-radius: 15px; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; background: var(--orb-soft); color: var(--orb-primary); font-size: 18px; }
    .orb-stat-sub { margin-top: 10px; color: var(--orb-muted); font-size: 12px; font-weight: 650; line-height: 1.45; }

    .orb-panel { background: var(--orb-card); border: 1px solid var(--orb-border); border-radius: 22px; box-shadow: var(--orb-shadow); overflow: hidden; margin-bottom: 18px; }
    .orb-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 18px; border-bottom: 1px solid var(--orb-border); }
    .orb-panel-title { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .orb-panel-icon { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--orb-soft); color: var(--orb-primary); flex: 0 0 auto; }
    .orb-panel-title h3 { margin: 0; color: var(--orb-text); font-size: 16px; line-height: 1.25; font-weight: 900; }
    .orb-panel-title p { margin: 3px 0 0; color: var(--orb-muted); font-size: 12px; font-weight: 650; }
    .orb-panel-body { padding: 16px 18px; }
    .orb-two { display: grid; grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); gap: 18px; }
    .orb-list { display: grid; gap: 10px; }
    .orb-list-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px; border: 1px solid var(--orb-border); border-radius: 14px; background: #FCFCFD; }
    .orb-list-main { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .orb-list-main i { width: 34px; height: 34px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--orb-soft); color: var(--orb-primary); flex: 0 0 auto; }
    .orb-list-main strong { display: block; color: var(--orb-text); font-size: 13px; font-weight: 900; white-space: normal; }
    .orb-list-main span { display: block; color: var(--orb-muted); font-size: 12px; font-weight: 650; margin-top: 2px; }
    .orb-count { min-width: 34px; height: 30px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; padding: 0 10px; background: var(--orb-soft); color: var(--orb-primary); font-size: 13px; font-weight: 900; }
    .orb-empty { padding: 24px; text-align: center; color: var(--orb-muted); font-size: 13px; font-weight: 750; border: 1px dashed var(--orb-border); border-radius: 14px; background: #FCFCFD; }
    .orb-alert { margin-bottom: 18px; padding: 14px 16px; border: 1px solid rgba(75,0,232,.14); border-radius: 16px; background: var(--orb-soft); color: var(--orb-primary); font-weight: 800; }
    .orb-table-scroll { overflow-x: auto; width: 100%; }
    .orb-table { width: 100%; min-width: 640px; border-collapse: separate; border-spacing: 0; }
    .orb-table th { background: #F8FAFC; color: var(--orb-muted); font-size: 11px; font-weight: 900; text-transform: uppercase; padding: 11px 12px; border-bottom: 1px solid var(--orb-border); white-space: nowrap; }
    .orb-table td { color: var(--orb-text); font-size: 13px; font-weight: 650; padding: 12px; border-bottom: 1px solid #F1F3F8; vertical-align: top; }
    .orb-table tr:last-child td { border-bottom: 0; }

    @media (max-width: 1199px) { .orb-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 991px) { .orb-role-page { padding: 18px; } .orb-two { grid-template-columns: 1fr; } .orb-hero-actions { justify-content: flex-start; } }
    @media (max-width: 640px) { .orb-role-page { padding: 12px; } .orb-role-hero { border-radius: 22px; padding: 18px; } .orb-title { font-size: 23px; } .orb-card-grid { grid-template-columns: 1fr; } .orb-panel-head { align-items: flex-start; } }
</style>

<div class="orb-role-page">
    <div class="orb-role-wrap">
        <section class="orb-role-hero">
            <div class="orb-hero-main">
                <div class="orb-eyebrow">Orbosis HRMS</div>
                <h1 class="orb-title">{{ data_get($meta, 'title', data_get($dashboard, 'role_title', 'Dashboard')) }}</h1>
                <p class="orb-subtitle">{{ data_get($meta, 'subtitle', 'Your role dashboard summary is ready.') }}</p>
                <span class="orb-date"><i class="fas fa-calendar-day mr-2"></i>{{ data_get($meta, 'current_date', now()->format('d M Y')) }}</span>
            </div>
            <div class="orb-hero-actions">
                @foreach($actions as $action)
                    @php
                        $route = data_get($action, 'route');
                        $url = $route && Route::has($route) ? route($route) : data_get($action, 'url');
                    @endphp
                    @if($url)
                        <a class="orb-hero-action" href="{{ $url }}">
                            <i class="{{ data_get($action, 'icon', 'fas fa-arrow-right') }}"></i>
                            <span>{{ data_get($action, 'title', 'Action') }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        @if(data_get($dashboard, 'empty_message'))
            <div class="orb-alert">{{ data_get($dashboard, 'empty_message') }}</div>
        @endif

        <section class="orb-card-grid">
            @forelse($cards as $card)
                <article class="orb-stat-card">
                    <div class="orb-stat-top">
                        <div>
                            <div class="orb-stat-label">{{ data_get($card, 'label', '-') }}</div>
                            <div class="orb-stat-value">{{ data_get($card, 'value', 0) }}</div>
                        </div>
                        <div class="orb-stat-icon"><i class="{{ data_get($card, 'icon', 'fas fa-chart-simple') }}"></i></div>
                    </div>
                    <div class="orb-stat-sub">{{ data_get($card, 'subtitle', '') }}</div>
                </article>
            @empty
                <div class="orb-stat-card"><div class="orb-empty">No dashboard cards available.</div></div>
            @endforelse
        </section>

        <section class="orb-two">
            <div class="orb-panel">
                <div class="orb-panel-head">
                    <div class="orb-panel-title">
                        <div class="orb-panel-icon"><i class="fas fa-bell"></i></div>
                        <div>
                            <h3>Action Required</h3>
                            <p>Pending items relevant to this role</p>
                        </div>
                    </div>
                </div>
                <div class="orb-panel-body">
                    @if(count($required))
                        <div class="orb-list">
                            @foreach($required as $item)
                                <div class="orb-list-row">
                                    <div class="orb-list-main">
                                        <i class="{{ data_get($item, 'icon', 'fas fa-circle-exclamation') }}"></i>
                                        <div>
                                            @if(data_get($item, 'url'))
                                                <a href="{{ data_get($item, 'url') }}"><strong>{{ data_get($item, 'title', '-') }}</strong></a>
                                            @else
                                                <strong>{{ data_get($item, 'title', '-') }}</strong>
                                            @endif
                                            <span>{{ data_get($item, 'subtitle', '') }}</span>
                                        </div>
                                    </div>
                                    <span class="orb-count">{{ data_get($item, 'count', 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="orb-empty">No action required.</div>
                    @endif
                </div>
            </div>

            <div class="orb-panel">
                <div class="orb-panel-head">
                    <div class="orb-panel-title">
                        <div class="orb-panel-icon"><i class="fas fa-clock-rotate-left"></i></div>
                        <div>
                            <h3>Recent Activity</h3>
                            <p>Latest role-relevant records</p>
                        </div>
                    </div>
                </div>
                <div class="orb-panel-body">
                    @if(count($activities))
                        <div class="orb-list">
                            @foreach($activities as $activity)
                                <div class="orb-list-row">
                                    <div class="orb-list-main">
                                        <i class="{{ data_get($activity, 'icon', 'fas fa-circle') }}"></i>
                                        <div>
                                            <strong>{{ data_get($activity, 'title', '-') }}</strong>
                                            <span>{{ \Illuminate\Support\Str::limit(data_get($activity, 'description', '-'), 100) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="orb-empty">No recent activity.</div>
                    @endif
                </div>
            </div>
        </section>

        @foreach($tables as $table)
            <section class="orb-panel">
                <div class="orb-panel-head">
                    <div class="orb-panel-title">
                        <div class="orb-panel-icon"><i class="{{ data_get($table, 'icon', 'fas fa-table') }}"></i></div>
                        <div>
                            <h3>{{ data_get($table, 'title', 'Table') }}</h3>
                            <p>{{ data_get($table, 'subtitle', '') }}</p>
                        </div>
                    </div>
                </div>
                <div class="orb-panel-body">
                    @if(count(data_get($table, 'rows', [])))
                        <div class="orb-table-scroll">
                            <table class="orb-table">
                                <thead>
                                    <tr>
                                        @foreach(data_get($table, 'columns', []) as $column)
                                            <th>{{ data_get($column, 'label', '') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(data_get($table, 'rows', []) as $row)
                                        <tr>
                                            @foreach(data_get($table, 'columns', []) as $column)
                                                <td>{{ data_get($row, data_get($column, 'key'), '-') }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="orb-empty">{{ data_get($table, 'empty', 'No records found.') }}</div>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</div>
