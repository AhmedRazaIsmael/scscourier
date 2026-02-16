@extends('layouts.master')
@section('title', 'Import Invoice')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $bookingColumns = [
        'invoice_no' => 'Invoice No',
        'invoice_date' => 'Invoice Date',
        'pay_due_date' => 'Pay Due Date',
        'customer' => 'Customer',
        'pay_mode' => 'Pay Mode',
        'remarks' => 'Remarks',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-file-earmark-text fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Import Invoice</h2></div>
        </div>
        <div class="ms-auto">
            <a href="{{ route('invoice.import.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Create
            </a>
        </div>
    </div>

    <div class="app-body">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Import Invoice List</h5></div>

            {{-- Table Controls --}}
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
                    @include('layouts.actions-dropdown', ['downloadRoute' => route('bookings.download')])
                    <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                </div>
            </div>

            {{-- Invoice Table --}}
            <div class="table-container">
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr class="header-row">
                            @foreach($bookingColumns as $key => $label)
                                <th class="header-cell sortable" data-column="{{ $key }}">{{ $label }} <span class="sort-icon"></span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $invoice)
                            <tr class="data-row">
                                <td class="data-cell" data-column="invoice_no">
                                    <a href="{{ route('invoice.import.edit', $invoice->id) }}" class="text-primary fw-bold">
                                        {{ $invoice->invoice_no ?? '-' }}
                                    </a>
                                </td>
                                <td class="data-cell" data-column="invoice_date">{{ $invoice->invoice_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="data-cell" data-column="pay_due_date">{{ $invoice->pay_due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="data-cell" data-column="customer">{{ $invoice->customer->customer_name ?? '-' }}</td>
                                <td class="data-cell" data-column="pay_mode">{{ ucfirst($invoice->pay_mode ?? '-') }}</td>
                                <td class="data-cell" data-column="remarks">{{ $invoice->remarks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($bookingColumns) }}" class="text-center text-muted py-3">No import invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@include('layouts.dashboard-modals', ['columns' => $bookingColumns])

{{-- Search, Filter, Sort handled by tables.js --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');

    // Toggle search options menu
    document.getElementById('searchOptionsButton').addEventListener('click', e => {
        e.stopPropagation();
        document.getElementById('searchOptionsMenu').classList.toggle('show-search-menu');
    });
    document.addEventListener('click', e => {
        if (!document.getElementById('searchOptionsButton').contains(e.target) &&
            !document.getElementById('searchOptionsMenu').contains(e.target)) {
            document.getElementById('searchOptionsMenu').classList.remove('show-search-menu');
        }
    });

    // Column sorting
    document.querySelectorAll('.header-cell.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const col = header.dataset.column;
            const dir = header.classList.contains('sort-asc') ? 'desc' : 'asc';
            document.querySelectorAll('.header-cell.sortable').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            header.classList.add('sort-' + dir);
            const rows = Array.from(document.querySelectorAll('#dataTable tbody tr.data-row'));
            rows.sort((a, b) => {
                const aText = a.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                const bText = b.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                return dir === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });
            const tbody = document.querySelector('#dataTable tbody');
            rows.forEach(r => tbody.appendChild(r));
        });
    });
});

// Filter table function
function filterTable() {
    const input = document.getElementById('searchInput').value.toUpperCase();
    const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
    const rows = document.querySelectorAll('#dataTable tbody tr.data-row');
    rows.forEach(row => {
        let match = false;
        if (selectedColumn === 'all') {
            match = [...row.querySelectorAll('td.data-cell')].some(td => td.innerText.toUpperCase().includes(input));
        } else {
            const cell = row.querySelector(`td[data-column="${selectedColumn}"]`);
            match = cell ? cell.innerText.toUpperCase().includes(input) : false;
        }
        row.style.display = match ? '' : 'none';
    });
}

function clearSearchAndReset() {
    document.getElementById('searchInput').value = '';
    document.querySelector('input[value="all"]').checked = true;
    filterTable();
}
</script>
@endsection
