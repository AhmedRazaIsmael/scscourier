@extends('layouts.master')
@section('title', 'Assigning Counter Partner')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $bookingColumns = [
        'bookNo' => 'Book No',
        'company' => 'Company',
        'customer' => 'Customer',
        'product' => 'Product',
        'service' => 'Service',
        'itemContent' => 'Item Content',
        'originCountry' => 'Origin Country',
        'origin' => 'Origin',
        'destinationCountry' => 'Destination Country',
        'destination' => 'Destination',
        'weight' => 'Weight (KG)',
        'pieces' => 'Pieces',
        'orderNo' => 'Order No',
        'shipperCompany' => 'Shipper Company Name',
        'shipperName' => 'Shipper Name',
        'shipperNumber' => 'Shipper Contact No',
        'shipperAddress' => 'Shipper Address',
        'consigneeCompany' => 'Consignee Company Name',
        'consigneeName' => 'Consignee Name',
        'consigneeNumber' => 'Consignee Contact No',
        'consigneeAddress' => 'Consignee Address',
    ];
@endphp
<div class="app-container">
    <!-- Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-people fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Assigning Counter Partner</h2></div>
        </div>
    </div>

    <div class="app-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Table Card -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Assign Counter Partner Data</h5></div>
            <div class="card-body">

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
                            'downloadRoute' => route('booking.download'),
                            'showChart' => true
                        ])
                        <button class="reset-button btn btn-outline-secondary" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Table -->
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
                                <tr class="data-row">
                                    @foreach($bookingColumns as $key => $label)
                                        @php
                                            $value = $booking->$key ?? '-';
                                        @endphp
                                        <td class="data-cell @if($key=='bookNo') text-primary fw-bold @endif" data-column="{{ $key }}"
                                            @if($key == 'bookNo')
                                                data-bs-toggle="modal" data-bs-target="#assignPartnerModal{{ $booking->id }}"
                                            @endif
                                        >
                                            @if($key == 'company')
                                                Airborn courier express
                                            @elseif($key == 'customer')
                                                {{ optional($booking->customer)->customer_name ?? '-' }}
                                            @elseif($key == 'product')
                                                {{ $booking->product ?? optional($booking->customer)->product ?? '-' }}
                                            @elseif($key == 'bookDate')
                                                {{ \Carbon\Carbon::parse($booking->bookDate)->format('d/m/Y') }}
                                            @elseif(in_array($key, ['shipperCompany','shipperName','shipperNumber','shipperAddress','consigneeCompany','consigneeName','consigneeNumber','consigneeAddress']))
                                                {{ $booking->$key ?? '-' }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
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

<!-- Assign Partner Modals -->
@foreach($bookings as $booking)
<div class="modal fade" id="assignPartnerModal{{ $booking->id }}" tabindex="-1" aria-labelledby="assignPartnerLabel{{ $booking->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('assign.counter.partner') }}" method="POST">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignPartnerLabel{{ $booking->id }}">Counter Partner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Booking Number</label>
                        <input type="text" class="form-control" value="{{ $booking->bookNo }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Counter Partner</label>
                        <select name="partner_id" class="form-select partner-select" required>
                            <option value="">-- Select Partner --</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign Date</label>
                        <input type="date" name="assign_date" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email To</label>
                        <input type="text" name="email_to" class="form-control email-to" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email CC (optional)</label>
                        <input type="text" name="email_cc" class="form-control email-cc">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Assign</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@include('layouts.dashboard-modals', [
    'columns' => ['book_no','book_date','company_name','ref_no','remarks','customer','shipper','consignee','updated_by','updated_at'],
    'chartModel' => 'ThirdPartyBooking',
    // 'chartAction' => route('thirdparty.chart'),
    // 'filterAction' => route('thirdparty.index'),
    // 'rowFilterAction' => route('thirdparty.rowFilter'),
    // 'sortAction' => route('thirdparty.index'),
    // 'aggregateAction' => route('thirdparty.aggregate'),
    // 'computeAction' => route('thirdparty.compute'),
    'downloadAction' => route('booking.download')
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
        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toUpperCase().includes(filter) ? '' : 'none';
        });
    }
    function clearSearchAndReset() {
        document.getElementById('searchInput').value = '';
        filterTable();
    }

    // Auto-fill emails based on partner
    document.querySelectorAll('.partner-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const modal = this.closest('.modal');
            const emailToInput = modal.querySelector('.email-to');
            const emailCcInput = modal.querySelector('.email-cc');
            const selectedText = this.options[this.selectedIndex].text.trim();

            emailToInput.value = '';
            emailCcInput.value = '';

            if (selectedText.includes('SDJ Intl Logistics')) {
                emailToInput.value = 'maxie66778@yahoo.com';
                emailCcInput.value = 'carecrew@blueoceanxpress.com';
            } else if (selectedText.includes('ESLogistics')) {
                emailToInput.value = 'shirley@eslogistics.ltd;ricky@eslogistics.ltd';
                emailCcInput.value = 'ricky@eslogistics.ltd';
            }
        });
    });
</script>
@endpush

@endsection
