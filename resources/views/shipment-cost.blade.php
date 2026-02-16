@extends('layouts.master')

@section('title', 'Shipment Costing')

@section('content')
@php
    $bookingColumns = [
        'bookNo' => 'Book No',
        'bookDate' => 'Book Date',
        'shipperCompany' => 'Company',
        'customer' => 'Customer',
        'product' => 'Product',
        'service' => 'Service',
        'itemContent' => 'Item Content',
        'originCountry' => 'Origin Country',
        'origin' => 'Origin',
        'destinationCountry' => 'Destination Country',
        'destination' => 'Destination',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-truck fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Shipment Costing</h2></div>
        </div>
    </div>

    <div class="app-body">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Shipment Data</h5></div>

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
                            'downloadRoute' => route('bookings.download'),
                            'showChart' => true
                        ])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="bookNo">Track No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="bookDate">Booking Date <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="customer">Customer <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="product">Product <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="origin">Origin <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="destination">Destination <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="weight">Wtt <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="pieces">Pcs <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="amount">Amount <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($shipments as $shipment)
                            <tr class="data-row">
                                <td class="data-cell" data-column="bookNo">
                                    <a href="{{ route('shipment.cost.detail', $shipment->bookNo) }}" class="text-primary fw-bold">
                                        {{ $shipment->bookNo }}
                                    </a>
                                </td>
                                <td class="data-cell" data-column="bookDate">{{ \Carbon\Carbon::parse($shipment->bookDate)->format('d-M-Y') }}</td>
                                <td class="data-cell" data-column="customer">{{ optional($shipment->customer)->customer_name ?? '-' }}</td>
                                <td class="data-cell" data-column="product">{{ ucfirst($shipment->bookingType) }}</td>
                                <td class="data-cell" data-column="origin">{{ $shipment->origin }}</td>
                                <td class="data-cell" data-column="destination">{{ $shipment->destination }}</td>
                                <td class="data-cell" data-column="weight">{{ $shipment->weight ?? '-' }}</td>
                                <td class="data-cell" data-column="pieces">{{ $shipment->pieces ?? '-' }}</td>
                                <td class="data-cell" data-column="amount">
                                    {{ $shipment->shipmentCosts ? number_format($shipment->shipmentCosts->sum('costAmount'), 2) : '0.00' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center text-muted py-3">No shipment data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div>
                        {{ $shipments->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.dashboard-modals', ['columns' => $bookingColumns])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
<style>
    /* Fix vertical alignment for headers and cells */
    .data-table th,
    .data-table td {
        vertical-align: middle; /* Align text vertically */
        text-align: left;       /* Left-align text */
        white-space: nowrap;    /* Prevent text from stacking */
    }

    /* Make sort icon inline with header text */
    .data-table th .sort-icon {
        display: inline-block;
        vertical-align: middle;
        margin-left: 4px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
@endpush
@endsection
