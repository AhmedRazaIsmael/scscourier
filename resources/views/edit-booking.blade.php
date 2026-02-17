@extends('layouts.master')
@section('title', 'Edit Bookings')

@section('content')
@php
$bookingColumns = [
'bookNo' => 'Book No',
'bookDate' => 'Book Date',
'customer_name' => 'Customer',
'product' => 'Product',
'service' => 'Service',
'itemContent' => 'Item Content',
'origin' => 'Origin',
'destination' => 'Destination',
'weight' => 'Weight',
'pieces' => 'Pieces',
'orderNo' => 'Order No',
'shipperName' => 'Shipper Name',
'shipperNumber' => 'Shipper Contact',
'shipperAddress' => 'Shipper Address',
'consigneeName' => 'Consignee Name',
'consigneeNumber' => 'Consignee Contact',
'consigneeAddress' => 'Consignee Address',
];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-pencil-square fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Edit Bookings Shipments</h2>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">All Bookings Shipments</h5>
        </div>
        <div class="card-body">
            <!-- Table Controls -->
            <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                <div class="search-group-container">
                    <button class="search-options-button" id="searchOptionsButton">🔍</button>
                    <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                    <div class="search-options-menu" id="searchOptionsMenu">
                        <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label><br>
                        @foreach($bookingColumns as $key => $label)
                        <label><input type="radio" name="searchColumn" value="{{ $key }}"> {{ $label }}</label><br>
                        @endforeach
                    </div>
                    <button class="go-button" onclick="filterTable()">Go</button>
                </div>

                <div class="right-controls">
                    @include('layouts.actions-dropdown')

                    <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                </div>
            </div>


            <!-- Table -->
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
                            <td class="data-cell" data-column="bookNo">
                                <a href="/booking/type/edit/redirect/{{ $b->bookNo }}" class="text-primary fw-bold">{{ $b->bookNo }}</a>
                            </td>
                            <td class="data-cell" data-column="bookDate">{{ $b->bookDate }}</td>
                            <td class="data-cell" data-column="customer_name">{{ $b->customer->customer_name ?? '-' }}</td>
                            <td class="data-cell" data-column="product">{{ $b->bookingType ?? '-' }}</td>
                            <td class="data-cell" data-column="service">{{ $b->service ?? '-' }}</td>
                            <td class="data-cell" data-column="itemContent">{{ $b->itemContent ?? '-' }}</td>
                            <td class="data-cell" data-column="origin">{{ $b->origin ?? '-' }}</td>
                            <td class="data-cell" data-column="destination">{{ $b->destination ?? '-' }}</td>
                            <td class="data-cell" data-column="weight">{{ $b->weight ?? '-' }}</td>
                            <td class="data-cell" data-column="pieces">{{ $b->pieces ?? '-' }}</td>
                            <td class="data-cell" data-column="orderNo">{{ $b->order_no ?? '-' }}</td>
                            <td class="data-cell" data-column="shipperName">{{ $b->shipperName ?? '-' }}</td>
                            <td class="data-cell" data-column="shipperNumber">{{ $b->shipperNumber ?? '-' }}</td>
                            <td class="data-cell" data-column="shipperAddress">{{ $b->shipperAddress ?? '-' }}</td>
                            <td class="data-cell" data-column="consigneeName">{{ $b->consigneeName ?? '-' }}</td>
                            <td class="data-cell" data-column="consigneeNumber">{{ $b->consigneeNumber ?? '-' }}</td>
                            <td class="data-cell" data-column="consigneeAddress">{{ $b->consigneeAddress ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($bookingColumns) }}" class="text-center text-muted py-3">No bookings found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="table-footer mt-2">
                    {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>

                <!-- Chart -->
                @if(session('chartData'))
                <div class="p-3 mt-4">
                    <h5>{{ session('chartData.valueTitle') }} by {{ session('chartData.labelTitle') }}</h5>
                    <canvas id="chartCanvas" height="120"></canvas>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Dashboard Modals --}}
@include('layouts.dashboard-modals', ['columns' => $bookingColumns])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
    @if(session('chartData'))
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('chartCanvas').getContext('2d');
        const labels = @json(session('chartData.labels'));
        const values = @json(session('chartData.values'));
        const chartType = "{{ session('chartData.chartType') }}";

        new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: "{{ session('chartData.valueTitle') }}",
                    data: values,
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
    @endif
</script>
@endpush

@endsection