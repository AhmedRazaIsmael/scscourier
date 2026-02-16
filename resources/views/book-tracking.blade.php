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

                            <td>
                                <button
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#detailsModal{{ $booking->id }}">
                                    View
                                </button>
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
@endphp

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

@endforeach

@endsection
  <style>
            /* Rotate arrow when button is expanded */
            button[aria-expanded="true"] .transition-arrow {
                transform: rotate(180deg);
                transition: transform 0.3s ease;
            }

            button .transition-arrow {
                transition: transform 0.3s ease;
            }
        </style>