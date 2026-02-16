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
                <h2 class="mb-1">Edit Booking Status: <span class="text-primary"></span></h2>
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

        <!-- Status Update Form -->
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
            </tr>
        </thead>
        <tbody>
            @forelse($booking->statuses as $status)
                <tr class="data-row">
                    <td class="data-cell" data-column="statusDateTime">{{ $status->created_at->format('d-M-Y H:i:s') }}</td>
                    <td class="data-cell" data-column="status">{{ $status->status }}</td>
                    <td class="data-cell" data-column="description">{{ $status->description ?? 'N/A' }}</td>
                    <td class="data-cell" data-column="user">{{ $status->user->name ?? 'System' }}</td>
                    <td class="data-cell" data-column="updated_at">{{ $status->updated_at->format('d-M-Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">No status history yet</td>
                </tr>
            @endforelse
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
/* Ensure table headers and cells align properly */
.data-table {
    width: 100%;
    table-layout: fixed; /* Fix column widths */
    border-collapse: collapse;
}

.data-table th, .data-table td {
    padding: 8px 12px;
    word-wrap: break-word;
    text-align: left;
}

.data-table th {
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>

<script>
function filterTable() {
    const input = document.getElementById('searchInput').value.toUpperCase();
    const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
    const rows = document.querySelectorAll('#dataTable tbody tr.data-row');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;
        if(selectedColumn === 'all') {
            match = [...cells].some(td => td.innerText.toUpperCase().includes(input));
        } else {
            const index = [...document.querySelectorAll('.header-cell')].findIndex(h => h.dataset.column === selectedColumn);
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
</script>
@endpush

@endsection
