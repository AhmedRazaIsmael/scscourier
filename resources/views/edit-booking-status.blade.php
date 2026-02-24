@extends('layouts.master')

@section('title', 'Edit Booking Status')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Booking Status: <span class="text-primary">{{ $booking->bookNo }}</span></h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- ===== SONIC API STATUS CARD ===== --}}
        <div class="row gx-4 mb-4">
            <div class="col-sm-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-truck me-2"></i>Sonic Courier Live Status
                            <small class="ms-2 opacity-75">#{{ $booking->bookNo }}</small>
                        </h5>
                        <button type="button" class="btn btn-sm btn-light" onclick="loadSonicStatus()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">

                        {{-- Loading State --}}
                        <div class="text-center py-3" id="sonicLoading">
                            <div class="spinner-border text-info" role="status"></div>
                            <p class="mt-2 text-muted">Fetching Sonic status...</p>
                        </div>

                        {{-- Success State --}}
                        <div id="sonicStatusContent" style="display:none;">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Current Status</small>
                                        <span id="sonic_current_status" class="fw-bold text-success fs-6">-</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Status Date/Time</small>
                                        <span id="sonic_status_datetime" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Reason</small>
                                        <span id="sonic_reason" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Origin</small>
                                        <span id="sonic_origin" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Destination</small>
                                        <span id="sonic_destination" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Order Date</small>
                                        <span id="sonic_order_date" class="fw-bold">-</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Booking Date</small>
                                        <span id="sonic_booking_date" class="fw-bold">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Error State --}}
                        <div id="sonicStatusError" style="display:none;" class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span id="sonicErrorMsg">Could not fetch Sonic status.</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        {{-- ===== END SONIC API STATUS CARD ===== --}}

        {{-- ===== UPDATE BOOKING STATUS FORM (COMMENTED OUT) =====
        <div class="row gx-4 mb-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Update Booking Status</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('booking.status.update', $booking->id) }}" method="post">
        @csrf
        <div class="row gx-4 mb-4">
            <div class="col-md-4">
                <label class="form-label">Book No.</label>
                <input type="text" class="form-control" value="{{ $booking->bookNo }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <input type="text" name="status" class="form-control"
                    value="{{ optional($booking->statuses->last())->status ?? '' }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status Date/Time</label>
                <input type="datetime-local" name="statusDateTime" class="form-control"
                    value="{{ now()->format('Y-m-d\TH:i') }}">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Status Description</label>
            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary">Update Status</button>
            <button type="reset" class="btn btn-outline-secondary">Reset</button>
        </div>
        </form>
    </div>
</div>
</div>
</div>
===== END UPDATE BOOKING STATUS FORM ===== --}}

<!-- Status History Table -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Booking Status History</h5>
    </div>
    <div class="card-body">
        <!-- Table Controls -->
        <div class="table-controls modern-controls d-flex justify-content-between mb-2">
            <div class="search-group-container">
                <button class="search-options-button" id="searchOptionsButton">🔍</button>
                <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                <div class="search-options-menu" id="searchOptionsMenu">
                    <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                    <label><input type="radio" name="searchColumn" value="statusDateTime">Status Date/Time</label>
                    <label><input type="radio" name="searchColumn" value="status">Status</label>
                    <label><input type="radio" name="searchColumn" value="description">Status Description</label>
                    <label><input type="radio" name="searchColumn" value="user">Updated By</label>
                    <label><input type="radio" name="searchColumn" value="updated_at">Updated Date/Time</label>
                    <label><input type="radio" name="searchColumn" value="source">Source</label>
                </div>
                <button class="go-button" onclick="filterTable()">Go</button>
            </div>
            <div class="right-controls">
                <button class="reset-button btn btn-outline-secondary" onclick="clearSearchAndReset()">Reset</button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr class="header-row">
                        <th class="header-cell sortable" data-column="statusDateTime">Status Date/Time <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="status">Status <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="description">Status Description <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="user">Updated By <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="updated_at">Updated Date/Time <span class="sort-icon"></span></th>
                        <th class="header-cell" data-column="source">Source <span class="sort-icon"></span></th>
                    </tr>
                </thead>
                <tbody id="statusHistoryTbody">
                    {{-- DB se aaya hua data --}}
                    @forelse($booking->statuses as $status)
                    <tr class="data-row">
                        <td class="data-cell" data-column="statusDateTime">{{ $status->created_at->format('d-M-Y H:i:s') }}</td>
                        <td class="data-cell" data-column="status">{{ $status->status }}</td>
                        <td class="data-cell" data-column="description">{{ $status->description ?? 'N/A' }}</td>
                        <td class="data-cell" data-column="user">{{ $status->user->name ?? 'System' }}</td>
                        <td class="data-cell" data-column="updated_at">{{ $status->updated_at->format('d-M-Y H:i:s') }}</td>
                        <td class="data-cell" data-column="source">
                            <span class="badge bg-primary">Internal</span>
                        </td>
                    </tr>
                    @empty
                    <tr id="noDbRecord">
                        <td colspan="6" class="text-center text-muted py-3">No status history yet</td>
                    </tr>
                    @endforelse

                    {{-- Sonic API row - JS se inject hoga --}}
                    <tr id="sonicHistoryRow" style="display:none;" class="data-row table-info">
                        <td class="data-cell" data-column="statusDateTime" id="hist_sonic_datetime">-</td>
                        <td class="data-cell" data-column="status" id="hist_sonic_status">-</td>
                        <td class="data-cell" data-column="description" id="hist_sonic_reason">-</td>
                        <td class="data-cell" data-column="user">Sonic API</td>
                        <td class="data-cell" data-column="updated_at" id="hist_sonic_updated">-</td>
                        <td class="data-cell" data-column="source">
                            <span class="badge bg-info text-dark">Sonic</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
