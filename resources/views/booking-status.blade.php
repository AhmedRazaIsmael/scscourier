@extends('layouts.master')
@section('title', 'Booking Status')

@section('content')
@php
    $bookingColumns = [
        'bookNo' => 'Book No',
        'bookDate' => 'Book Date',
        'statusDateTime' => 'Status Date/Time',
        'trackStatus' => 'Track Status',
        'customer_name' => 'Customer',
        'product' => 'Product',
        'service' => 'Service',
        'itemContent' => 'Item Content',
        'origin' => 'Origin',
        'destination' => 'Destination',
        'weight' => 'Weight (KG)',
        'pieces' => 'Pieces',
        'orderNo' => 'Order No',
        'shipperName' => 'Shipper Name',
        'shipperNumber' => 'Shipper Contact No',
        'shipperAddress' => 'Shipper Address',
        'consigneeName' => 'Consignee Name',
        'consigneeNumber' => 'Consignee Contact No',
        'consigneeAddress' => 'Consignee Address',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-table fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Booking Status</h2></div>
        </div>
    </div>

    <div class="app-body">
        <div class="card mb-4">

            {{-- Table Controls --}}
            <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                <div class="search-group-container">
                    <button class="search-options-button" id="searchOptionsButton">🔍</button>
                    <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                    <div class="search-options-menu" id="searchOptionsMenu">
                        <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                        @foreach($bookingColumns as $key => $label)
                            <label><input type="radio" name="searchColumn" value="{{ $key }}"> {{ $label }}</label>
                        @endforeach
                    </div>
                    <button class="go-button" onclick="filterTable()">Go</button>
                </div>
                <div class="right-controls">
                    @include('layouts.actions-dropdown', ['downloadRoute' => route('pending.download')])
                    <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                </div>
            </div>

            {{-- Booking Table --}}
            <div class="table-container">
                <table class="data-table table-striped table-hover" id="dataTable">
                    <thead>
                        <tr class="header-row">
                            @foreach($bookingColumns as $key => $label)
                                <th class="header-cell sortable" data-column="{{ $key }}">
                                    {{ $label }} <span class="sort-icon"></span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            @php $latestStatus = $booking->latestStatusMixed(); @endphp
                            <tr class="data-row">
                                <td class="data-cell" data-column="bookNo">
                                    <a href="{{ route('booking.status.edit', $booking->id) }}" class="text-primary fw-bold">
                                        {{ $booking->bookNo ?? '-' }}
                                    </a>
                                </td>
                                <td class="data-cell" data-column="bookDate">{{ $booking->bookDate ? \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y') : '-' }}</td>
                                <td class="data-cell" data-column="statusDateTime">{{ $latestStatus ? $latestStatus->created_at->format('d-M-Y H:i:s') : '-' }}</td>
                                <td class="data-cell" data-column="trackStatus">{{ $latestStatus->status ?? '-' }}</td>
                                <td class="data-cell" data-column="customer_name">{{ optional($booking->customer)->customer_name ?? '-' }}</td>
                                <td class="data-cell" data-column="product">{{ optional($booking->customer)->product ?? '-' }}</td>
                                <td class="data-cell" data-column="service">{{ $booking->service ?? '-' }}</td>
                                <td class="data-cell" data-column="itemContent">{{ $booking->itemContent ?? '-' }}</td>
                                <td class="data-cell" data-column="origin">{{ $booking->origin ?? '-' }}</td>
                                <td class="data-cell" data-column="destination">{{ $booking->destination ?? '-' }}</td>
                                <td class="data-cell" data-column="weight">{{ $booking->weight ?? '-' }}</td>
                                <td class="data-cell" data-column="pieces">{{ $booking->pieces ?? '-' }}</td>
                                <td class="data-cell" data-column="orderNo">{{ $booking->orderNo ?? '-' }}</td>
                                <td class="data-cell" data-column="shipperName">{{ $booking->shipperName ?? '-' }}</td>
                                <td class="data-cell" data-column="shipperNumber">{{ $booking->shipperNumber ?? '-' }}</td>
                                <td class="data-cell" data-column="shipperAddress">{{ $booking->shipperAddress ?? '-' }}</td>
                                <td class="data-cell" data-column="consigneeName">{{ $booking->consigneeName ?? '-' }}</td>
                                <td class="data-cell" data-column="consigneeNumber">{{ $booking->consigneeNumber ?? '-' }}</td>
                                <td class="data-cell" data-column="consigneeAddress">{{ $booking->consigneeAddress ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($bookingColumns) }}" class="text-center text-muted py-3">No bookings found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
</div>
                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.dashboard-modals', [
    'columns' => $bookingColumns,
    'downloadAction' => route('pending.download')
])

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
        const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            if (selectedColumn === 'all') {
                row.style.display = row.textContent.toUpperCase().includes(filter) ? '' : 'none';
            } else {
                const cell = row.querySelector(`[data-column="${selectedColumn}"]`);
                row.style.display = cell && cell.textContent.toUpperCase().includes(filter) ? '' : 'none';
            }
        });
    }

    function clearSearchAndReset() {
        document.getElementById('searchInput').value = '';
        filterTable();
    }
</script>
@endpush
@endsection
