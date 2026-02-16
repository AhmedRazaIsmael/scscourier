@extends('layouts.master')

@section('title', 'VOID Bookings')

@section('content')
@php
    $voidBookingColumns = [
        'bookNo' => 'Book No.',
        'customer' => 'Customer',
        'remarks' => 'Remarks',
        'user' => 'Voided By',
        'marked_at' => 'Marked At',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">VOID Bookings</h2>
            </div>
        </div>
    </div>

    <div class="app-body">

        {{-- =================== DATA TABLE =================== --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">VOID Bookings Table</h5>
            </div>
            <div class="card-body">
                <!-- Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            @foreach($voidBookingColumns as $key => $label)
                                <label><input type="radio" name="searchColumn" value="{{ $key }}"> {{ $label }}</label>
                            @endforeach
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>
                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => route('booking.download'), 'showChart' => false])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Main VOID Bookings Table -->
                <div class="table-container">
                    <table class="data-table" id="voidDataTable">
                        <thead>
                            <tr class="header-row">
                                @foreach($voidBookingColumns as $key => $label)
                                    <th class="header-cell sortable" data-column="{{ $key }}">
                                        {{ $label }} <span class="sort-icon"></span>
                                    </th>
                                @endforeach
                                <th class="header-cell text-center">Action</th> <!-- New Action column -->
                            </tr>
                        </thead>
                        <tbody id="voidBookingsTableBody">
                            @forelse($voidBookings as $vb)
                                <tr class="data-row">
                                    <td class="data-cell" data-column="bookNo">{{ $vb->booking->bookNo ?? '-' }}</td>
                                    <td class="data-cell" data-column="customer">{{ $vb->booking->customer->customer_name ?? '-' }}</td>
                                    <td class="data-cell" data-column="remarks">{{ $vb->remarks }}</td>
                                    <td class="data-cell" data-column="user">{{ $vb->user->name ?? '-' }}</td>
                                    <td class="data-cell" data-column="marked_at">{{ $vb->created_at->format('d-m-Y H:i') }}</td>
                                    <td class="data-cell text-center"> <!-- Reset button per row -->
                                        <form action="{{ route('voidBookings.reset', $vb->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this booking from VOID?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Reset</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($voidBookingColumns) + 1 }}" class="text-center text-muted py-3">
                                        No VOID bookings found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination if using paginated $voidBookings --}}
                    <div class="mt-3">
                        {{ $voidBookings->onEachSide(1)->links('pagination::bootstrap-5') ?? '' }}
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

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

<script>
    // Only populate hidden booking_id
    var searchInput = document.getElementById('searchInput');
    var tableBody = document.getElementById('voidBookingsTableBody');

    function filterTable() {
        let filter = searchInput.value.toLowerCase();
        let rows = tableBody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            let cells = rows[i].getElementsByTagName('td');
            let show = false;
            for (let j = 0; j < cells.length; j++) {
                if(cells[j].textContent.toLowerCase().includes(filter)) {
                    show = true;
                    break;
                }
            }
            rows[i].style.display = show ? '' : 'none';
        }
    }

    function clearSearchAndReset() {
        searchInput.value = '';
        filterTable();
    }
</script>
@endpush
@endsection
