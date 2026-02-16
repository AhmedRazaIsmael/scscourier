@extends('layouts.master')

@section('title') {{ 'Sales Funnel' }} @endsection

@section('content')
<style>
.table-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    border: 1px solid #ddd;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
.right-controls { display: flex; gap: 10px; }
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
.reset-button {
    padding: 8px 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    cursor: pointer;
    background: #fff;
    font-weight: 600;
}
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
.data-cell { padding: 8px 12px; text-align: left; color: #555; }
.table-footer {
    display: flex;
    justify-content: flex-end;
    padding: 8px 12px;
    background: #f7f7f7;
    border-top: 1px solid #ddd;
    position: sticky;
    bottom: 0;
}
.total-count { font-size: 12px; color: #777; }
.sort-icon { font-size: 11px; margin-left: 4px; color: #888; }
.sort-asc .sort-icon::after { content: "▲"; }
.sort-desc .sort-icon::after { content: "▼"; }
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
</style>

<div class="container mt-4">
    <h3 class="mb-4">Sales Funnel</h3>

    {{-- Filter --}}
    <form method="GET" action="{{ route('sales.funnel') }}" class="row mb-4">
        <div class="col-md-4">
            <select name="salesPerson" class="form-control">
                <option value="">All Sales Persons</option>
                @foreach($salesPeople as $user)
                    <option value="{{ $user->id }}" {{ $salesPersonId == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Submit</button>
        </div>
    </form>

    {{-- Funnel Chart --}}
    <div class="card p-3 mb-5">
        <canvas id="funnelChart" height="200"></canvas>
    </div>

    {{-- Modern Table --}}
    <div class="card p-3">
        <h5>Sales Activity Breakdown</h5>

        <div class="table-controls">
            <div class="search-group-container">
                <button class="search-options-button" id="searchOptionsButton">🔍</button>
                <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                <div class="search-options-menu" id="searchOptionsMenu">
                    <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label><br>
                    <label><input type="radio" name="searchColumn" value="bookingType"> Booking Type</label><br>
                    <label><input type="radio" name="searchColumn" value="territory"> Territory</label><br>
                    <label><input type="radio" name="searchColumn" value="sales_person"> Sales Person</label><br>
                    <label><input type="radio" name="searchColumn" value="customer"> Customer</label><br>
                    <label><input type="radio" name="searchColumn" value="days"> Days Till Last Sale</label><br>
                    <label><input type="radio" name="searchColumn" value="level"> Sales Level</label><br>
                    <label><input type="radio" name="searchColumn" value="last_date"> Last Date</label>
                </div>
                <button class="go-button" onclick="filterTable()">Go</button>
            </div>

            <div class="right-controls">
                <button class="reset-button" onclick="clearSearchAndReset()">Reset</button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr class="header-row">
                        <th class="header-cell sortable" data-column="bookingType">Booking Type <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="territory">Territory <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="sales_person">Sales Person <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="customer">Customer <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="days">Days Till Last Sale <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="level">Sales Level <span class="sort-icon"></span></th>
                        <th class="header-cell sortable" data-column="last_date">Last Date <span class="sort-icon"></span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tableData as $row)
                        <tr class="data-row">
                            <td class="data-cell" data-column="bookingType">{{ $row['bookingType'] }}</td>
                            <td class="data-cell" data-column="territory">{{ $row['territory'] }}</td>
                            <td class="data-cell" data-column="sales_person">{{ $row['sales_person'] }}</td>
                            <td class="data-cell" data-column="customer">{{ $row['customer'] }}</td>
                            <td class="data-cell" data-column="days">{{ $row['days'] }}</td>
                            <td class="data-cell" data-column="level">{{ $row['level'] }}</td>
                            <td class="data-cell" data-column="last_date">{{ $row['last_date'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No data found</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="table-footer">
                <span class="total-count">Total {{ count($tableData) }}</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.getElementById('searchOptionsButton');
    const menu = document.getElementById('searchOptionsMenu');

    searchBtn.addEventListener('click', e => {
        e.stopPropagation();
        menu.classList.toggle('show-search-menu');
    });

    document.addEventListener('click', e => {
        if (!searchBtn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('show-search-menu');
        }
    });

    // Sorting
    document.querySelectorAll('.header-cell.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const col = header.dataset.column;
            const dir = header.classList.contains('sort-asc') ? 'desc' : 'asc';
            document.querySelectorAll('.header-cell.sortable').forEach(h => h.classList.remove('sort-asc','sort-desc'));
            header.classList.add('sort-' + dir);

            const rows = Array.from(document.querySelectorAll('#dataTable tbody tr'));
            rows.sort((a,b) => {
                const aText = a.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                const bText = b.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                return dir === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });
            const tbody = document.querySelector('#dataTable tbody');
            rows.forEach(r => tbody.appendChild(r));
        });
    });
});

function filterTable() {
    const input = document.getElementById('searchInput').value.toUpperCase();
    const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
    const rows = document.querySelectorAll('#dataTable tbody tr');

    rows.forEach(row => {
        let match = false;
        if(selectedColumn === 'all') {
            match = [...row.querySelectorAll('td')].some(td => td.innerText.toUpperCase().includes(input));
        } else {
            const cell = row.querySelector(`td[data-column="${selectedColumn}"]`);
            match = cell ? cell.innerText.toUpperCase().includes(input) : false;
        }
        row.style.display = match ? '' : 'none';
    });
}

function clearSearchAndReset() {
    document.getElementById('searchInput').value = '';
    filterTable();
}
</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('funnelChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Active (<30)', 'Need Attention (31-60)', 'Serious (61-90)', 'Dormant (91-120)', 'Lost (>120)'],
        datasets: [{
            label: 'Bookings',
            data: [
                {{ $funnel['Active'] }},
                {{ $funnel['Need Attention'] }},
                {{ $funnel['Need Serious Attention'] }},
                {{ $funnel['Dormant'] }},
                {{ $funnel['Lost'] }}
            ],
            backgroundColor: ['#17a2b8', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'],
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});
</script>
@endpush
