@extends('layouts.master')

@section('title', 'Edit Booking Status')

@section('content')
<div class="page-content">
    <div class="page-container">

        <h4>Edit Status for Booking: {{ $booking->bookNo }}</h4>

        @if(session('success'))
            <p class="alert alert-success">{{ session('success') }}</p>
        @endif

        <!-- Status Update Form -->
        <form action="{{ route('booking.status.update', $booking->id) }}" method="post">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option value="">--SELECT--</option>
                        <option value="Shipment Booked">Shipment Booked</option>
                        <option value="Arrived at BOX Facility">Arrived at BOX Facility</option>
                        <option value="In Transit">In Transit</option>
                        <option value="Out For Delivery">Out For Delivery</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Return to Shipper">Return to Shipper</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Enter description">
                </div>
            </div>

            <button class="btn btn-primary">Update Status</button>
        </form>

        <hr>

        <!-- Status History -->
        <h5>Status History</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Updated By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->statuses as $status)
                <tr>
                    <td>{{ $status->created_at }}</td>
                    <td>{{ $status->status }}</td>
                    <td>{{ $status->description }}</td>
                    <td>{{ $status->user->name ?? 'System' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection
