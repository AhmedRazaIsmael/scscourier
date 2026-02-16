@extends('layouts.master')

@section('title', 'Shipment Costing Detail')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-truck fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Costing Detail</h2></div>
        </div>
    </div>

    <div class="app-body">
        {{-- Costing Form --}}
        <div class="card mb-4">
            <div class="card-header"><h5>Create Cost Entry</h5></div>
            <div class="card-body">
                <form action="{{ route('shipment.cost.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="trackNo" value="{{ $bookNo }}">

                    <div class="row gx-4 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Track No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="{{ $bookNo }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($bookDate)->format('d-M-Y') }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Account Head <span class="text-danger">*</span></label>
                            <select name="account_head" class="form-select" required>
                                <option value="">-- Select Account Head --</option>
                                <option value="Clearance Charges">Clearance Charges</option>
                                <option value="Customs">Customs</option>
                                <option value="DO Charges">DO Charges</option>
                                <option value="Fuel">Fuel</option>
                                <option value="Other Cost">Other Cost</option>
                                <option value="Sales Commission">Sales Commission</option>
                                <option value="Transportation">Transportation</option>
                            </select>
                        </div>
                    </div>

                    <div class="row gx-4 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cost Description</label>
                            <input type="text" name="costDesc" class="form-control" placeholder="Enter Cost Description" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cost Amount <span class="text-danger">*</span></label>
                            <input type="number" name="costAmount" step="0.01" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="OPEN">OPEN</option>
                                <option value="CLOSED">CLOSED</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create</button>
                </form>

                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success mt-2">{{ session('success') }}</div>
                @endif
            </div>
        </div>

        {{-- Costing Entries Table --}}
        <div class="card mb-4">
            <div class="card-header"><h5>Costing Entries</h5></div>
            <div class="card-body">
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search costing entries..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="trackNo"> Track No</label>
                            <label><input type="radio" name="searchColumn" value="date"> Date</label>
                            <label><input type="radio" name="searchColumn" value="accountHead"> Account Head</label>
                            <label><input type="radio" name="searchColumn" value="description"> Description</label>
                            <label><input type="radio" name="searchColumn" value="amount"> Amount</label>
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>
                    <div class="right-controls">
                        @include('layouts.actions-dropdown')
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="trackNo">Track No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="date">Date <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="accountHead">Account Head <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="description">Description <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="amount">Amount <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($costEntries as $entry)
                                <tr class="data-row">
                                    <td class="data-cell" data-column="trackNo">{{ $entry->trackNo }}</td>
                                    <td class="data-cell" data-column="date">{{ \Carbon\Carbon::parse($entry->booking->bookDate)->format('d-M-Y') }}</td>
                                    <td class="data-cell" data-column="accountHead">{{ $entry->accountHead }}</td>
                                    <td class="data-cell" data-column="description">{{ $entry->costDesc }}</td>
                                    <td class="data-cell" data-column="amount">{{ number_format($entry->costAmount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No costing entries found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="table-footer">
                        <span class="total-count">Total {{ count($costEntries) }}</span>
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
