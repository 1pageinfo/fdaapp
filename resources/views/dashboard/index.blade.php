@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))
@section('page_title', 'Dashboard')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item active" aria-current="page">Overview</li>
@endsection

@section('page_actions')
  <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-primary"><i class="ti-calendar"></i> Open Calendar</a>
@endsection

@section('content')
  @includeWhen(View::exists('partials.flash'), 'partials.flash')

  <div class="container-fluid px-0">
    {{-- Filter Card --}}
    <div class="card shadow-sm mb-4 border-0">
      <div class="card-body">
        <form method="GET" action="{{ route('dashboard') }}" class="row align-items-end" aria-labelledby="filterHeading">
          <div class="col-12 col-md-3">
            <label for="start_date" class="mb-1 small text-muted">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}" aria-describedby="startHelp">
            <small id="startHelp" class="form-text text-muted">From</small>
          </div>
          <div class="col-12 col-md-3 mt-3 mt-md-0">
            <label for="end_date" class="mb-1 small text-muted">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}" aria-describedby="endHelp">
            <small id="endHelp" class="form-text text-muted">To</small>
          </div>
          <div class="col-12 col-md-3 mt-3 mt-md-0">
            <label class="mb-1 small text-muted" aria-hidden="true">Quick Ranges</label>
            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('dashboard', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-light">Today</a>
              <a href="{{ route('dashboard', ['start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-light">7d</a>
              <a href="{{ route('dashboard', ['start_date' => now()->subDays(29)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-light">30d</a>
              <a href="{{ route('dashboard', ['start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-light">YTD</a>
            </div>
          </div>
          <div class="col-12 col-md-3 mt-3 mt-md-0">
            <button class="btn btn-primary w-100"><i class="ti-filter"></i> Apply Filter</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row">
      <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small mb-1">Total Receipts</div>
              <div class="h3 mb-0">{{ $data['receipts'] }}</div>
            </div>
            <div class="icon icon-box-success">
              <span class="icon-paper"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small mb-1">Sanghs Registered</div>
              <div class="h3 mb-0">{{ $data['sanghs'] }}</div>
            </div>
            <div class="icon icon-box-info">
              <span class="icon-contract"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small mb-1">Meetings / Events</div>
              <div class="h3 mb-0">{{ $data['meetings'] }}</div>
            </div>
            <div class="icon icon-box-warning">
              <span class="ti-calendar"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small mb-1">Folders & Subfolders</div>
              <div class="h3 mb-0">{{ $data['folders'] }}</div>
            </div>
            <div class="icon icon-box-primary">
              <span class="icon-layout"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small mb-1">Groups</div>
              <div class="h3 mb-0">{{ $data['groups'] }}</div>
            </div>
            <div class="icon icon-box-danger">
              <span class="ti-comments"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small mb-1">Users</div>
              <div class="h3 mb-0">{{ $data['users'] }}</div>
            </div>
            <div class="icon icon-box-dark">
              <span class="ti-user"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
<style>
  .icon-box-success, .icon-box-info, .icon-box-warning, .icon-box-primary, .icon-box-danger, .icon-box-dark {
    width: 48px; height: 48px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,0.03);
  }
  .icon-box-success { background: rgba(40, 167, 69, .12); }
  .icon-box-info    { background: rgba(23, 162, 184, .12); }
  .icon-box-warning { background: rgba(255, 193, 7,  .16); }
  .icon-box-primary { background: rgba(0, 123, 255, .12); }
  .icon-box-danger  { background: rgba(220, 53, 69, .12); }
  .icon-box-dark    { background: rgba(52, 58, 64, .12); }
  .gap-2 { gap: .5rem; }
</style>
@endpush
