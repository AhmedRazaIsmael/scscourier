@extends('layouts.master')

@section('title', 'Recovered Invoices')

@section('content')
<div class="app-container">

    <!-- 🔹 Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-receipt fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Recovered Invoices</h2></div>
        </div>
    </div>

    <div class="app-body">

        {{-- =================== DATA TABLE =================== --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Recovered Invoices Data</h5></div>
            <div class="card-body">

                <!-- 🔹 Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">

                        <!-- 🔽 Search Column Dropdown -->
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="invoice_no"> Invoice No</label>
                            <label><input type="radio" name="searchColumn" value="invoice_date"> Invoice Date</label>
                            <label><input type="radio" name="searchColumn" value="customer_name"> Customer</label>
                            <label><input type="radio" name="searchColumn" value="recovery_person"> Recovery Person</label>
                            <label><input type="radio" name="searchColumn" value="recovery_amount"> Recovered Amount</label>
                            <label><input type="radio" name="searchColumn" value="remarks"> Remarks</label>
                            <label><input type="radio" name="searchColumn" value="receiving_path"> Receiving Path</label>
                            <label><input type="radio" name="searchColumn" value="inserted_by"> Inserted By</label>
                        </div>

                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => '#'])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- 🔹 Data Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="invoice_no">Invoice No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="invoice_date">Invoice Date <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="customer_name">Customer <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="recovery_person">Recovery Person <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="recovery_amount">Recovered Amount <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="remarks">Remarks <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="receiving_path">Receiving Path <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="inserted_by">Inserted By <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="created_at">Updated At <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($recoveries as $r)
                                <tr class="data-row">
                                    <td class="data-cell" data-column="invoice_no">{{ $r->invoice_no }}</td>
                                    <td class="data-cell" data-column="invoice_date">{{ \Carbon\Carbon::parse($r->invoice_date)->format('d-M-Y') }}</td>
                                    <td class="data-cell" data-column="customer_name">{{ $r->customer_name }}</td>
                                    <td class="data-cell" data-column="recovery_person">{{ $r->recovery_person }}</td>
                                    <td class="data-cell" data-column="recovery_amount">{{ number_format($r->recovery_amount, 2) }}</td>
                                    <td class="data-cell" data-column="remarks">{{ $r->remarks }}</td>
                                    <td class="data-cell" data-column="receiving_path">{{ $r->receiving_path }}</td>
                                    <td class="data-cell" data-column="inserted_by">{{ $r->inserted_by ?? '-' }}</td>
                                    <td class="data-cell" data-column="created_at">{{ \Carbon\Carbon::parse($r->created_at)->format('d-M-Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-3">No recovered invoices found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $recoveries->onEachSide(1)->links('pagination::bootstrap-5') }}
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
<script>
    // Toggle search options menu
    document.getElementById('searchOptionsButton').addEventListener('click', function() {
        const menu = document.getElementById('searchOptionsMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });

    // Search / Filter Table
    function filterTable() {
        const filter = document.getElementById('searchInput').value.toUpperCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toUpperCase().includes(filter) ? '' : 'none';
        });
    }
    function clearSearchAndReset() {
        document.getElementById('searchInput').value = '';
        filterTable();
    }
</script>
@endpush

@endsection
