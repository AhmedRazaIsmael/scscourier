@extends('layouts.master')

@section('title', 'Invoice Recovery')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .table-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: auto;
        max-height: 80vh;
        position: relative;
        border-radius: 6px;
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
    .right-controls { display: flex; gap: 10px; align-items: center; }
    .search-group-container { position: relative; display: flex; align-items: center; }
    .search-options-button {
        background-color: #007bff;
        color: white;
        border: 1px solid #007bff;
        padding: 8px 12px;
        border-radius: 4px 0 0 4px;
        cursor: pointer;
        height: 38px;
    }
    .redesigned-search-input { padding: 8px 10px; border: 1px solid #ccc; width: 300px; height: 38px; }
    .go-button { padding: 8px 15px; border: 1px solid #ccc; border-left: none; cursor: pointer; background: #fff; height: 38px; }
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
    .search-options-menu.show-search-menu { display: block; }
    .data-table { width: 100%; border-collapse: collapse; }
    .header-row { background: #fff; border-bottom: 2px solid #ddd; }
    .header-cell {
        text-align: left;
        padding: 10px 12px;
        font-weight: bold;
        color: #333;
        cursor: pointer;
        user-select: none;
    }
    .data-row { border-bottom: 1px solid #eee; transition: background 0.2s; }
    .data-row:hover { background: #f5f5f5; }
    .data-cell { padding: 8px 12px; text-align: center; color: #555; }
    .table-footer { display: flex; justify-content: flex-end; padding: 8px 12px; background: #f7f7f7; border-top: 1px solid #ddd; position: sticky; bottom: 0; }
    .total-count { font-size: 12px; color: #141414; }
    .sort-icon { font-size: 11px; margin-left: 4px; color: #888; }
    .sort-asc .sort-icon::after { content: "▲"; }
    .sort-desc .sort-icon::after { content: "▼"; }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">

            {{-- Customer Wise Outstanding Chart --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="ti ti-bar-chart"></i> Customer Wise Outstanding
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div style="width: 100%; max-width: 800px;">
                        <canvas id="customerChart" height="200"></canvas>
                    </div>
                </div>
            </div>
{{-- Invoice Recovery Table --}}
<div class="card shadow-sm">
    <div class="card-body">

        {{-- Table Controls --}}
        <div class="table-controls modern-controls d-flex justify-content-between mb-2">
            <div class="search-group-container">
                <button class="search-options-button" id="searchOptionsButton">🔍</button>
                <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">

                {{-- Search Column Dropdown --}}
                <div class="search-options-menu" id="searchOptionsMenu">
                    <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                    <label><input type="radio" name="searchColumn" value="invoice_no"> Invoice No</label>
                    <label><input type="radio" name="searchColumn" value="invoice_date"> Invoice Date</label>
                    <label><input type="radio" name="searchColumn" value="pay_due_date"> Pay Due Date</label>
                    <label><input type="radio" name="searchColumn" value="customer"> Customer</label>
                    <label><input type="radio" name="searchColumn" value="product"> Product</label>
                    <label><input type="radio" name="searchColumn" value="invoice_amount"> Invoice Amount</label>
                    <label><input type="radio" name="searchColumn" value="recovered_amount"> Recovered Amount</label>
                    <label><input type="radio" name="searchColumn" value="balance"> Balance</label>
                </div>

                <button class="go-button" onclick="filterTable()">Go</button>
            </div>

            <div class="right-controls">
                @include('layouts.actions-dropdown', ['downloadRoute' => '#'])
                <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-container">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr class="header-row text-center">
                        <th class="header-cell sortable" data-column="invoice_no">Invoice No <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="invoice_date">Invoice Date <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="pay_due_date">Pay Due Date <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="customer">Customer <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="product">Product <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="invoice_amount">Invoice Amount <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="recovered_amount">Recovered Amount <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="balance">Balance <span class="sort-icon"></span></th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    @forelse ($invoices as $inv)
                        <tr class="data-row text-center"
                            @if($inv->balance >= 100000)
                                style="background-color: yellow; color: black; font-weight:bold;"
                            @endif>
                            <td class="data-cell" data-column="invoice_no">
                                <a href="{{ route('invoice.recovery.show', ['id'=>$inv->id, 'type'=>$inv->type]) }}" 
                                   style="color:inherit; text-decoration:underline;">
                                    {{ $inv->invoice_no }}
                                </a>
                            </td>
                            <td class="data-cell" data-column="invoice_date">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-M-Y') }}</td>
                            <td class="data-cell" data-column="pay_due_date">{{ \Carbon\Carbon::parse($inv->pay_due_date)->format('d-M-Y') }}</td>
                            <td class="data-cell" data-column="customer">{{ $inv->customer }}</td>
                            <td class="data-cell" data-column="product">{{ $inv->product }}</td>
                            <td class="data-cell" data-column="invoice_amount">{{ number_format($inv->invoice_amount, 2) }}</td>
                            <td class="data-cell" data-column="recovered_amount">{{ number_format($inv->recovered_amount, 2) }}</td>
                            <td class="data-cell" data-column="balance">{{ number_format($inv->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot class="table-light fw-bold text-center volo">
                    <tr>
                        <td colspan="5" class="text-end">Total</td>
                        <td>{{ number_format($totals['invoice_amount'], 2) }}</td>
                        <td>{{ number_format($totals['recovered_amount'], 2) }}</td>
                        <td>{{ number_format($totals['balance'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="table-footer">
                <span class="total-count" id="totalCount">Total {{ count($invoices) }}</span>
            </div>
        </div>
    </div>
</div>


{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Chart
    const ctx = document.getElementById('customerChart').getContext('2d');
    const customerLabels = {!! json_encode($customerWise->keys()) !!};
    const customerData = {!! json_encode($customerWise->values()) !!};
    new Chart(ctx, {
        type: 'bar',
        data: { 
            labels: customerLabels, 
            datasets: [{ 
                label:'Outstanding Balance', 
                data: customerData, 
                backgroundColor:'rgba(54,162,235,0.5)', 
                borderColor:'rgba(54,162,235,1)', 
                borderWidth:1 
            }] 
        },
        options: { 
            plugins:{legend:{display:false}}, 
            responsive:true, 
            scales:{ 
                y:{beginAtZero:true, ticks:{callback:v=>new Intl.NumberFormat().format(v)}} 
            } 
        }
    });

    // Search + Filter
    const searchInput = document.getElementById('searchInput');
    window.filterTable = () => {
        const input = searchInput.value.toUpperCase();
        const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
        const rows = document.querySelectorAll('#dataTable tbody tr.data-row');
        rows.forEach(row => {
            let match = selectedColumn==='all'
                ? [...row.querySelectorAll('td.data-cell')].some(td => td.innerText.toUpperCase().includes(input))
                : row.querySelector(`td[data-column="${selectedColumn}"]`)?.innerText.toUpperCase().includes(input);
            row.style.display = match ? '' : 'none';
        });
    };
    window.clearSearchAndReset = () => {
        searchInput.value='';
        document.querySelectorAll('#dataTable tbody tr').forEach(r=>r.style.display='');
    };

    // Sorting
    document.querySelectorAll('.header-cell.sortable').forEach(header=>{
        header.addEventListener('click', ()=>{
            const col = header.dataset.column;
            const dir = header.classList.contains('sort-asc')?'desc':'asc';
            document.querySelectorAll('.header-cell.sortable').forEach(h=>h.classList.remove('sort-asc','sort-desc'));
            header.classList.add('sort-'+dir);
            const rows = Array.from(document.querySelectorAll('#dataTable tbody tr.data-row'));
            rows.sort((a,b)=>{
                const aText=a.querySelector(`td[data-column="${col}"]`)?.innerText??'';
                const bText=b.querySelector(`td[data-column="${col}"]`)?.innerText??'';
                return dir==='asc'?aText.localeCompare(bText):bText.localeCompare(aText);
            });
            const tbody=document.querySelector('#dataTable tbody');
            rows.forEach(r=>tbody.appendChild(r));
        });
    });

    // Search Options toggle
    document.getElementById('searchOptionsButton').addEventListener('click', e=>{
        e.stopPropagation();
        document.getElementById('searchOptionsMenu').classList.toggle('show-search-menu');
    });
    document.addEventListener('click', e=>{
        if(!document.getElementById('searchOptionsButton').contains(e.target) &&
           !document.getElementById('searchOptionsMenu').contains(e.target)) {
            document.getElementById('searchOptionsMenu').classList.remove('show-search-menu');
        }
    });
});
</script>
@endsection
