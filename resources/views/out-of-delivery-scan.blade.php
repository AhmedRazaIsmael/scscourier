@extends('layouts.master')

@section('title', isset($type) ? ucfirst($type) . ' Scan' : 'Scan')

@section('content')
<div class="app-container">

    <!-- Hero Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">{{ isset($type) ? ucfirst($type) : 'Scan' }} Scan</h2>
            </div>
        </div>
    </div>

    <div class="app-body">

        {{-- =================== FORM =================== --}}
        <div class="row gx-4 mb-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Scan {{ ucfirst($type) }}</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('scan.form', ['type' => $type]) }}" method="GET">
                            <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label for="bookNo" class="form-label">Enter Book No</label>
                                    <input type="text" name="bookNo" class="form-control" id="bookNo"
                                           value="{{ request('bookNo') }}" placeholder="Enter Book No" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary"
                                    onclick="window.location='{{ route('scan.form', ['type' => $type]) }}'">
                                Reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== SCAN TABLE =================== --}}
        @if(request()->filled('bookNo') && $scans->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">{{ ucfirst($type) }} Scan Data</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Book No</th>
                            <th>Scan Date/Time</th>
                            <th>Hub</th>
                            <th>Scanned By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scans as $scan)
                            <tr>
                                <td>{{ $scan->book_no }}</td>
                                <td>{{ $scan->created_at->format('d-M-Y H:i:s') }}</td>
                                <td>{{ $scan->hub->name ?? $scan->booking->destinationHub->code ?? '-' }}</td>
                                <td>{{ $scan->user ? strtoupper($scan->user->username) : '-' }}</td>
                                <td>{{ $scan->latestBookingStatus->status ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @elseif(request()->filled('bookNo'))
            <div class="alert alert-warning mt-3">
                No record found for Book No: <strong>{{ request('bookNo') }}</strong>
            </div>
        @endif

    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
@endpush

@endsection
