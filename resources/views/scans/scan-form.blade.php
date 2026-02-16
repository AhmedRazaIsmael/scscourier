@extends('layouts.master')
@section('title', isset($type) ? ucfirst($type) . ' Scan' : 'Scan')

@section('content')
<div class="app-container">
    <!-- Hero Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">{{ isset($type) ? ucfirst($type) : 'Scan' }} Scan</h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        {{-- =================== FORM =================== --}}
        <div class="row gx-4 mb-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Scan {{ ucfirst($type) }}</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('scan.form', ['type' => $type]) }}" method="GET">
                            <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label for="bookNo" class="form-label">Enter Book No</label>
                                    <input type="text" name="bookNo" class="form-control" id="bookNo"
                                           value="{{ request('bookNo') }}" placeholder="Enter Book No" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary"
                                    onclick="window.location='{{ route('scan.form', ['type' => $type]) }}'">
                                Reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== SCAN TABLE =================== --}}
        @if(request()->filled('bookNo') && $scans->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">{{ ucfirst($type) }} Scan Data</h5>
            </div>
            <div class="card-body">
                <!-- Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="book_no"> Book No</label>
                            <label><input type="radio" name="searchColumn" value="scan_datetime"> Scan Date/Time</label>
                            <label><input type="radio" name="searchColumn" value="hub"> Hub</label>
                            <label><input type="radio" name="searchColumn" value="scanned_by"> Scanned By</label>
                            <label><input type="radio" name="searchColumn" value="status"> Status</label>
                            <label><input type="radio" name="searchColumn" value="updated_by"> Updated By</label>
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>
                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute'])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="book_no">Book No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="scan_datetime">Scan Date/Time <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="hub">Hub <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="scanned_by">Scanned By <span class="sort-icon"></span></th>
                                
                                <th class="header-cell sortable" data-column="updated_by">Updated By <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="status">Status <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody id="scansTableBody">
                            @forelse($scans as $scan)
                                <tr class="data-row">
                                    <td class="data-cell" data-column="book_no">{{ $scan->book_no }}</td>
                                    <td class="data-cell" data-column="scan_datetime">{{ $scan->created_at->format('d-M-Y H:i:s') }}</td>
                                    <td class="data-cell" data-column="hub">{{ $scan->hub->name ?? $scan->booking->destination ?? '-' }}</td>
                                    <td class="data-cell" data-column="scanned_by">{{ $scan->user ? strtoupper($scan->user->name) : '-' }}</td>
                                    
                                    <td class="data-cell" data-column="updated_by">{{ $scan->user ? $scan->user->name : '-' }}</td>
                                    <td class="data-cell" data-column="status">{{ $scan->latestBookingStatus->status ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No scans found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $scans->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@include('layouts.dashboard-modals', [
    'columns' => ['book_no','scan_datetime','hub','scanned_by','status','updated_by'],
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
@endpush
@endsection
