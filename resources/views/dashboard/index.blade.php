@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('content')
@php
    $inr = fn ($v) => '₹ ' . number_format((float) $v, 0);
    $fyStart = now()->month >= 4 ? now()->startOfYear()->addMonths(3) : now()->subYear()->startOfYear()->addMonths(3);
    $ranges = [
        'All time' => [null, null],
        'Today' => [now()->toDateString(), now()->toDateString()],
        '7 days' => [now()->subDays(6)->toDateString(), now()->toDateString()],
        '30 days' => [now()->subDays(29)->toDateString(), now()->toDateString()],
        'This year' => [now()->startOfYear()->toDateString(), now()->toDateString()],
        'Financial year' => [$fyStart->toDateString(), now()->toDateString()],
    ];
    $statusTotal = array_sum($statusCounts);
    $statusRows = [
        ['Unpaid', $statusCounts['unpaid'] ?? 0],
        ['Information approved', $statusCounts['information_approved'] ?? 0],
        ['Sangh Registered', $statusCounts['sangh_registered'] ?? 0],
    ];
    $pct = fn ($n, $total) => $total > 0 ? round($n * 100 / $total) : 0;
    $catTotal = collect($byCategory)->sum('c');
    $typeTotal = collect($byType)->sum('c');
    $genderTotal = $kpi['male'] + $kpi['female'];
    $statusBadge = fn ($s) => match ($s->registrationReceipt?->status) {
        'sangh_registered' => ['bg-primary text-white', 'Registered'],
        'information_approved' => ['bg-success text-white', 'Approved'],
        default => ['bg-warning text-dark', 'Unpaid'],
    };
@endphp

