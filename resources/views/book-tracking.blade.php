@extends('layouts.master')

@section('title', 'Book Tracking')

@section('content')

@php
$bookings = $bookings ?? collect();
$tranzoCompany = $tranzoCompany ?? [];
@endphp

<div class="app-container">

    <!-- Header -->
    <div class="app-hero-header d-flex align-items-center">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Book Tracking</h2>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="app-body">

        <!-- SEARCH FORM -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Track Your Booking</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('booking.track') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Enter Book No</label>
                        <input
                            type="text"
                            name="book_no"
                            class="form-control"
                            value="{{ request('book_no') }}"
                            placeholder="Enter book numbers separated by comma"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="{{ route('booking.track') }}" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
        </div>

        {{-- BOOKING LIST --}}
        @if(!empty($bookings) && $bookings->count())

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Booking List</h5>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Book No</th>
                            <th>Customer</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Weight</th>
                            <th>Pieces</th>
                            <th>Ref No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        @php
                        $refNo = $thirdPartyBookings[$booking->bookNo] ?? '-';
                        $companyName = strtolower($tranzoCompany[$booking->bookNo] ?? '');
                        @endphp
                        <tr>
                            <td>{{ $booking->bookNo }}</td>
                            <td>{{ $booking->customer->customer_name ?? '-' }}</td>
                            <td>{{ $booking->origin ?? '-' }}</td>
                            <td>{{ $booking->destination ?? '-' }}</td>
                            <td>{{ $booking->weight ?? '-' }}</td>
                            <td>{{ $booking->pieces ?? '-' }}</td>
                            <td>{{ $refNo }}</td>
                            <td class="d-flex gap-1">

                                {{-- Existing View Button --}}
                                <button
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#detailsModal{{ $booking->id }}">
                                    View
                                </button>

                                {{-- Sonic Track Button --}}
                                @if($refNo !== '-' && $companyName === 'sonic')
                                <button
                                    class="btn btn-success btn-sm btn-sonic-track"
                                    data-ref="{{ $refNo }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#sonicModal{{ $booking->id }}">
                                    <i class="bi bi-truck me-1"></i>Sonic Track
                                </button>
                                @endif

                                {{-- Tranzo Track Button --}}
                                @if($refNo !== '-' && $companyName === 'tranzo')
                                <button
                                    class="btn btn-success btn-sm btn-tranzo-track"
                                    data-ref="{{ $refNo }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#tranzoModal{{ $booking->id }}">
                                    <i class="bi bi-truck me-1"></i>Tranzo Track
                                </button>
                                @endif

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @else
        @if(request('book_no'))
        <div class="alert alert-warning">No bookings found</div>
        @endif
        @endif

    </div>
</div>

{{-- ================= MODALS ================= --}}
@foreach($bookings as $booking)
@php
$refNo = $thirdPartyBookings[$booking->bookNo] ?? '-';
$companyName = strtolower($tranzoCompany[$booking->bookNo] ?? '');
@endphp

