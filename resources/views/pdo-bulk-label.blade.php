@extends('layouts.master')

@section('title', 'POD Bulk Label')

@section('content')
<div class="app-container">
    <!-- Hero Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">POD Bulk Label</h2></div>
        </div>
        <div class="ms-auto d-lg-flex d-none flex-row"></div>
    </div>

    <!-- Table Card -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title">POD Bulk Label Data</h5></div>
        <div class="card-body">
            <!-- Controls -->
            <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                <div class="search-group-container">
                    <button class="search-options-button" id="searchOptionsButton">🔍</button>
                    <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                    <div class="search-options-menu" id="searchOptionsMenu">
                        <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                        <label><input type="radio" name="searchColumn" value="book_no"> Book No</label>
                        <label><input type="radio" name="searchColumn" value="customer"> Customer</label>
                        <label><input type="radio" name="searchColumn" value="origin"> Origin</label>
                        <label><input type="radio" name="searchColumn" value="destination"> Destination</label>
                    </div>
                    <button class="go-button" onclick="filterTable()">Go</button>
                </div>
                <div class="right-controls">
                    @include('layouts.actions-dropdown', ['downloadRoute' => route('bookings.download')])
                    <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    <button class="btn btn-secondary" onclick="printSelectedLabels()">Print Labels</button>
                </div>
            </div>

            <!-- Table -->
            <form method="POST" action="{{ route('label.bulk.pod') }}" id="bulkActionForm">
                @csrf
                <input type="hidden" name="action_type" id="actionType">

                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell checkbox-cell"><input type="checkbox" id="selectAll"></th>
                                <th class="header-cell sortable">Book No. <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Book Date <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Customer <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Product <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Service <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Item Content <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Origin <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Destination <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Weight (KG) <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Pieces <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Shipper Name <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Shipper Contact No. <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Shipper Address <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Consignee Name <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Consignee Contact No. <span class="sort-icon"></span></th>
                                <th class="header-cell sortable">Consignee Address <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr class="data-row">
                                    <td class="data-cell checkbox-cell">
                                        <input type="checkbox" name="booking_ids[]" class="row-checkbox" value="{{ $booking->id }}">
                                    </td>
                                    <td class="data-cell">{{ $booking->bookNo ?? '-' }}</td>
                                    <td class="data-cell">{{ $booking->bookDate ? \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y') : '-' }}</td>
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
                                <tr><td colspan="17" class="text-center text-muted py-4">No bookings found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- ✅ Dashboard modals include --}}
@include('layouts.dashboard-modals', [
    'columns' => [
        'book_no', 'book_date', 'customer', 'product', 'service',
        'item_content', 'origin', 'destination', 'weight', 'pieces',
        'shipper_name', 'shipper_contact', 'shipper_address',
        'consignee_name', 'consignee_contact', 'consignee_address'
    ],
    // 'chartModel' => 'Booking',
    // 'chartAction' => route('bookings.chart'),
    // 'filterAction' => route('bookings.index'),
    // 'rowFilterAction' => route('bookings.rowFilter'),
    // 'sortAction' => route('bookings.index'),
    // 'aggregateAction' => route('bookings.aggregate'),
    // 'computeAction' => route('bookings.compute'),
    'downloadAction' => route('bookings.download')
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
<style>
    /* Keep rows same height and vertically centered */
    .data-table td, .data-table th {
        vertical-align: middle;
        height: 45px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    .data-table td:nth-child(14),
    .data-table td:nth-child(17) {
        max-width: 250px;
    }
    .data-table th {
        white-space: nowrap;
        text-align: center;
    }
    .data-table tr {
        height: 45px !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
    // Select All functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Print selected labels
    function printSelectedLabels() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Please select at least one booking.');
            return;
        }

        const url = '{{ route("label.bulk.pod") }}';
        const token = document.querySelector('input[name="_token"]').value;

        const newForm = document.createElement('form');
        newForm.method = 'POST';
        newForm.action = url;
        newForm.target = '_blank';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = token;
        newForm.appendChild(csrfInput);

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'booking_ids[]';
            input.value = id;
            newForm.appendChild(input);
        });

        document.body.appendChild(newForm);
        newForm.submit();
        document.body.removeChild(newForm);
    }
</script>
@endpush
@endsection
