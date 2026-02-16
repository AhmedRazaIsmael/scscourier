@extends('layouts.master')
@section('title', 'Search Booking')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $bookingColumns = [
        'bookNo' => 'Book No',
        'bookDate' => 'Book Date',
        'company' => 'Company',
        'customer_name' => 'Customer',
        'product' => 'Product',
        'service' => 'Service',
        'thirdPartyCompany' => '3PL Company',
        'thirdPartyReference' => '3PL Reference',
        'itemContent' => 'Item Content',
        'paymentMode' => 'Payment Mode',
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
        'codAmount' => 'COD Amount',
        'arrivalClear' => 'Arrival Clear',
        'userId' => 'User ID',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Search Booking</h2></div>
        </div>
    </div>

    <div class="app-body">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Booking Data</h5></div>

            <div class="card-body">

                <!-- Date Filter -->
                <div class="d-flex gap-2 mb-2 align-items-center">
                    <label class="mb-0">From:</label>
                    <input type="date" id="fromDate" class="form-control form-control-sm" onchange="filterTable()">
                    <label class="mb-0">To:</label>
                    <input type="date" id="toDate" class="form-control form-control-sm" onchange="filterTable()">
                </div>

                <!-- Table Controls -->
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
                        @include('layouts.actions-dropdown', [
                            'downloadRoute' => route('bookings.download'),
                            'showChart' => true
                        ])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Booking Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                @foreach($bookingColumns as $key => $label)
                                    <th class="header-cell sortable" data-column="{{ $key }}">{{ $label }} <span class="sort-icon"></span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                @php $thirdParty = $booking->thirdparty ?? null; @endphp
                                <tr class="data-row">
                                    <td class="data-cell" data-column="bookNo">{{ $booking->bookNo ?? '-' }}</td>
                                    <td class="data-cell" data-column="bookDate">{{ $booking->bookDate ? \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y') : '-' }}</td>
                                    <td class="data-cell" data-column="company">{{ $booking->customer->company_name ?? 'ABC Express' }}</td>
                                    <td class="data-cell" data-column="customer_name">{{ $booking->customer->customer_name ?? '-' }}</td>
                                    <td class="data-cell" data-column="product">{{ $booking->customer->product ?? '-' }}</td>
                                    <td class="data-cell" data-column="service">{{ $booking->service ?? '-' }}</td>
                                    <td class="data-cell" data-column="thirdPartyCompany">{{ $thirdParty->company_name ?? '-' }}</td>
                                    <td class="data-cell" data-column="thirdPartyReference">{{ $thirdParty->ref_no ?? '-' }}</td>
                                    <td class="data-cell" data-column="itemContent">{{ $booking->itemContent ?? $thirdParty->remarks ?? '-' }}</td>
                                    <td class="data-cell" data-column="paymentMode">{{ $booking->paymentMode ?? '-' }}</td>
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
                                    <td class="data-cell" data-column="codAmount">{{ $booking->codAmount ?? '-' }}</td>
                                    <td class="data-cell" data-column="arrivalClear">{{ $booking->arrivalClearance ?? '-' }}</td>
                                    <td class="data-cell" data-column="userId">{{ $thirdParty->updated_by ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($bookingColumns) }}" class="text-center text-muted py-3">No bookings found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.dashboard-modals', [
    'columns' => $bookingColumns,
    'chartModel' => 'BookingStatus',
    'chartAction' => route('booking.chart'),
    'filterAction' => route('search.data'),
    'rowFilterAction' => route('booking.rowFilter'),
    'sortAction' => route('search.data'),
    'aggregateAction' => route('booking.aggregate'),
    'computeAction' => route('booking.compute'),
    'downloadAction' => route('bookings.download'),
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

    // Filter table with column search and date range
    function filterTable() {
        const input = document.getElementById('searchInput').value.toUpperCase();
        const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
        const fromDate = document.getElementById('fromDate').value;
        const toDate = document.getElementById('toDate').value;
        const rows = document.querySelectorAll('#dataTable tbody tr.data-row');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;

            // Column search
            if(selectedColumn === 'all') {
                match = [...cells].some(td => td.innerText.toUpperCase().includes(input));
            } else {
                const index = [...document.querySelectorAll('.header-cell')].findIndex(h => h.dataset.column === selectedColumn);
                match = cells[index] && cells[index].innerText.toUpperCase().includes(input);
            }

            // Date filter (bookDate column)
            const bookDateCell = row.querySelector('td[data-column="bookDate"]');
            if(bookDateCell && (fromDate || toDate)) {
                const cellDate = new Date(bookDateCell.innerText);
                const from = fromDate ? new Date(fromDate) : null;
                const to = toDate ? new Date(toDate) : null;

                if(from && cellDate < from) match = false;
                if(to && cellDate > to) match = false;
            }

            row.style.display = match ? '' : 'none';
        });
    }

    // Clear all filters
    function clearSearchAndReset() {
        document.getElementById('searchInput').value = '';
        document.querySelector('input[value="all"]').checked = true;
        document.getElementById('fromDate').value = '';
        document.getElementById('toDate').value = '';
        filterTable();
    }
</script>
@endpush

@endsection
