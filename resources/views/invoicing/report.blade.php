@extends('layouts.master')

@section('title', 'Invoice Detail Report')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.table-container { font-family:'Segoe UI', Tahoma, Geneva, Verdana,sans-serif; font-size:14px; border:1px solid #ddd; box-shadow:0 4px 12px rgba(0,0,0,0.08); overflow:auto; max-height:80vh; border-radius:6px; }
.table-controls { display:flex; justify-content:space-between; align-items:center; padding:10px 15px; background:#f0f2f5; border-bottom:1px solid #ddd; position:sticky; top:0; z-index:20; }
.right-controls { display:flex; gap:10px; align-items:center; }
.search-group-container { position:relative; display:flex; align-items:center; }
.search-options-button { background-color:#007bff; color:white; border:1px solid #007bff; padding:8px 12px; border-radius:4px 0 0 4px; cursor:pointer; height:38px; }
.redesigned-search-input { padding:8px 10px; border:1px solid #ccc; width:300px; height:38px; }
.go-button { padding:8px 15px; border:1px solid #ccc; border-left:none; cursor:pointer; background:#fff; height:38px; }
.search-options-menu { position:absolute; top:38px; left:0; background:#fff; border:1px solid #ddd; padding:8px; display:none; z-index:10; border-radius:0 0 4px 4px; }
.search-options-menu.show-search-menu { display:block; }
.data-table { width:100%; border-collapse:collapse; }
.header-row { background:#fff; border-bottom:2px solid #ddd; }
.header-cell { text-align:left; padding:10px 12px; font-weight:bold; color:#333; cursor:pointer; user-select:none; }
.data-row { border-bottom:1px solid #eee; transition:background 0.2s; }
.data-row:hover { background:#f5f5f5; }
.data-cell { padding:8px 12px; text-align:center; color:#555; }
.table-footer { display:flex; justify-content:flex-end; padding:8px 12px; background:#f7f7f7; border-top:1px solid #ddd; position:sticky; bottom:0; }
.total-count { font-size:12px; color:#777; }
.sort-icon { font-size:11px; margin-left:4px; color:#888; }
.sort-asc .sort-icon::after { content:"▲"; }
.sort-desc .sort-icon::after { content:"▼"; }
</style>

<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="ti ti-file-invoice"></i> Invoice Detail Report</h4>

    <div class="card shadow-sm">
        <div class="table-container">

            {{-- === Controls === --}}
            <div class="table-controls">
                <div class="search-group-container">
                    <button class="search-options-button" id="searchOptionsButton">🔍</button>
                    <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                    <div class="search-options-menu" id="searchOptionsMenu">
                        <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label><br>
                        <label><input type="radio" name="searchColumn" value="book_no"> Book No</label><br>
                        <label><input type="radio" name="searchColumn" value="invoice_no"> Invoice No</label><br>
                        <label><input type="radio" name="searchColumn" value="customer"> Customer</label><br>
                        <label><input type="radio" name="searchColumn" value="product"> Product</label><br>
                        <label><input type="radio" name="searchColumn" value="origin"> Origin</label><br>
                        <label><input type="radio" name="searchColumn" value="destination"> Destination</label>
                    </div>
                    <button class="go-button" onclick="filterTable()">Go</button>
                </div>

                <div class="right-controls">
                    @include('layouts.actions-dropdown', ['downloadRoute' => '#'])
                    <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                </div>
            </div>

            {{-- === Table === --}}
            <table class="data-table" id="dataTable">
                <thead>
                    <tr class="header-row text-center">
                        <th class="header-cell sortable" data-column="book_no">Book No <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="book_date">Book Date <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="invoice_no">Invoice No <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="invoice_date">Invoice Date <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="customer">Customer <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="product">Product <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="origin">Origin <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="destination">Destination <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="wtt">Wtt <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="company">3PL Company <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="refno">3PL Refno <span class="sort-icon"></span></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($bookings as $booking)
                        @php
                            $export = $booking->exportInvoices->first();
                            $import = $booking->importInvoices->first();
                        @endphp
                        @if($export || $import)
                        <tr class="data-row text-center">
                            <td class="data-cell" data-column="book_no">{{ $booking->bookNo }}</td>
                            <td class="data-cell" data-column="book_date">{{ \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y') }}</td>
                            <td class="data-cell" data-column="invoice_no">{{ $export->invoice_no ?? $import->invoice_no ?? '-' }}</td>
                            <td class="data-cell" data-column="invoice_date">
                                @php
                                    $date = $export->invoice_date ?? $import->invoice_date ?? null;
                                @endphp
                                {{ $date ? \Carbon\Carbon::parse($date)->format('d-M-Y') : '-' }}
                            </td>
                            <td class="data-cell" data-column="customer">{{ $booking->customer->customer_name ?? '-' }}</td>
                            <td class="data-cell" data-column="product">{{ $booking->itemContent ?? '-' }}</td>
                            <td class="data-cell" data-column="origin">{{ $booking->origin ?? '-' }}</td>
                            <td class="data-cell" data-column="destination">{{ $booking->destination ?? '-' }}</td>
                            <td class="data-cell" data-column="wtt">{{ $booking->weight ?? '-' }}</td>
                            <td class="data-cell" data-column="company">{{ $booking->partners->company_name ?? '-' }}</td>
                            <td class="data-cell" data-column="refno">{{ $booking->partners->ref_no ?? '-' }}</td>
                        </tr>
                        @endif
                    @empty
                        <tr><td colspan="11" class="text-center text-muted py-3">No invoiced bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="d-flex justify-content-end p-3">
                {{ $bookings->links() }}
            </div>

            <div class="table-footer">
                <span class="total-count" id="totalCount">Total {{ count($bookings) }}</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');

    // === Search Filter ===
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

    // === Reset ===
    window.clearSearchAndReset = () => {
        searchInput.value = '';
        document.querySelectorAll('#dataTable tbody tr').forEach(r => r.style.display = '');
    };

    // === Sort Columns ===
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

    // === Search Menu Toggle ===
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
});
</script>
@endsection
