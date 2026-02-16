@extends('layouts.master')
@section('title', 'Export Invoice')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
       .pagination {
    margin: 4px 0;
    gap: 4px;
    font-size: 12px;
}
.pagination .page-item {
    margin: 0;
}
.pagination .page-link {
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1.2;
    border-radius: 3px !important;
    color: #007bff;
    border: 1px solid #ddd;
}
.pagination .page-item.active .page-link {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: #fff !important;
}
.pagination .page-item.disabled .page-link {
    color: #aaa !important;
    background-color: #f8f9fa !important;
    border-color: #ddd !important;
}
/* Same modern table style as Import Invoice */
.table-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    border: 1px solid #ddd;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    overflow: auto;
    max-height: 80vh;
    position: relative;
}
.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    background: #f0f2f5;
    border-bottom: 1px solid #ddd;
    position: sticky;
    top: 0;
    z-index: 20;
}
.right-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}
.search-group-container {
    position: relative;
    display: flex;
    align-items: center;
}
.search-options-button {
    background-color: #007bff;
    color: white;
    border: 1px solid #007bff;
    padding: 8px 12px;
    border-radius: 4px 0 0 4px;
    cursor: pointer;
    height: 38px;
}
.redesigned-search-input {
    padding: 8px 10px;
    border: 1px solid #ccc;
    width: 300px;
    height: 38px;
}
.go-button {
    padding: 8px 15px;
    border: 1px solid #ccc;
    border-left: none;
    cursor: pointer;
    background: #fff;
    height: 38px;
}
.search-options-menu {
    position: absolute;
    top: 38px;
    left: 0;
    background: #fff;
    border: 1px solid #ddd;
    padding: 8px;
    display: none;
    z-index: 10;
    border-radius: 0 0 4px 4px;
}
.search-options-menu.show-search-menu {
    display: block;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.header-row {
    background: #fff;
    border-bottom: 2px solid #ddd;
}
.header-cell {
    text-align: left;
    padding: 10px 12px;
    font-weight: bold;
    color: #333;
    cursor: pointer;
    user-select: none;
}
.data-row {
    border-bottom: 1px solid #eee;
    transition: background 0.2s;
}
.data-row:hover {
    background: #f5f5f5;
}
.data-cell {
    padding: 8px 12px;
    text-align: left;
    color: #555;
}
.table-footer {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background: #f7f7f7;
    border-top: 1px solid #ddd;
    position: sticky;
    bottom: 0;
}
.total-count {
    font-size: 12px;
    color: #777;
}
.sort-icon {
    font-size: 11px;
    margin-left: 4px;
    color: #888;
}
.sort-asc .sort-icon::after {
    content: "▲";
}
.sort-desc .sort-icon::after {
    content: "▼";
}
</style>

<div class="app-container">
    <!-- Page Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-file-earmark-arrow-up fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Export Invoice</h2></div>
        </div>
        <div class="ms-auto">
            <a href="{{ route('invoice.export.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Create
            </a>
        </div>
    </div>

    <div class="app-body">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Export Invoice List</h5></div>

            <div class="table-container">
                <!-- Table Controls -->
                <div class="table-controls modern-controls">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search invoice..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="invoice_no"> Invoice No</label>
                            <label><input type="radio" name="searchColumn" value="invoice_date"> Invoice Date</label>
                            <label><input type="radio" name="searchColumn" value="pay_due_date"> Pay Due Date</label>
                            <label><input type="radio" name="searchColumn" value="customer"> Customer</label>
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => '#'])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Invoice Table -->
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr class="header-row">
                            <th class="header-cell sortable" data-column="invoice_no">Invoice No <span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="invoice_date">Invoice Date <span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="pay_due_date">Pay Due Date <span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="customer">Customer <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody id="invoiceTableBody">
                        @forelse ($invoices as $invoice)
                            <tr class="data-row">
                                <td class="data-cell" data-column="invoice_no">
                                    <a href="{{ route('invoice.export.edit', $invoice->id) }}"
                                       class="text-decoration-none fw-semibold text-success">
                                       {{ $invoice->invoice_no ?? '-' }}
                                    </a>
                                </td>
                                <td class="data-cell" data-column="invoice_date">
                                    {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="data-cell" data-column="pay_due_date">
                                    {{ $invoice->pay_due_date ? \Carbon\Carbon::parse($invoice->pay_due_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="data-cell" data-column="customer">{{ $invoice->customer->customer_name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No export invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Footer -->
                <div>
                    {{ $invoices->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS for search and sort -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');

    window.filterTable = () => {
        const input = searchInput.value.toUpperCase();
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
    };

    window.clearSearchAndReset = () => {
        searchInput.value = '';
        filterTable();
    };

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
</script>
@endsection
