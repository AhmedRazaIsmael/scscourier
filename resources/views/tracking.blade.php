@extends('layouts.master')

@section('title') {{'Tracking'}} @endsection

@section('content')
<div class="page-content">
    <div class="page-container">

        {{-- Page Header --}}
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 text-uppercase fw-bold mb-0">Book Track</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Express</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Booking</a></li>
                    <li class="breadcrumb-item active">Book Track</li>
                </ol>
            </div>
        </div>

        {{-- Search Form --}}
        <form action="{{ route('booking.track') }}" method="get" class="mb-4">
            <div class="row">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed">
                            <h4 class="card-title">Book Track</h4>
                            @if(session('error'))
                                <p class="text-danger">{{ session('error') }}</p>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="bookNo" class="form-label">Book No.</label>
                                <input type="text" class="form-control" id="bookNo" name="bookNo"
                                       placeholder="Enter book no" value="{{ request('bookNo') }}" required>
                            </div>
                        </div>
                        <div class="card-footer border-top border-dashed text-end">
                            <button type="submit" class="btn btn-primary">Track</button>
                            <button type="reset" class="btn btn-danger">Reset</button>
                        </div>
                    </div>
                </div>

                {{-- Current Status --}}
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed">
                            <h4 class="card-title">Current Status</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">
                                @if(isset($booking) && $booking->statuses->count() > 0)
                                    <b>{{ $booking->statuses->last()->status }}</b>
                                @else
                                    <b>Pending</b>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if(isset($booking))
        {{-- Booking Details --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="card-title">Booking Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <!-- Book No -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Book No.</label>
                                    <input type="text" class="form-control" value="{{ $booking->bookNo }}" readonly>
                                </div>
                            </div>

                            <!-- Book Date -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Book Date</label>
                                    <input type="text" class="form-control" value="{{ $booking->bookDate }}" readonly>
                                </div>
                            </div>

                            <!-- Customer -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Customer</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $booking->customer->customer_name ?? 'N/A' }}" readonly>
                                </div>
                            </div>

                            <!-- Service -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Service</label>
                                    <input type="text" class="form-control" value="{{ $booking->service }}" readonly>
                                </div>
                            </div>

                            <!-- Book Channel -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Book Channel</label>
                                    <input type="text" class="form-control" value="{{ $booking->bookChannel }}" readonly>
                                </div>
                            </div>

                            <!-- Payment Mode -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Payment Mode</label>
                                    <input type="text" class="form-control" value="{{ $booking->paymentMode }}" readonly>
                                </div>
                            </div>

                            <!-- Origin / Destination -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Origin</label>
                                    <input type="text" class="form-control" value="{{ $booking->origin }}" readonly>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Destination</label>
                                    <input type="text" class="form-control" value="{{ $booking->destination }}" readonly>
                                </div>
                            </div>

                            <!-- Invoice Value -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Invoice Value</label>
                                    <input type="text" class="form-control" value="{{ $booking->invoiceValue }}" readonly>
                                </div>
                            </div>

                            <!-- Weight -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Weight (KG)</label>
                                    <input type="text" class="form-control" value="{{ $booking->weight }}" readonly>
                                </div>
                            </div>

                            <!-- Pieces -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Pieces</label>
                                    <input type="text" class="form-control" value="{{ $booking->pieces }}" readonly>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" rows="2" readonly>{{ $booking->remarks }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tracking History --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="header-title">Book Tracking Details</h4>
                    </div>
                    <div class="card-body">
                        <div id="table-gridjs"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
    <x-footer></x-footer>
</div>
@endsection

@section('script')
@if(isset($booking))
<script>
    let statuses = @json($booking->statuses);

    let gridData = statuses.map(s => [
        s.created_at,
        s.status,
        s.description ?? "-",
        s.user ? s.user.name : "System",
        s.updated_at,
    ]);

    new gridjs.Grid({
        columns: [
            { name: "Status Date/Time", width: "200px" },
            { name: "Status", width: "150px" },
            { name: "Status Description", width: "200px" },
            { name: "Updated By", width: "150px" },
            { name: "Updated Date/Time", width: "200px" },
        ],
        sort: true,
        search: false,
        data: gridData
    }).render(document.getElementById("table-gridjs"));
</script>
@endif
@endsection
