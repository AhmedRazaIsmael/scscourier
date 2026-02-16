@extends('layouts.master')

@section('title', 'Uninvoiced Import Bookings')

@section('content')
<div class="app-container">

    <!-- 🔹 Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-archive fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Uninvoiced Import Bookings</h2></div>
        </div>
    </div>

    <div class="app-body">

        <!-- =================== DATA TABLE =================== -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Uninvoiced Bookings Data</h5></div>
            <div class="card-body">

                <!-- 🔹 Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">

                        <!-- 🔽 Search Column Dropdown -->
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="book_no"> Book No</label>
                            <label><input type="radio" name="searchColumn" value="book_date"> Book Date</label>
                            <label><input type="radio" name="searchColumn" value="customer_name"> Customer</label>
                            <label><input type="radio" name="searchColumn" value="origin"> Origin</label>
                            <label><input type="radio" name="searchColumn" value="destination"> Destination</label>
                            <label><input type="radio" name="searchColumn" value="shipper_name"> Shipper Name</label>
                            <label><input type="radio" name="searchColumn" value="consignee_name"> Consignee Name</label>
                        </div>

                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => '#'])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- 🔹 Data Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="book_no">Book No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="book_date">Book Date <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="customer_name">Customer <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="origin">Origin <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="destination">Destination <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="weight">Weight <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="pieces">Pcs <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="shipper_name">Shipper Name <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="consignee_name">Consignee Name <span class="sort-icon"></span></th>
                               <th class="header-cell sortable" data-column="action">Action<span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody id="bookingsTableBody">
                            @forelse($bookings as $booking)
                                <tr class="data-row">
                                    <td class="data-cell" data-column="book_no">{{ $booking->bookNo }}</td>
                                    <td class="data-cell" data-column="book_date">{{ \Carbon\Carbon::parse($booking->bookDate)->format('d-m-Y') }}</td>
                                    <td class="data-cell" data-column="customer_name">{{ $booking->customer->customer_name ?? '-' }}</td>
                                    <td class="data-cell" data-column="origin">{{ $booking->origin ?? '-' }}</td>
                                    <td class="data-cell" data-column="destination">{{ $booking->destination ?? '-' }}</td>
                                    <td class="data-cell" data-column="weight">{{ $booking->weight ?? '-' }}</td>
                                    <td class="data-cell" data-column="pieces">{{ $booking->pieces ?? '-' }}</td>
                                    <td class="data-cell">{{ $booking->shipperName ?? '-' }}</td>
                                    <td class="data-cell">{{ $booking->consigneeName ?? '-' }}</td>
                                    <td class="data-cell">
                                        <a href="{{ route('invoice.import.create', ['bookNo' => $booking->bookNo]) }}" class="btn btn-sm btn-primary">Create Invoice</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-3">No bookings found</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
    // Toggle search options menu
    document.getElementById('searchOptionsButton').addEventListener('click', function() {
        const menu = document.getElementById('searchOptionsMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });

    // Search / Filter Table
    function filterTable() {
        const filter = document.getElementById('searchInput').value.toUpperCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toUpperCase().includes(filter) ? '' : 'none';
        });
    }

    function clearSearchAndReset() {
        document.getElementById('searchInput').value = '';
        filterTable();
    }
</script>
@endpush
