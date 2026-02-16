@extends('layouts.master')
@section('title', 'Bulk Booking Status')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Bulk Booking Status</h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        {{-- =================== FORM =================== --}}
        <div class="row gx-4 mb-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Bulk Status</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('booking.status.updateSelected') }}" method="POST">
                            @csrf

                            {{-- =================== BULK STATUS FORM =================== --}}
                            <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label for="status_date" class="form-label">Status Date/Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="status_date" id="status_date" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <input type="text" name="status" id="status" class="form-control" placeholder="Enter status" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                                </div>
                            </div>

                            {{-- =================== DATA TABLE =================== --}}
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Booking Status Data</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                                        <div class="search-group-container">
                                            <button class="search-options-button" id="searchOptionsButton">🔍</button>
                                            <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                                            <div class="search-options-menu" id="searchOptionsMenu">
                                                <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                                                <label><input type="radio" name="searchColumn" value="bookNo"> Book No</label>
                                                <label><input type="radio" name="searchColumn" value="bookDate"> Book Date</label>
                                                <label><input type="radio" name="searchColumn" value="statusDateTime"> Status Date/Time</label>
                                                <label><input type="radio" name="searchColumn" value="trackStatus"> Track Status</label>
                                                <label><input type="radio" name="searchColumn" value="customer_name"> Customer</label>
                                                <label><input type="radio" name="searchColumn" value="itemContent"> Item Content</label>
                                                <label><input type="radio" name="searchColumn" value="origin"> Origin</label>
                                                <label><input type="radio" name="searchColumn" value="destination"> Destination</label>
                                                <label><input type="radio" name="searchColumn" value="weight"> Weight</label>
                                                <label><input type="radio" name="searchColumn" value="pieces"> Pieces</label>
                                                <label><input type="radio" name="searchColumn" value="orderNo"> Order No</label>
                                                <label><input type="radio" name="searchColumn" value="shipperName"> Shipper Name</label>
                                                <label><input type="radio" name="searchColumn" value="shipperNumber"> Shipper Contact No.</label>
                                                <label><input type="radio" name="searchColumn" value="shipperAddress"> Shipper Address</label>
                                                <label><input type="radio" name="searchColumn" value="consigneeName"> Consignee Name</label>
                                                <label><input type="radio" name="searchColumn" value="consigneeNumber"> Consignee Contact No.</label>
                                                <label><input type="radio" name="searchColumn" value="consigneeAddress"> Consignee Address</label>
                                            </div>
                                            <button class="go-button" type="button" onclick="filterTable()">Go</button>
                                        </div>
                                        <div class="right-controls">
                                            @include('layouts.actions-dropdown', ['downloadRoute' => route('bookingStatus.download')])
                                            <button class="reset-button btn btn-outline-secondary ms-2" type="button" onclick="clearSearchAndReset()">Reset</button>
                                        </div>
                                    </div>

                                    <div class="table-container">
                                        <table class="data-table" id="dataTable">
                                            <thead>
                                                <tr class="header-row">
                                                    <th class="header-cell sortable" data-column="selectAll"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                                                    <th class="header-cell sortable" data-column="bookNo">Book No <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="bookDate">Book Date <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="statusDateTime">Status Date/Time <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="trackStatus">Track Status <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="customer_name">Customer <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="itemContent">Item Content <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="origin">Origin <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="destination">Destination <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="weight">Weight <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="pieces">Pieces <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="orderNo">Order No <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="shipperName">Shipper Name <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="shipperNumber">Shipper Contact No. <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="shipperAddress">Shipper Address <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="consigneeName">Consignee Name <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="consigneeNumber">Consignee Contact No. <span class="sort-icon"></span></th>
                                                    <th class="header-cell sortable" data-column="consigneeAddress">Consignee Address <span class="sort-icon"></span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($bookings as $booking)
                                                  @foreach($booking->allStatusesMixed() as $status)
                                                    <tr class="data-row">
                                                        <td><input type="checkbox" class="select-booking" name="selected_bookings[]" value="{{ $booking->id }}"></td>
                                                        <td class="data-cell">{{ $booking->bookNo ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->bookDate ? \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y') : '-' }}</td>
                                                        <td class="data-cell">{{ $status->created_at->format('d-M-Y H:i:s') }}</td>
                                                        <td class="data-cell">{{ $status->status ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->customer?->customer_name ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->itemContent ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->origin ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->destination ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->weight ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->pieces ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->orderNo ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->shipperName ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->shipperNumber ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->shipperAddress ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->consigneeName ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->consigneeNumber ?? '-' }}</td>
                                                        <td class="data-cell">{{ $booking->consigneeAddress ?? '-' }}</td>
                                                    </tr>
                                                  @endforeach
                                                @empty
                                                    <tr>
                                                        <td colspan="18" class="text-center text-muted py-3">No bookings found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3">
                                        {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
 @include('layouts.dashboard-modals', [
    'downloadAction' => route('bookingStatus.download')
])                   
@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
function toggleSelectAll(source) {
    document.querySelectorAll('.select-booking').forEach(cb => cb.checked = source.checked);
}

function filterTable() {
    const input = document.getElementById('searchInput').value.toUpperCase();
    const column = document.querySelector('input[name="searchColumn"]:checked').value;
    document.querySelectorAll('#dataTable tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;
        if(column === 'all') {
            match = [...cells].some(td => td.innerText.toUpperCase().includes(input));
        } else {
            const headerCells = [...row.closest('table').querySelectorAll('th')];
            const idx = headerCells.findIndex(h => h.dataset.column === column);
            match = idx >= 0 && cells[idx]?.innerText.toUpperCase().includes(input);
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
