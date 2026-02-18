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

                        {{-- Success / Error Alert --}}
                        @if(session('scan_success'))
                        <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('scan_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        @if(session('scan_error'))
                        <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('scan_error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <form action="{{ route('scan.form', ['type' => $type]) }}" method="GET" id="scanForm">
                            <div class="row gx-4 mb-3">
                                <div class="col-md-12">
                                    <label for="bookNo" class="form-label fw-semibold">
                                        <i class="bi bi-upc-scan me-1"></i> Scan or Enter Book No
                                        <span class="badge bg-success ms-2">
                                            <i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>Scanner Ready
                                        </span>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" name="bookNo" class="form-control"
                                            id="bookNo"
                                            placeholder="Scan barcode / QR or type Book No"
                                            autocomplete="off"
                                            autofocus
                                            required>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send-fill"></i> Submit
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Scan barcode or QR code — form will submit automatically. You can scan multiple items one by one.
                                    </small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="clearSession()">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset / Clear List
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== SCAN TABLE =================== --}}
        @if($scans instanceof \Illuminate\Pagination\LengthAwarePaginator ? $scans->isNotEmpty() : $scans->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ ucfirst($type) }} Scan — This Session</h5>
                <span class="badge bg-primary fs-6">
                    {{ $scans instanceof \Illuminate\Pagination\LengthAwarePaginator ? $scans->total() : $scans->count() }} Scanned
                </span>
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
<script>
    (function() {
        const input = document.getElementById('bookNo');
        const form = document.getElementById('scanForm');

        // Auto focus
        if (input) input.focus();

        // Re-focus on page click
        document.addEventListener('click', function(e) {
            if (e.target.id !== 'bookNo') {
                setTimeout(() => input && input.focus(), 50);
            }
        });

        // Scanner submits with Enter
        input && input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const raw = input.value.trim();
                if (!raw) return;

                // Fix double-scan
                input.value = fixDouble(raw);
                form.submit();
            }
        });

        function fixDouble(val) {
            const len = val.length;
            if (len >= 4 && len % 2 === 0) {
                const half = val.substring(0, len / 2);
                if (half === val.substring(len / 2)) return half;
            }
            return val;
        }

        // Auto dismiss alerts after 3 seconds
        document.querySelectorAll('.auto-dismiss').forEach(function(el) {
            setTimeout(function() {
                el.style.transition = 'opacity 0.6s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 600);
            }, 3000);
        });

        // Re-focus after alert close
        document.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', () => setTimeout(() => input && input.focus(), 200));
        });
    })();

    // Clear session list
    function clearSession() {
        if (confirm('Session list clear ho jayegi. Confirm?')) {
            const url = new URL(window.location.href);
            url.searchParams.set('clearSession', '1');
            url.searchParams.delete('bookNo');
            window.location = url.toString();
        }
    }
</script>
@endpush
@endsection