<div class="container-fluid mt-4 mb-5 dash">

    {{-- ===== Header + period ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h2 class="dash-title mb-1">Dashboard</h2>
            <p class="dash-sub mb-0">
                {{ $isSuperadmin ? 'Whole organisation' : 'Your Sanghs, folders, groups and meetings' }}
                · Period
                @if($allTime)
                    <strong>All time</strong>
                @else
                    <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> – <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                @endif
                <span class="d-block small">Sanghs counted by संघ नोंदणी दिनांक · receipts by पावती दिनांक</span>
            </p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="dash-filter d-flex flex-wrap align-items-end gap-2">
            <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Quick ranges">
                @foreach($ranges as $label => [$s, $e])
                    <a href="{{ $s === null ? route('dashboard') : route('dashboard', ['start_date' => $s, 'end_date' => $e]) }}"
                       class="btn {{ ($s === null ? $allTime : (! $allTime && $startDate === $s && $endDate === $e)) ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
                @endforeach
            </div>
            <input type="date" name="start_date" value="{{ $allTime ? '' : $startDate }}" class="form-control form-control-sm" aria-label="From">
            <input type="date" name="end_date" value="{{ $allTime ? '' : $endDate }}" class="form-control form-control-sm" aria-label="To">
            <button class="btn btn-sm btn-primary"><i class="ti-filter"></i> Apply</button>
        </form>
    </div>

    {{-- ===== Headline figures ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl">
            <a href="{{ route('sanghs.index') }}" class="dash-kpi" data-perm="sanghs.view">
                <div class="dash-kpi-label"><i class="ti-home"></i> Sanghs</div>
                <div class="dash-kpi-value">{{ number_format($kpi['sanghs']) }}</div>
                <div class="dash-kpi-sub">{{ $allTime ? 'All Sanghs' : 'Registered in period · of ' . number_format($kpi['sanghs_total']) . ' total' }}</div>
            </a>
        </div>
        <div class="col-6 col-xl">
            <div class="dash-kpi">
                <div class="dash-kpi-label"><i class="ti-user"></i> Total members</div>
                <div class="dash-kpi-value">{{ number_format($kpi['members']) }}</div>
                <div class="dash-kpi-sub">पुरुष {{ number_format($kpi['male']) }} · महिला {{ number_format($kpi['female']) }}@if($kpi['members'] > $genderTotal) · {{ number_format($kpi['members'] - $genderTotal) }} not split @endif</div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="dash-kpi">
                <div class="dash-kpi-label"><i class="ti-wallet"></i> Collections in period</div>
                <div class="dash-kpi-value">{{ $inr($kpi['collections']) }}</div>
                <div class="dash-kpi-sub">{{ number_format($kpi['receipts_in_period']) }} Sangh receipt(s) in period</div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="dash-kpi">
                <div class="dash-kpi-label"><i class="ti-alert"></i> Balance due</div>
                <div class="dash-kpi-value {{ $kpi['balance_due'] > 0 ? 'text-danger' : '' }}">{{ $inr($kpi['balance_due']) }}</div>
                <div class="dash-kpi-sub">{{ $allTime ? 'Outstanding on all receipts' : 'Outstanding on receipts in period' }}</div>
            </div>
        </div>
        <div class="col-12 col-xl">
            <div class="dash-kpi">
                <div class="dash-kpi-label"><i class="ti-medall"></i> Registered Sanghs</div>
                <div class="dash-kpi-value">{{ number_format($kpi['registered']) }}</div>
                <div class="dash-kpi-sub">{{ $allTime ? $pct($kpi['registered'], max($kpi['sanghs_total'], 1)) . '% of Sanghs' : 'by संघ नोंदणी दिनांक in period' }} @if($isSuperadmin && $kpi['blank_rows'])· {{ number_format($kpi['blank_rows']) }} blank rows @endif</div>
            </div>
        </div>
    </div>

    {{-- ===== Collections by month + receipt status ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="dash-card h-100">
                <div class="dash-card-head">
                    <h5>Collections by month</h5>
                    <span class="dash-muted">₹, by receipt date</span>
                </div>
                <div class="dash-card-body">
                    <div class="dash-chart" style="height: 290px;"><canvas id="chartMonthly" aria-label="Collections by month" role="img"></canvas></div>
                    <details class="dash-table-toggle mt-2">
                        <summary>View as table</summary>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Month</th><th class="text-end">Registration</th><th class="text-end">Renewal</th><th class="text-end">Other receipts</th><th class="text-end">Total</th></tr></thead>
                                <tbody>
                                    @foreach($monthly as $m)
                                        <tr>
                                            <td>{{ $m['label'] }}</td>
                                            <td class="text-end">{{ $inr($m['registration']) }}</td>
                                            <td class="text-end">{{ $inr($m['renewal']) }}</td>
                                            <td class="text-end">{{ $inr($m['other']) }}</td>
                                            <td class="text-end fw-semibold">{{ $inr($m['registration'] + $m['renewal'] + $m['other']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card h-100">
                <div class="dash-card-head">
                    <h5>Receipt status</h5>
                    <span class="dash-muted">{{ number_format($statusTotal) }} receipts</span>
                </div>
                <div class="dash-card-body">
                    @if($statusTotal === 0)
                        <p class="dash-empty">No receipts yet.</p>
                    @else
                        <div class="dash-donut-wrap">
                            <canvas id="chartStatus" aria-label="Receipt status" role="img"></canvas>
                            <div class="dash-donut-center"><strong>{{ number_format($statusTotal) }}</strong><span>receipts</span></div>
                        </div>
                        <ul class="dash-legend mt-3">
                            @foreach($statusRows as $i => [$label, $count])
                                <li>
                                    <span class="dash-swatch" style="background: var(--series-{{ $i + 1 }})"></span>
                                    <span class="flex-grow-1">{{ $label }}</span>
                                    <strong>{{ number_format($count) }}</strong>
                                    <span class="dash-muted ms-2" style="width:3rem;text-align:right">{{ $pct($count, $statusTotal) }}%</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Where the Sanghs are ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-6">
            <div class="dash-card h-100">
                <div class="dash-card-head"><h5>Sanghs by प्रादेशिक विभाग</h5><span class="dash-muted">count</span></div>
                <div class="dash-card-body">
                    @if(count($byVibhag))
                        <div class="dash-chart" style="height: {{ max(160, count($byVibhag) * 34 + 30) }}px;"><canvas id="chartVibhag" role="img" aria-label="Sanghs by vibhag"></canvas></div>
                    @else
                        <p class="dash-empty">No Sanghs with a प्रादेशिक विभाग yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="dash-card h-100">
                <div class="dash-card-head"><h5>Sanghs by जिल्हा</h5><span class="dash-muted">top 10</span></div>
                <div class="dash-card-body">
                    @if(count($byDistrict))
                        <div class="dash-chart" style="height: {{ max(160, count($byDistrict) * 34 + 30) }}px;"><canvas id="chartDistrict" role="img" aria-label="Sanghs by district"></canvas></div>
                    @else
                        <p class="dash-empty">No Sanghs with a जिल्हा yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Breakdowns ===== --}}
    <div class="row g-3 mb-3">
        @foreach([
            ['श्रेणी (Category)', $byCategory, $catTotal],
            ['संघ प्रकार (Type)', $byType, $typeTotal],
            ['सभासद (Members)', array_values(array_filter([
                ['label' => 'पुरुष (Male)', 'c' => $kpi['male']],
                ['label' => 'महिला (Female)', 'c' => $kpi['female']],
                $kpi['members'] > $genderTotal ? ['label' => 'Not split (total only)', 'c' => $kpi['members'] - $genderTotal] : null,
            ])), max($kpi['members'], $genderTotal)],
        ] as [$title, $rows, $total])
            <div class="col-md-4">
                <div class="dash-card h-100">
                    <div class="dash-card-head"><h5>{{ $title }}</h5><span class="dash-muted">{{ number_format($total) }}</span></div>
                    <div class="dash-card-body">
                        @foreach($rows as $row)
                            <div class="dash-bar-row">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ $row['label'] }}</span>
                                    <span><strong>{{ number_format($row['c']) }}</strong> <span class="dash-muted">· {{ $pct($row['c'], $total) }}%</span></span>
                                </div>
                                <div class="dash-bar"><span style="width: {{ $pct($row['c'], $total) }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Workspace ===== --}}
    <div class="row g-3 mb-3">
        @foreach(array_filter([
            ['Folders', $workspace['folders'], 'ti-folder', route('folders.index'), 'folders.view'],
            ['Files', $workspace['files'], 'ti-file', route('files.index'), 'files.view'],
            ['Groups', $workspace['groups'], 'ti-comments', route('groups.index'), 'groups.view'],
            ['Upcoming meetings', $workspace['meetings_upcoming'], 'ti-calendar', route('meetings.index'), 'meetings.view'],
            ['Links', $workspace['links'], 'ti-link', route('links.index'), 'links.view'],
            $workspace['users'] !== null ? ['Users', $workspace['users'], 'ti-id-badge', route('admin.user_roles.index'), ''] : null,
        ]) as [$label, $count, $icon, $url, $perm])
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ $url }}" class="dash-mini" @if($perm) data-perm="{{ $perm }}" @endif>
                    <i class="{{ $icon }}"></i>
                    <div><div class="dash-mini-value">{{ number_format($count) }}</div><div class="dash-mini-label">{{ $label }}</div></div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- ===== Lists ===== --}}
    <div class="row g-3">
        <div class="col-xl-4">
            <div class="dash-card h-100">
                <div class="dash-card-head"><h5>Upcoming meetings</h5><a href="{{ route('meetings.index') }}" class="dash-link" data-perm="meetings.view">Calendar →</a></div>
                <ul class="dash-list">
                    @forelse($upcomingMeetings as $mt)
                        <li>
                            <div class="dash-date"><strong>{{ $mt->start_at->format('d') }}</strong><span>{{ $mt->start_at->format('M') }}</span></div>
                            <div class="min-w-0">
                                <div class="text-truncate fw-semibold">{{ $mt->title }}</div>
                                <div class="dash-muted small">{{ $mt->start_at->format('D, h:i A') }}@if($mt->group) · {{ $mt->group->name }}@endif</div>
                            </div>
                        </li>
                    @empty
                        <li class="dash-empty">No upcoming meetings.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card h-100">
                <div class="dash-card-head"><h5>Recently registered Sanghs</h5><a href="{{ route('sanghs.index') }}" class="dash-link" data-perm="sanghs.view">All →</a></div>
                <ul class="dash-list">
                    @forelse($recentSanghs as $s)
                        <li>
                            <div class="min-w-0 flex-grow-1">
                                <a href="{{ route('sanghs.show', $s) }}" class="text-truncate d-block fw-semibold dash-plain">{{ $s->name_of_sangh }}</a>
                                <div class="dash-muted small">{{ $s->district ?: '-' }} · {{ optional($s->registered_at)->format('d M Y') }}</div>
                            </div>
                            <span class="badge {{ $statusBadge($s)[0] }}">{{ $statusBadge($s)[1] }}</span>
                        </li>
                    @empty
                        <li class="dash-empty">No Sanghs yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-card h-100">
                <div class="dash-card-head"><h5>{{ $isSuperadmin ? 'Recent activity' : 'Your recent activity' }}</h5><a href="{{ route('admin.activity_logs.index') }}" class="dash-link">All →</a></div>
                <ul class="dash-list">
                    @forelse($recentActivity as $a)
                        <li>
                            <span class="badge bg-{{ $a['badge'][1] }} text-white dash-act-badge"><i class="fa {{ $a['badge'][2] }}"></i></span>
                            <div class="min-w-0">
                                <div class="small dash-two-lines">{{ $a['summary'] }}</div>
                                <div class="dash-muted small">{{ $a['when']->diffForHumans() }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="dash-empty">No activity yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    /* Chart roles (reference palette, light): categorical slots in fixed order. */
    .dash {
        --series-1: #2a78d6;
        --series-2: #eb6834;
        --series-3: #1baf7a;
        --ink: #1f2937;
        --ink-2: #52514e;
        --muted: #6b7280;
        --line: #e7eaf0;
        --soft: #f7f9fc;
        color: var(--ink);
    }
    .dash .min-w-0 { min-width: 0; }
    .dash-title { font-size: 1.9rem; font-weight: 700; letter-spacing: -.01em; }
    .dash-sub, .dash-muted { color: var(--muted); }
    .dash-filter .form-control { width: 150px; }
    .dash-filter .btn { border-radius: 8px; }

    .dash-kpi {
        display: block; height: 100%; padding: 1rem 1.15rem; background: #fff; color: var(--ink);
        border: 1px solid var(--line); border-radius: 12px; text-decoration: none;
        box-shadow: 0 1px 2px rgba(16,24,40,.04), 0 2px 10px rgba(16,24,40,.05);
    }
    a.dash-kpi:hover { border-color: #c9d6ea; color: var(--ink); text-decoration: none; }
    .dash-kpi-label { font-size: .78rem; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
    .dash-kpi-label i { margin-right: .25rem; }
    .dash-kpi-value { font-size: 1.75rem; font-weight: 700; line-height: 1.25; margin: .2rem 0; white-space: nowrap; }
    .dash-kpi-sub { font-size: .8rem; color: var(--muted); }

    .dash-card { background: #fff; border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 1px 2px rgba(16,24,40,.04), 0 2px 10px rgba(16,24,40,.05); overflow: hidden; }
    .dash-card-head { display: flex; justify-content: space-between; align-items: center; gap: .5rem; padding: .85rem 1.15rem; border-bottom: 1px solid var(--line); }
    .dash-card-head h5 { font-size: 1rem; font-weight: 600; margin: 0; }
    .dash-card-body { padding: 1rem 1.15rem; }
    .dash-chart { position: relative; }
    .dash-empty { color: var(--muted); margin: 0; padding: .5rem 0; }
    .dash-link { font-size: .85rem; text-decoration: none; }

    .dash-donut-wrap { position: relative; height: 190px; }
    .dash-donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; }
    .dash-donut-center strong { font-size: 1.5rem; line-height: 1; }
    .dash-donut-center span { font-size: .75rem; color: var(--muted); }
    .dash-legend { list-style: none; padding: 0; margin: 0; }
    .dash-legend li { display: flex; align-items: center; gap: .5rem; padding: .35rem 0; border-top: 1px solid var(--line); font-size: .9rem; }
    .dash-legend li:first-child { border-top: 0; }
    .dash-swatch { width: 10px; height: 10px; border-radius: 3px; flex: 0 0 10px; }

    .dash-bar-row + .dash-bar-row { margin-top: .85rem; }
    .dash-bar { height: 8px; background: var(--soft); border-radius: 4px; overflow: hidden; }
    .dash-bar span { display: block; height: 100%; background: var(--series-1); border-radius: 4px; }

    .dash-mini { display: flex; align-items: center; gap: .8rem; height: 100%; padding: .85rem 1rem; background: #fff; border: 1px solid var(--line); border-radius: 12px; color: var(--ink); text-decoration: none; }
    .dash-mini:hover { border-color: #c9d6ea; color: var(--ink); text-decoration: none; }
    .dash-mini i { font-size: 1.15rem; color: var(--series-1); width: 36px; height: 36px; border-radius: 10px; background: #e8f0fb; display: flex; align-items: center; justify-content: center; flex: 0 0 36px; }
    .dash-mini-value { font-size: 1.25rem; font-weight: 700; line-height: 1.1; }
    .dash-mini-label { font-size: .78rem; color: var(--muted); }

    .dash-list { list-style: none; margin: 0; padding: .25rem 1.15rem .75rem; }
    .dash-list li { display: flex; align-items: center; gap: .75rem; padding: .6rem 0; border-top: 1px solid var(--line); }
    .dash-list li:first-child { border-top: 0; }
    .dash-date { flex: 0 0 44px; text-align: center; border: 1px solid var(--line); border-radius: 8px; padding: .2rem 0; line-height: 1.1; }
    .dash-date strong { display: block; font-size: 1.05rem; }
    .dash-date span { font-size: .7rem; color: var(--muted); text-transform: uppercase; }
    .dash-act-badge { flex: 0 0 auto; width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; }
    .dash-two-lines { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .dash-plain { color: var(--ink); text-decoration: none; }
    .dash-plain:hover { color: var(--series-1); }
    .dash-table-toggle summary { cursor: pointer; font-size: .85rem; color: var(--muted); }

    @media (max-width: 767px) {
        .dash-title { font-size: 1.5rem; }
        .dash-kpi-value { font-size: 1.35rem; }
        .dash-filter .form-control { width: 130px; }
    }
</style>

@push('scripts')
<script>
    // Chart.js loads after this script, so draw once the page is ready.
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        const css = getComputedStyle(document.querySelector('.dash'));
        const C = (name) => css.getPropertyValue(name).trim();
        const S1 = C('--series-1'), S2 = C('--series-2'), S3 = C('--series-3'), INK2 = C('--ink-2'), LINE = C('--line');
        const inr = (v) => '₹ ' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
        const num = (v) => Number(v || 0).toLocaleString('en-IN');

        Chart.defaults.global.defaultFontFamily = getComputedStyle(document.body).fontFamily;
        Chart.defaults.global.defaultFontColor = INK2;
        Chart.defaults.global.legend.labels.boxWidth = 10;

        const tooltip = {
            backgroundColor: '#1f2937', titleFontStyle: '600', bodySpacing: 4, xPadding: 10, yPadding: 8,
            cornerRadius: 8, displayColors: true,
        };
        const grid = { color: LINE, drawBorder: false, zeroLineColor: LINE };

        // Value labels at the end of horizontal bars (selective direct labels, text ink).
        const endLabels = {
            afterDatasetsDraw(chart) {
                const ctx = chart.ctx;
                ctx.save();
                ctx.fillStyle = INK2;
                ctx.font = '12px ' + Chart.defaults.global.defaultFontFamily;
                ctx.textBaseline = 'middle';
                chart.getDatasetMeta(0).data.forEach((bar, i) => {
                    ctx.fillText(num(chart.data.datasets[0].data[i]), bar._model.x + 6, bar._model.y);
                });
                ctx.restore();
            },
        };

        // 1) Collections by month — stacked bars, 3 series in fixed order, 2px surface gap.
        const monthly = @json($monthly);
        const mEl = document.getElementById('chartMonthly');
        if (mEl) {
            const series = [['Registration', 'registration', S1], ['Renewal', 'renewal', S2], ['Other receipts', 'other', S3]];
            new Chart(mEl, {
                type: 'bar',
                data: {
                    labels: monthly.map((m) => m.label),
                    datasets: series.map(([label, key, color]) => ({
                        label, data: monthly.map((m) => m[key]), backgroundColor: color,
                        hoverBackgroundColor: color, borderWidth: 0,
                    })),
                },
                options: {
                    maintainAspectRatio: false,
                    legend: { position: 'top', align: 'end' },
                    tooltips: Object.assign({}, tooltip, {
                        mode: 'index', intersect: false,
                        callbacks: {
                            label: (t, d) => d.datasets[t.datasetIndex].label + ': ' + inr(t.yLabel),
                            footer: (items) => 'Total: ' + inr(items.reduce((s, t) => s + t.yLabel, 0)),
                        },
                    }),
                    scales: {
                        xAxes: [{ stacked: true, gridLines: { display: false }, barPercentage: 0.6, categoryPercentage: 0.8 }],
                        yAxes: [{ stacked: true, gridLines: grid, ticks: { beginAtZero: true, maxTicksLimit: 5, callback: inr } }],
                    },
                },
            });
        }

        // 2) Receipt status — doughnut, 3 slots in fixed order; legend + counts are in HTML beside it.
        const sEl = document.getElementById('chartStatus');
        if (sEl) {
            const rows = @json($statusRows);
            new Chart(sEl, {
                type: 'doughnut',
                data: { labels: rows.map((r) => r[0]), datasets: [{ data: rows.map((r) => r[1]), backgroundColor: [S1, S2, S3], borderColor: '#fff', borderWidth: 2 }] },
                options: {
                    maintainAspectRatio: false, cutoutPercentage: 72, legend: { display: false },
                    tooltips: Object.assign({}, tooltip, { callbacks: { label: (t, d) => d.labels[t.index] + ': ' + num(d.datasets[0].data[t.index]) } }),
                },
            });
        }

        // 3) + 4) Sanghs by vibhag / district — single series horizontal bars (no legend; the title names it).
        const hbar = (id, rows, label) => {
            const el = document.getElementById(id);
            if (!el) return;
            const max = Math.max(...rows.map((r) => r.c), 1);
            new Chart(el, {
                type: 'horizontalBar',
                data: { labels: rows.map((r) => r.label), datasets: [{ label, data: rows.map((r) => r.c), backgroundColor: S1, borderSkipped: 'left' }] },
                options: {
                    maintainAspectRatio: false, legend: { display: false },
                    layout: { padding: { right: 36 } },
                    tooltips: Object.assign({}, tooltip, { displayColors: false, callbacks: { label: (t) => label + ': ' + num(t.xLabel) } }),
                    scales: {
                        xAxes: [{ gridLines: grid, ticks: { beginAtZero: true, maxTicksLimit: 5, suggestedMax: max * 1.1, callback: num } }],
                        yAxes: [{ gridLines: { display: false }, barPercentage: 0.7, categoryPercentage: 0.85 }],
                    },
                },
                plugins: [endLabels],
            });
        };
        hbar('chartVibhag', @json($byVibhag), 'Sanghs');
        hbar('chartDistrict', @json($byDistrict), 'Sanghs');
    });
</script>
@endpush
@endsection