{{-- ============================================================ --}}
{{-- 1) EXISTING DETAILS MODAL (unchanged)                        --}}
{{-- ============================================================ --}}
<div class="modal fade" id="detailsModal{{ $booking->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Booking Details — {{ $booking->bookNo }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Book Info --}}
                <div class="row gx-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Book No</label>
                        <input type="text" class="form-control" value="{{ $booking->bookNo }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">3PL Tracking No</label>
                        <input type="text" class="form-control" value="{{ $refNo }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Book Date</label>
                        <input type="date" class="form-control" value="{{ $booking->bookDate }}" readonly>
                    </div>
                </div>

                {{-- Customer / Service --}}
                <div class="row gx-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Customer</label>
                        <input class="form-control" value="{{ $booking->customer->customer_name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Service</label>
                        <input class="form-control" value="{{ ucfirst($booking->service ?? '-') }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Channel</label>
                        <input class="form-control" value="{{ ucfirst($booking->bookChannel ?? '-') }}" readonly>
                    </div>
                </div>

                {{-- Location --}}
                <div class="row gx-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Payment</label>
                        <input class="form-control" value="{{ ucfirst($booking->paymentMode ?? '-') }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Origin</label>
                        <input class="form-control" value="{{ $booking->origin ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Destination</label>
                        <input class="form-control" value="{{ $booking->destination ?? '-' }}" readonly>
                    </div>
                </div>

                {{-- Shipment --}}
                <div class="row gx-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Postal Code</label>
                        <input class="form-control" value="{{ $booking->postalCode ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Invoice Value</label>
                        <input class="form-control" value="{{ $booking->invoiceValue ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Weight</label>
                        <input class="form-control" value="{{ $booking->weight ?? '-' }}" readonly>
                    </div>
                </div>

                <div class="row gx-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Pieces</label>
                        <input class="form-control" value="{{ $booking->pieces ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">COD Amount</label>
                        <input class="form-control" value="{{ $booking->codAmount ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Item Detail</label>
                        <input class="form-control" value="{{ $booking->itemDetail ?? '-' }}" readonly>
                    </div>
                </div>

                {{-- Tracking History --}}
                <hr>
                <h5>Tracking History</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Updated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->statuses as $status)
                        <tr>
                            <td>{{ $status->created_at->format('d-M-Y H:i') }}</td>
                            <td>{{ $status->status }}</td>
                            <td>{{ $status->description ?? '-' }}</td>
                            <td>{{ $status->user->name ?? 'System' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No tracking updates yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- 2) SONIC TRACK MODAL                                         --}}
{{-- ============================================================ --}}
@if($refNo !== '-' && $companyName === 'sonic')
<div class="modal fade" id="sonicModal{{ $booking->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-truck me-2"></i>Sonic Track Data — {{ $booking->bookNo }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Loading Spinner --}}
                <div class="sonic-loading text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2 text-muted">Fetching tracking data...</p>
                </div>

                {{-- Error Message --}}
                <div class="sonic-error alert alert-danger d-none"></div>

                {{-- Sonic Data Content --}}
                <div class="sonic-content d-none">

                    {{-- Shipment Info --}}
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-box-seam me-1"></i> Shipment Info</h6>
                    <div class="row gx-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Tracking Number</label>
                            <input class="form-control" id="sTrackNo{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order ID</label>
                            <input class="form-control" id="sOrderId{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order Date</label>
                            <input class="form-control" id="sOrderDate{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Shipping Mode</label>
                            <input class="form-control" id="sShipMode{{ $booking->id }}" readonly>
                        </div>
                    </div>

                    <div class="row gx-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Weight</label>
                            <input class="form-control" id="sWeight{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount</label>
                            <input class="form-control" id="sAmount{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Instructions</label>
                            <input class="form-control" id="sInstructions{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sub Segment</label>
                            <input class="form-control" id="sSubSegment{{ $booking->id }}" readonly>
                        </div>
                    </div>

                    <hr>

                    {{-- Shipper & Consignee --}}
                    <div class="row gx-4 mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-fill me-1"></i> Shipper</h6>
                            <div class="row gx-3">
                                <div class="col-6 mb-2">
                                    <label class="form-label">Name</label>
                                    <input class="form-control" id="sShipperName{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">City</label>
                                    <input class="form-control" id="sShipperCity{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Phone</label>
                                    <input class="form-control" id="sShipperPhone{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" id="sShipperEmail{{ $booking->id }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-person-lines-fill me-1"></i> Consignee</h6>
                            <div class="row gx-3">
                                <div class="col-6 mb-2">
                                    <label class="form-label">Name</label>
                                    <input class="form-control" id="sConsigneeName{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Destination</label>
                                    <input class="form-control" id="sConsigneeDest{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Phone</label>
                                    <input class="form-control" id="sConsigneePhone{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Address</label>
                                    <input class="form-control" id="sConsigneeAddr{{ $booking->id }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Items Table --}}
                    <h6 class="fw-bold mb-3"><i class="bi bi-list-ul me-1"></i> Order Items</h6>
                    <table class="table table-bordered table-sm mb-4">
                        <thead class="table-secondary">
                            <tr>
                                <th>Order ID</th>
                                <th>Product Type</th>
                                <th>Description</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="sItemsBody{{ $booking->id }}">
                        </tbody>
                    </table>

                    <hr>

                    {{-- Tracking History --}}
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i> Tracking History</h6>
                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Date / Time</th>
                                <th>Status</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody id="sHistoryBody{{ $booking->id }}">
                        </tbody>
                    </table>

                </div>{{-- end sonic-content --}}

            </div>
        </div>
    </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- 3) TRANZO TRACK MODAL                                        --}}
{{-- ============================================================ --}}
@if($refNo !== '-' && $companyName === 'tranzo')
<div class="modal fade" id="tranzoModal{{ $booking->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-truck me-2"></i>Tranzo Track Data — {{ $booking->bookNo }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Loading --}}
                <div class="tranzo-loading text-center py-4">
                    <div class="spinner-border text-white" role="status"></div>
                    <p class="mt-2 text-white">Fetching Tranzo tracking data...</p>
                </div>

                {{-- Error --}}
                <div class="tranzo-error alert alert-danger d-none"></div>

                {{-- Content --}}
                <div class="tranzo-content d-none">

                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-box-seam me-1"></i> Shipment Info</h6>
                    <div class="row gx-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Tracking Number</label>
                            <input class="form-control" id="tTrackNo{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Number</label>
                            <input class="form-control" id="tRefNo{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order Status</label>
                            <input class="form-control" id="tOrderStatus{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Shipment Type</label>
                            <input class="form-control" id="tShipType{{ $booking->id }}" readonly>
                        </div>
                    </div>

                    <div class="row gx-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Booking Amount</label>
                            <input class="form-control" id="tAmount{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Booking Weight</label>
                            <input class="form-control" id="tWeight{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Items</label>
                            <input class="form-control" id="tTotalItems{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order Details</label>
                            <input class="form-control" id="tOrderDetails{{ $booking->id }}" readonly>
                        </div>
                    </div>

                    <hr>

                    <div class="row gx-4 mb-4">

                        {{-- Customer / Consignee --}}
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-fill me-1"></i> Customer / Consignee</h6>
                            <div class="row gx-3">
                                <div class="col-6 mb-2">
                                    <label class="form-label">Name</label>
                                    <input class="form-control" id="tCustName{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Phone</label>
                                    <input class="form-control" id="tCustPhone{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" id="tCustEmail{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Destination City</label>
                                    <input class="form-control" id="tDestCity{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-12 mb-2">
                                    <label class="form-label">Delivery Address</label>
                                    <input class="form-control" id="tDeliveryAddr{{ $booking->id }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Merchant / Pickup --}}
                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-shop me-1"></i> Merchant / Pickup</h6>
                            <div class="row gx-3">
                                <div class="col-6 mb-2">
                                    <label class="form-label">Merchant Store</label>
                                    <input class="form-control" id="tMerchant{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Pickup Location</label>
                                    <input class="form-control" id="tPickup{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Return Location</label>
                                    <input class="form-control" id="tReturn{{ $booking->id }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Special Instructions</label>
                                    <input class="form-control" id="tInstructions{{ $booking->id }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Fees --}}
                    <h6 class="fw-bold mb-3"><i class="bi bi-cash-stack me-1"></i> Fees & Flags</h6>
                    <div class="row gx-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Delivery Fee</label>
                            <input class="form-control" id="tDeliveryFee{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Delivery Tax</label>
                            <input class="form-control" id="tDeliveryTax{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fuel Fee</label>
                            <input class="form-control" id="tFuelFee{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cash Handling Fee</label>
                            <input class="form-control" id="tCashFee{{ $booking->id }}" readonly>
                        </div>
                    </div>

                    <div class="row gx-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Return Initiated</label>
                            <input class="form-control" id="tReturnInit{{ $booking->id }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Advice Initiated</label>
                            <input class="form-control" id="tAdviceInit{{ $booking->id }}" readonly>
                        </div>
                    </div>

                </div>{{-- end tranzo-content --}}

            </div>
        </div>
    </div>
