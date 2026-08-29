@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa fa-history text-primary"></i> User Activity Logs</h2>
        <form action="{{ route('admin.activity_logs.index') }}" method="GET" class="d-flex w-25">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search logs..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary ms-2"><i class="fa fa-search"></i></button>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Subject Type</th>
                        <th>Subject ID</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                            <td>
                                @if($log->causer)
                                    <span class="badge bg-secondary">{{ $log->causer->name }}</span>
                                @else
                                    <span class="text-muted">System/Guest</span>
                                @endif
                            </td>
                            <td>
                                @if($log->event === 'created')
                                    <span class="badge bg-success">Created</span>
                                @elseif($log->event === 'updated')
                                    <span class="badge bg-primary">Updated</span>
                                @elseif($log->event === 'deleted')
                                    <span class="badge bg-danger">Deleted</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($log->event) }}</span>
                                @endif
                            </td>
                            <td><code>{{ class_basename($log->subject_type) }}</code></td>
                            <td>{{ $log->subject_id ?? '-' }}</td>
                            <td>
                                @if($log->properties && count($log->properties) > 0)
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                        View Changes
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Activity Log Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        @if(isset($log->properties['old']))
                                                        <div class="col-md-6">
                                                            <h6>Old Values</h6>
                                                            <pre class="bg-light p-2 border rounded" style="white-space: pre-wrap; font-size: 12px;">@json($log->properties['old'], JSON_PRETTY_PRINT)</pre>
                                                        </div>
                                                        @endif
                                                        @if(isset($log->properties['attributes']))
                                                        <div class="col-md-6">
                                                            <h6>New Values</h6>
                                                            <pre class="bg-light p-2 border rounded" style="white-space: pre-wrap; font-size: 12px;">@json($log->properties['attributes'], JSON_PRETTY_PRINT)</pre>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">No details</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No activity logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
