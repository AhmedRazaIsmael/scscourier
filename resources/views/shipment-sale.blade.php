@extends('layouts.master')
@section('title', 'All Bookings')

@section('content')
<div class="app-container">
    <div class="app-body">
        <div class="card mb-4">
            <div class="card-body">
                {{-- Table Controls --}}
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="track_no">Track No</label>
                            <label><input type="radio" name="searchColumn" value="book_date">Booking Date</label>
                            <label><input type="radio" name="searchColumn" value="customer">Customer</label>
                            <label><input type="radio" name="searchColumn" value="product">Product</label>
                            <label><input type="radio" name="searchColumn" value="origin">Origin</label>
                            <label><input type="radio" name="searchColumn" value="destination">Destination</label>
                            <label><input type="radio" name="searchColumn" value="wtt">Wtt</label>
                            <label><input type="radio" name="searchColumn" value="pcs">Pcs</label>
                            <label><input type="radio" name="searchColumn" value="sales_person">Sales Person</label>
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', [
                            'downloadRoute' => route('booking.download')
                        ])
                        <button class="reset-button ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                {{-- Data Table --}}
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="track_no">Track No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="book_date">Booking Date <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="customer">Customer <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="product">Product <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="origin">Origin <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="destination">Destination <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="wtt">Wtt <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="pcs">Pcs <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="sales_person">Sales Person <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                            <tr class="data-row">
                                <td class="data-cell" data-column="track_no">
                                    <a href="{{ route('invoice.createFromBooking', $booking->track_no) }}" class="text-primary text-decoration-underline">
                                        {{ $booking->track_no }}
                                    </a>
                                </td>
                                <td class="data-cell" data-column="book_date">{{ \Carbon\Carbon::parse($booking->bookDate)->format('d-m-Y') }}</td>
                                <td class="data-cell" data-column="customer">{{ $booking->customer ?? '-' }}</td>
                                <td class="data-cell" data-column="product">{{ ucfirst($booking->product) ?? '-' }}</td>
                                <td class="data-cell" data-column="origin">{{ $booking->origin ?? '-' }}</td>
                                <td class="data-cell" data-column="destination">{{ $booking->destination ?? '-' }}</td>
                                <td class="data-cell" data-column="wtt">{{ $booking->wtt ?? '-' }}</td>
                                <td class="data-cell" data-column="pcs">{{ $booking->pcs ?? '-' }}</td>
                                <td class="data-cell" data-column="sales_person">{{ $booking->sales_person ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">No bookings found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $bookings->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
@endpush

@endsection
