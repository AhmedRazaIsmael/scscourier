@extends('layouts.master')

@section('title', 'Book Tracking')

@section('content')

@php
$bookings = $bookings ?? collect();
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

                                {{-- 🔹 NEW: Track Data Button — only if ref_no exists --}}
                                @if($refNo !== '-')
                                <button
                                    class="btn btn-success btn-sm btn-sonic-track"
                                    data-ref="{{ $refNo }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#sonicModal{{ $booking->id }}">
                                    Track Data
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

{{-- ================= EXISTING MODALS ================= --}}
@foreach($bookings as $booking)
@php
$refNo = $thirdPartyBookings[$booking->bookNo] ?? '-';
@endphp

{{-- ---- Existing Details Modal (unchanged) ---- --}}
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
                    <div class="col-md-3">
                        <label class="form-label">Pieces</label>
                        <input class="form-control" value="{{ $booking->pieces ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Width</label>
                        <input class="form-control" value="{{ $booking->width ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">COD Amount</label>
                        <input class="form-control" value="{{ $booking->codAmount ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
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

{{-- ---- 🔹 NEW: Sonic Track Data Modal ---- --}}
@if($refNo !== '-')
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

@endforeach

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 🔹 Track which modals already loaded data (avoid duplicate API calls)
        const loadedModals = {};

        // 🔹 Listen for Sonic modal open
        document.querySelectorAll('[id^="sonicModal"]').forEach(function(modalEl) {

            modalEl.addEventListener('show.bs.modal', function() {

                const bookingId = modalEl.id.replace('sonicModal', '');

                // Already loaded? Skip
                if (loadedModals[bookingId]) return;

                const btn = document.querySelector(`[data-bs-target="#sonicModal${bookingId}"]`);
                const trackingNumber = btn ? btn.getAttribute('data-ref') : null;

                if (!trackingNumber) return;

                const loadingEl = modalEl.querySelector('.sonic-loading');
                const errorEl = modalEl.querySelector('.sonic-error');
                const contentEl = modalEl.querySelector('.sonic-content');

                // Show loading
                loadingEl.classList.remove('d-none');
                errorEl.classList.add('d-none');
                contentEl.classList.add('d-none');

                // 🔹 Call our Laravel route (which calls Sonic API securely)
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

                        // ── Shipment Info ──
                        setVal(`sTrackNo${bookingId}`, d.tracking_number);
                        setVal(`sOrderId${bookingId}`, d.order_id);
                        setVal(`sOrderDate${bookingId}`, d.order_date);
                        setVal(`sShipMode${bookingId}`, d.order_information?.shipping_mode ?? '-');
                        setVal(`sWeight${bookingId}`, d.order_information?.weight ?? '-');
                        setVal(`sAmount${bookingId}`, d.order_information?.amount ?? '-');
                        setVal(`sInstructions${bookingId}`, d.order_information?.instructions ?? '-');
                        setVal(`sSubSegment${bookingId}`, d.order_information?.sub_segment ?? '-');

                        // ── Shipper ──
                        setVal(`sShipperName${bookingId}`, d.shipper?.name ?? '-');
                        setVal(`sShipperCity${bookingId}`, d.shipper?.city ?? '-');
                        setVal(`sShipperPhone${bookingId}`, d.shipper?.phone_number_1 ?? '-');
                        setVal(`sShipperEmail${bookingId}`, d.shipper?.email ?? '-');

                        // ── Consignee ──
                        setVal(`sConsigneeName${bookingId}`, d.consignee?.name ?? '-');
                        setVal(`sConsigneeDest${bookingId}`, d.consignee?.destination ?? '-');
                        setVal(`sConsigneePhone${bookingId}`, d.consignee?.phone_number_1 ?? '-');
                        setVal(`sConsigneeAddr${bookingId}`, d.consignee?.address ?? '-');

                        // ── Items ──
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

                        // ── Tracking History ──
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
                        loadedModals[bookingId] = true; // Mark as loaded
                    })
                    .catch(err => {
                        loadingEl.classList.add('d-none');
                        errorEl.textContent = 'Something went wrong. Please try again.';
                        errorEl.classList.remove('d-none');
                        console.error(err);
                    });
            });
        });

        // Helper
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