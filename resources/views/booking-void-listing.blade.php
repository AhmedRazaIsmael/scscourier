@extends('layouts.master')

@section('title', 'Booking Void')

@section('content')
@php
    $bookingColumns = [
        'bookNo' => 'Book No.',
        'bookDate' => 'Book Date',
        'shipperCompany' => 'Company',
        'customer' => 'Customer',
        'product' => 'Product',
        'service' => 'Service',
        'itemContent' => 'Item Content',
        'paymentMode' => 'Payment Mode',
        'origin' => 'Origin',
        'destination' => 'Destination',
        'weight' => 'Weight (KG)',
        'pieces' => 'Pieces',
        'orderNo' => 'Order No.',
        'shipperName' => 'Shipper Name',
        'shipperNumber' => 'Shipper Contact No.',
        'shipperAddress' => 'Shipper Address',
        'consigneeName' => 'Consignee Name',
        'consigneeNumber' => 'Consignee Contact No.',
        'consigneeAddress' => 'Consignee Address',
        'codAmount' => 'COD Amount',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Booking Void</h2>
            </div>
        </div>
    </div>

    <div class="app-body">

        {{-- =================== FLASH MESSAGE =================== --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- =================== DATA TABLE =================== --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Booking Void Data</h5>
            </div>
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
                        @include('layouts.actions-dropdown', ['downloadRoute' => route('bookings.download'), 'showChart' => true])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Main Bookings Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                @foreach($bookingColumns as $key => $label)
                                    <th class="header-cell sortable" data-column="{{ $key }}">
                                        {{ $label }} <span class="sort-icon"></span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="bookingsTableBody">
                            @forelse($bookings as $b)
                                <tr class="data-row">
                                    @foreach($bookingColumns as $key => $label)
                                        <td class="data-cell" data-column="{{ $key }}">
                                            @if($key=='bookNo')
                                                <a href="javascript:void(0);" class="text-primary fw-bold" 
                                                   data-bs-toggle="modal" data-bs-target="#voidBookingModal"
                                                   data-booking-id="{{ $b->id }}"
                                                   data-book-no="{{ $b->bookNo }}">
                                                   {{ $b->bookNo }}
                                                </a>
                                            @elseif($key=='bookDate')
                                                {{ \Carbon\Carbon::parse($b->bookDate)->format('d-m-Y') }}
                                            @elseif($key=='customer')
                                                {{ $b->customer->customer_name ?? '-' }}
                                            @else
                                                {{ $b->$key ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($bookingColumns) }}" class="text-center text-muted py-3">No bookings found</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== VOID BOOKINGS TABLE =================== --}}
        <div class="card mt-5">
            <div class="card-header">
                <h5 class="card-title">VOID Bookings</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Book No</th>
                            <th>Customer</th>
                            <th>Remarks</th>
                            <th>Marked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\VoidBooking::latest()->get() as $voidBooking)
                            <tr>
                                <td>{{ $voidBooking->booking->bookNo ?? '-' }}</td>
                                <td>{{ $voidBooking->booking->customer->customer_name ?? '-' }}</td>
                                <td>{{ $voidBooking->remarks }}</td>
                                <td>{{ $voidBooking->created_at->format('d-m-Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- VOID Booking Modal -->
<div class="modal fade" id="voidBookingModal" tabindex="-1" aria-labelledby="voidBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="voidBookingForm" method="POST" action="{{ route('booking.void.submit') }}">
            @csrf
            <input type="hidden" name="booking_id" id="modalBookingId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="voidBookingModalLabel">Booking Void</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" id="status" class="form-control" value="VOID" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="voidRemarks" class="form-label">Remarks</label>
                        <textarea name="void_remarks" id="voidRemarks" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Mark as VOID</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('layouts.dashboard-modals', [
    'columns' => $bookingColumns,
    'chartModel' => 'BookingVoid',
    'downloadAction' => route('booking.download')
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>

<script>
    // Only populate hidden booking_id
    var voidModal = document.getElementById('voidBookingModal');
    voidModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; 
        var bookingId = button.getAttribute('data-booking-id');

        // Set hidden input
        document.getElementById('modalBookingId').value = bookingId;
    });

    // Auto-hide success flash message
    document.addEventListener("DOMContentLoaded", function() {
        let alert = document.querySelector('.alert-success');
        if(alert) {
            setTimeout(() => {
                alert.classList.remove('show');
                alert.classList.add('hide');
            }, 4000);
        }
    });
</script>

@endpush
@endsection