</div>
@endif

@endforeach

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Track which modals already loaded data (avoid duplicate API calls)
        const loadedModals = {};

        // ============================================================
        // SONIC MODAL LOGIC
        // ============================================================
        document.querySelectorAll('[id^="sonicModal"]').forEach(function(modalEl) {

            modalEl.addEventListener('show.bs.modal', function() {

                const bookingId = modalEl.id.replace('sonicModal', '');

                if (loadedModals['sonic_' + bookingId]) return;

                const btn = document.querySelector(`[data-bs-target="#sonicModal${bookingId}"]`);
                const trackingNumber = btn ? btn.getAttribute('data-ref') : null;

                if (!trackingNumber) return;

                const loadingEl = modalEl.querySelector('.sonic-loading');
                const errorEl = modalEl.querySelector('.sonic-error');
                const contentEl = modalEl.querySelector('.sonic-content');

                loadingEl.classList.remove('d-none');
                errorEl.classList.add('d-none');
                contentEl.classList.add('d-none');

                fetch(`{{ route('booking.sonic.track') }}?tracking_number=${trackingNumber}`)
                    .then(res => res.json())
                    .then(data => {

                        loadingEl.classList.add('d-none');

                        if (data.error) {
                            errorEl.textContent = data.error;
                            errorEl.classList.remove('d-none');
                            return;
                        }

                        const d = data.details;

                        setVal(`sTrackNo${bookingId}`, d.tracking_number);
                        setVal(`sOrderId${bookingId}`, d.order_id);
                        setVal(`sOrderDate${bookingId}`, d.order_date);
                        setVal(`sShipMode${bookingId}`, d.order_information?.shipping_mode ?? '-');
                        setVal(`sWeight${bookingId}`, d.order_information?.weight ?? '-');
                        setVal(`sAmount${bookingId}`, d.order_information?.amount ?? '-');
                        setVal(`sInstructions${bookingId}`, d.order_information?.instructions ?? '-');
                        setVal(`sSubSegment${bookingId}`, d.order_information?.sub_segment ?? '-');

                        setVal(`sShipperName${bookingId}`, d.shipper?.name ?? '-');
                        setVal(`sShipperCity${bookingId}`, d.shipper?.city ?? '-');
                        setVal(`sShipperPhone${bookingId}`, d.shipper?.phone_number_1 ?? '-');
                        setVal(`sShipperEmail${bookingId}`, d.shipper?.email ?? '-');

                        setVal(`sConsigneeName${bookingId}`, d.consignee?.name ?? '-');
                        setVal(`sConsigneeDest${bookingId}`, d.consignee?.destination ?? '-');
                        setVal(`sConsigneePhone${bookingId}`, d.consignee?.phone_number_1 ?? '-');
                        setVal(`sConsigneeAddr${bookingId}`, d.consignee?.address ?? '-');

                        const itemsBody = document.getElementById(`sItemsBody${bookingId}`);
                        itemsBody.innerHTML = '';
                        const items = d.order_information?.items ?? [];
                        if (items.length) {
                            items.forEach(item => {
                                itemsBody.innerHTML += `
                                <tr>
                                    <td>${item.order_id ?? '-'}</td>
                                    <td>${item.product_type ?? '-'}</td>
                                    <td>${item.description ?? '-'}</td>
                                    <td>${item.quantity ?? '-'}</td>
                                </tr>`;
                            });
                        } else {
                            itemsBody.innerHTML = '<tr><td colspan="4" class="text-center">No items</td></tr>';
                        }

                        const histBody = document.getElementById(`sHistoryBody${bookingId}`);
                        histBody.innerHTML = '';
                        const history = d.tracking_history ?? [];
                        if (history.length) {
                            history.forEach(h => {
                                histBody.innerHTML += `
                                <tr>
                                    <td>${h.date_time ?? '-'}</td>
                                    <td>${h.status ?? '-'}</td>
                                    <td>${h.status_reason ?? '-'}</td>
                                </tr>`;
                            });
                        } else {
                            histBody.innerHTML = '<tr><td colspan="3" class="text-center">No history</td></tr>';
                        }

                        contentEl.classList.remove('d-none');
                        loadedModals['sonic_' + bookingId] = true;
                    })
                    .catch(err => {
                        loadingEl.classList.add('d-none');
                        errorEl.textContent = 'Something went wrong. Please try again.';
                        errorEl.classList.remove('d-none');
                        console.error(err);
                    });
            });
        });

        // ============================================================
        // TRANZO MODAL LOGIC
        // ============================================================
        document.querySelectorAll('[id^="tranzoModal"]').forEach(function(modalEl) {

            modalEl.addEventListener('show.bs.modal', function() {

                const bookingId = modalEl.id.replace('tranzoModal', '');

                if (loadedModals['tranzo_' + bookingId]) return;

                const btn = document.querySelector(`[data-bs-target="#tranzoModal${bookingId}"]`);
                const trackingNumber = btn ? btn.getAttribute('data-ref') : null;

                if (!trackingNumber) return;

                const loadingEl = modalEl.querySelector('.tranzo-loading');
                const errorEl = modalEl.querySelector('.tranzo-error');
                const contentEl = modalEl.querySelector('.tranzo-content');

                loadingEl.classList.remove('d-none');
                errorEl.classList.add('d-none');
                contentEl.classList.add('d-none');

                fetch(`{{ route('booking.tranzo.track') }}?tracking_number=${trackingNumber}`)
                    .then(res => res.json())
                    .then(data => {

                        loadingEl.classList.add('d-none');

                        if (data.error) {
                            errorEl.textContent = data.error;
                            errorEl.classList.remove('d-none');
                            return;
                        }

                        const d = data.details;

                        // Shipment Info
                        setVal(`tTrackNo${bookingId}`, d.tracking_number ?? '-');
                        setVal(`tRefNo${bookingId}`, d.reference_number ?? '-');
                        setVal(`tOrderStatus${bookingId}`, d.order_status ?? '-');
                        setVal(`tShipType${bookingId}`, d.ds_shipment_type ?? '-');
                        setVal(`tAmount${bookingId}`, d.booking_amount ?? '-');
                        setVal(`tWeight${bookingId}`, d.booking_weight ?? '-');
                        setVal(`tTotalItems${bookingId}`, d.total_items ?? '-');
                        setVal(`tOrderDetails${bookingId}`, d.order_details ?? '-');

                        // Customer
                        setVal(`tCustName${bookingId}`, d.customer_name ?? '-');
                        setVal(`tCustPhone${bookingId}`, d.customer_phone ?? '-');
                        setVal(`tCustEmail${bookingId}`, d.customer_email ?? '-');
                        setVal(`tDestCity${bookingId}`, d.destination_city ?? '-');
                        setVal(`tDeliveryAddr${bookingId}`, d.delivery_address ?? '-');

                        // Merchant / Pickup
                        setVal(`tMerchant${bookingId}`, d.merchant_store ?? '-');
                        setVal(`tPickup${bookingId}`, d.pickup_location ?? '-');
                        setVal(`tReturn${bookingId}`, d.return_location ?? '-');
                        setVal(`tInstructions${bookingId}`, d.special_instructions || '-');

                        // Fees
                        setVal(`tDeliveryFee${bookingId}`, d.delivery_fee ?? '-');
                        setVal(`tDeliveryTax${bookingId}`, d.delivery_tax ?? '-');
                        setVal(`tFuelFee${bookingId}`, d.delivery_fuel_fee ?? '-');
                        setVal(`tCashFee${bookingId}`, d.cash_handling_fee ?? '-');
                        setVal(`tReturnInit${bookingId}`, d.return_initiated ?? '-');
                        setVal(`tAdviceInit${bookingId}`, d.advice_initiated ?? '-');

                        contentEl.classList.remove('d-none');
                        loadedModals['tranzo_' + bookingId] = true;
                    })
                    .catch(err => {
                        loadingEl.classList.add('d-none');
                        errorEl.textContent = 'Something went wrong. Please try again.';
                        errorEl.classList.remove('d-none');
                        console.error(err);
                    });
            });
        });

        // Shared Helper
        function setVal(id, value) {
            const el = document.getElementById(id);
            if (el) el.value = value ?? '-';
        }

    });
</script>
@endpush

<style>
    button[aria-expanded="true"] .transition-arrow {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }

    button .transition-arrow {
        transition: transform 0.3s ease;
    }
</style>