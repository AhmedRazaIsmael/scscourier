@extends('layouts.master')
@section('title', 'Booking Attachments')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-paperclip fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Booking Attachments</h2></div>
        </div>
    </div>

    <div class="app-body">
        {{-- =================== TABLE =================== --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Booking Attachments Data</h5></div>
            <div class="card-body">
                <!-- Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="book_no"> Book No</label>
                            <label><input type="radio" name="searchColumn" value="customer_code"> Customer Code</label>
                            <label><input type="radio" name="searchColumn" value="customer_name"> Customer</label>
                            <label><input type="radio" name="searchColumn" value="product"> Product</label>
                            <label><input type="radio" name="searchColumn" value="filename"> Filename</label>
                            <label><input type="radio" name="searchColumn" value="insert_by"> Insert By</label>
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>
                    <div class="right-controls">
                        <a href="{{ route('booking.attachments.download', 0) }}" class="btn btn-outline-primary">Download All</a>
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="attachment">Attachment <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="book_no">Book No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="customer_code">Customer Code <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="customer_name">Customer <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="product">Product <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="filename">Filename <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="insert_by">Insert By <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="insert_datetime">Insert Date/Time <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody id="attachmentsTableBody">
                            @forelse($attachments as $att)
                            <tr class="data-row">
                                <td class="data-cell" data-column="attachment">
                                    @if($att->file_path)
                                        <a href="{{ route('booking.attachments.download', $att->id) }}" target="_blank">📄</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="data-cell" data-column="book_no">{{ $att->book_no ?? '-' }}</td>
                                <td class="data-cell" data-column="customer_code">{{ $att->customer_code ?? '-' }}</td>
                                <td class="data-cell" data-column="customer_name">{{ $att->customer_name ?? '-' }}</td>
                                <td class="data-cell" data-column="product">{{ $att->product ?? '-' }}</td>
                                <td class="data-cell" data-column="filename">{{ $att->filename ?? '-' }}</td>
                                <td class="data-cell" data-column="insert_by">{{ $att->insert_by ?? '-' }}</td>
                                <td class="data-cell" data-column="insert_datetime">{{ $att->created_at ? \Carbon\Carbon::parse($att->created_at)->format('d-m-Y H:i') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">No attachments found</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div>
                        {{ $attachments->onEachSide(1)->links('pagination::bootstrap-5') }}
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
function filterTable() {
    // Implement your JS search/filter logic
}

function clearSearchAndReset() {
    document.getElementById('searchInput').value = '';
    // Reset all filters, re-render table
}
</script>
@endpush
@endsection