<style>
    .data-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 8px 12px;
        word-wrap: break-word;
        text-align: left;
    }

    .data-table th {
        background-color: #f8f9fa;
    }

    .table-info td {
        background-color: #e8f4f8 !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
    const sonicApiRoute = "{{ route('booking.sonic.status', $booking->bookNo) }}";

    function loadSonicStatus() {
        // Top card reset
        document.getElementById('sonicLoading').style.display = '';
        document.getElementById('sonicStatusContent').style.display = 'none';
        document.getElementById('sonicStatusError').style.display = 'none';

        // History row hide
        document.getElementById('sonicHistoryRow').style.display = 'none';

        fetch(sonicApiRoute)
            .then(response => {
                // Pehle check karo ke response JSON hai ya HTML
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response. Check route/auth.');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('sonicLoading').style.display = 'none';

                if (data.error) {
                    document.getElementById('sonicErrorMsg').textContent = data.error;
                    document.getElementById('sonicStatusError').style.display = '';
                    return;
                }

                // ===== Top Card fill =====
                document.getElementById('sonic_current_status').textContent = data.current_status ?? '-';
                document.getElementById('sonic_status_datetime').textContent = data.current_status_datetime ?? '-';
                document.getElementById('sonic_reason').textContent = data.reason ?? 'N/A';
                document.getElementById('sonic_origin').textContent = data.origin ?? '-';
                document.getElementById('sonic_destination').textContent = data.destination ?? '-';
                document.getElementById('sonic_order_date').textContent = data.order_date ?? '-';
                document.getElementById('sonic_booking_date').textContent = data.booking_date ?
                    data.booking_date.substring(0, 10) : '-';
                document.getElementById('sonicStatusContent').style.display = '';

                // ===== History Table row fill =====
                const now = new Date().toLocaleString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                document.getElementById('hist_sonic_datetime').textContent = data.current_status_datetime ?? '-';
                document.getElementById('hist_sonic_status').textContent = data.current_status ?? '-';
                document.getElementById('hist_sonic_reason').textContent = data.reason ?? 'N/A';
                document.getElementById('hist_sonic_updated').textContent = now;

                // "No status history" row hatao agar tha
                const noRecord = document.getElementById('noDbRecord');
                if (noRecord) noRecord.style.display = 'none';

                document.getElementById('sonicHistoryRow').style.display = '';
            })
            .catch(err => {
                document.getElementById('sonicLoading').style.display = 'none';
                document.getElementById('sonicErrorMsg').textContent = 'Network error: ' + err.message;
                document.getElementById('sonicStatusError').style.display = '';
            });
    }

    // Page load pe auto call
    document.addEventListener('DOMContentLoaded', loadSonicStatus);

    // Table search
    function filterTable() {
        const input = document.getElementById('searchInput').value.toUpperCase();
        const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
        document.querySelectorAll('#dataTable tbody tr.data-row').forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;
            if (selectedColumn === 'all') {
                match = [...cells].some(td => td.innerText.toUpperCase().includes(input));
            } else {
                const index = [...document.querySelectorAll('.header-cell')]
                    .findIndex(h => h.dataset.column === selectedColumn);
                match = cells[index] && cells[index].innerText.toUpperCase().includes(input);
            }
            row.style.display = match ? '' : 'none';
        });
    }

    function clearSearchAndReset() {
        document.getElementById('searchInput').value = '';
        document.querySelector('input[value="all"]').checked = true;
        filterTable();
    }

    // Search options toggle
    document.getElementById('searchOptionsButton').addEventListener('click', function() {
        const menu = document.getElementById('searchOptionsMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
</script>
@endpush

@endsection