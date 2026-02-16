@extends('layouts.master')
@section('title', 'Bulk Label')

@section('content')
<div class="app-container">
    <!-- 🔹 Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Bulk Label</h2></div>
        </div>
    </div>

    <div class="app-body">
        {{-- =================== DATA TABLE =================== --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Bulk Label Data</h5></div>
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
                            <label><input type="radio" name="searchColumn" value="customer"> Customer</label>
                            <label><input type="radio" name="searchColumn" value="origin"> Origin</label>
                            <label><input type="radio" name="searchColumn" value="destination"> Destination</label>
                        </div>

                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => route('bookings.download')])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                        <button class="btn btn-secondary ms-2" onclick="printSelectedLabels()">Print Labels</button>
                    </div>
                </div>

                <!-- 🔹 Data Table -->
                <form method="POST" id="bulkActionForm">
                    @csrf
                    <div class="table-container">
                        <table class="data-table" id="dataTable">
                            <thead>
                                <tr class="header-row">
                                    <th class="header-cell checkbox-cell"><input type="checkbox" id="selectAll"></th>
                                    <th class="header-cell sortable" data-column="book_no">Book No <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="book_date">Book Date <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="customer">Customer <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="product">Product <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="service">Service <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="item_content">Item Content <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="origin">Origin <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="destination">Destination <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="weight">Weight <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="pieces">Pieces <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="shipper_name">Shipper Name <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="shipper_contact">Shipper Contact <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="shipper_address">Shipper Address <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="consignee_name">Consignee Name <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="consignee_contact">Consignee Contact <span class="sort-icon"></span></th>
                                    <th class="header-cell sortable" data-column="consignee_address">Consignee Address <span class="sort-icon"></span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bookings as $booking)
                                    <tr class="data-row">
                                        <td class="data-cell checkbox-cell">
                                            <input type="checkbox" name="booking_ids[]" class="row-checkbox" value="{{ $booking->id }}">
                                        </td>
                                        <td class="data-cell">{{ $booking->bookNo ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->bookDate ? \Carbon\Carbon::parse($booking->bookDate)->format('d-m-Y') : '-' }}</td>
                                        <td class="data-cell">{{ $booking->customer->customer_name ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->customer->product ?? '-' }}</td>
                                        <td class="data-cell">{{ ucfirst($booking->service ?? '-') }}</td>
                                        <td class="data-cell">{{ $booking->itemContent ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->origin ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->destination ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->weight ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->pieces ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->shipperName ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->shipperNumber ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->shipperAddress ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->consigneeName ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->consigneeNumber ?? '-' }}</td>
                                        <td class="data-cell">{{ $booking->consigneeAddress ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="17" class="text-center text-muted py-3">No bookings found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.dashboard-modals', [
    'columns' => ['book_no','book_date','company_name','ref_no','remarks','customer','shipper','consignee','updated_by','updated_at'],
    'chartModel' => 'ThirdPartyBooking',
    // 'chartAction' => route('thirdparty.chart'),
    // 'filterAction' => route('thirdparty.index'),
    // 'rowFilterAction' => route('thirdparty.rowFilter'),
    // 'sortAction' => route('thirdparty.index'),
    // 'aggregateAction' => route('thirdparty.aggregate'),
    // 'computeAction' => route('thirdparty.compute'),
    'downloadAction' => route('bookings.download')
])
@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
<style>
    .data-table td, .data-table th {
        vertical-align: middle;
        white-space: normal;
        word-wrap: break-word;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }
    .data-table td[data-column="shipper_address"],
    .data-table td[data-column="consignee_address"],
    .data-table td[data-column="item_content"] {
        max-width: 250px;
    }
    .data-table tr { height: 48px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
    // Select All
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Search / Filter
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

    // Print Selected Labels
    function printSelectedLabels() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) { alert('Please select at least one booking.'); return; }

        const url = '{{ route("print.bulk.label") }}';
        const token = document.querySelector('input[name="_token"]').value;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = token;
        form.appendChild(csrfInput);

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'booking_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
@endpush
@endsection
