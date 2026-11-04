@extends('layouts.app')

@use('App\Support\ActivityLogPresenter', 'P')

@section('content')
<div class="container-fluid mt-4 mb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
        <h2 class="mb-0"><i class="fa fa-history text-primary"></i> Activity History</h2>
        <form action="{{ route('admin.activity_logs.index') }}" method="GET" class="d-flex gap-2">
            <select name="action" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="">All actions</option>
                <option value="created" @selected(request('action') === 'created')>Added</option>
                <option value="updated" @selected(request('action') === 'updated')>Edited</option>
                <option value="deleted" @selected(request('action') === 'deleted')>Deleted</option>
            </select>
            <input type="text" name="search" class="form-control form-control-sm" style="min-width:220px"
                   placeholder="Search by person, Sangh, receipt…" value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fa fa-search"></i></button>
            @if(request('search') || request('action'))
                <a href="{{ route('admin.activity_logs.index') }}" class="btn btn-sm btn-link">Clear</a>
            @endif
        </form>
    </div>
    <p class="text-muted mb-4">Who added, edited or deleted what, and when. Click <strong>See details</strong> to view exactly what changed.</p>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:170px">When</th>
                        <th style="width:110px">Action</th>
                        <th>What happened</th>
                        <th style="width:130px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php([$badgeText, $badgeColor, $badgeIcon] = P::badge($log))
                        @php($changes = P::changes($log))
                        <tr>
                            <td>
                                <div>{{ $log->created_at->format('d M Y, h:i A') }}</div>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }}"><i class="fa {{ $badgeIcon }}"></i> {{ $badgeText }}</span>
                            </td>
                            <td>
                                <div>{{ P::summary($log) }}</div>
                                <small class="text-muted">{{ P::modelLabel($log) }}</small>
                            </td>
                            <td class="text-end">
                                @if(count($changes) > 0)
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                        See details
                                    </button>
                                @else
                                    <span class="text-muted small">No details</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nothing has been recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- Modals live outside the table so they render correctly. --}}
    @foreach($logs as $log)
        @php($changes = P::changes($log))
        @continue(count($changes) === 0)
        <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">{{ P::summary($log) }}</h5>
                            <small class="text-muted">{{ $log->created_at->format('d M Y, h:i A') }} · {{ $log->created_at->diffForHumans() }}</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($log->event === 'updated')
                            <p class="text-muted small mb-2">These fields were changed:</p>
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th style="width:30%">Field</th><th>Before</th><th>After</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($changes as [$label, $before, $after])
                                        <tr>
                                            <td class="fw-semibold">{{ $label }}</td>
                                            <td class="text-danger">
                                                @if(($before ?? '') === '') <em class="text-muted">(empty)</em> @else {{ $before }} @endif
                                            </td>
                                            <td class="text-success">
                                                @if(($after ?? '') === '') <em class="text-muted">(empty)</em> @else {{ $after }} @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted small mb-2">
                                {{ $log->event === 'deleted' ? 'Details of the record when it was deleted:' : 'Details entered:' }}
                            </p>
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <tbody>
                                    @foreach($changes as [$label, $before, $after])
                                        <tr>
                                            <td class="fw-semibold" style="width:35%">{{ $label }}</td>
                                            <td>{{ ($after ?? '') !== '' ? $after : $before }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
