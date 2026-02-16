@extends('layouts.master')

@section('title', 'Uninvoiced 3PL / Export Bookings')

@section('content')
<div class="app-container">

    <!-- 🔹 Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-plane-departure fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Uninvoiced 3PL / Export Bookings</h2></div>
        </div>
    </div>

    <div class="app-body">

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-semibold text-center mb-3">📦 Monthly Pending Bookings</h6>
                        <canvas id="monthChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-semibold text-center mb-3">👤 Customer-wise Pending Bookings</h6>
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Uninvoiced Bookings Data</h5>
            </div>

            <div class="card-body">

                <!-- Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">

                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="book_no"> Book No</label>
                            <label><input type="radio" name="searchColumn" value="customer"> Customer</label>
                            <label><input type="radio" name="searchColumn" value="origin"> Origin</label>
                            <label><input type="radio" name="searchColumn" value="destination"> Destination</label>
                            <label><input type="radio" name="searchColumn" value="shipper_company"> Shipper Company</label>
                            <label><input type="radio" name="searchColumn" value="consignee_company"> Consignee Company</label>
                        </div>

                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => '#'])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row text-center">
                                <th class="header-cell sortable" data-column="book_no">Book No</th>
                                <th class="header-cell sortable" data-column="book_date">Book Date</th>
                                <th class="header-cell sortable" data-column="customer">Customer</th>
                                <th class="header-cell sortable" data-column="product">Product</th>
                                <th class="header-cell sortable" data-column="origin">Origin</th>
                                <th class="header-cell sortable" data-column="destination">Destination</th>
                                <th class="header-cell sortable" data-column="weight">Weight</th>
                                <th class="header-cell sortable" data-column="pcs">Pcs</th>
                                <th class="header-cell sortable" data-column="shipper_company">Shipper Company</th>
                                <th class="header-cell sortable" data-column="shipper_name">Shipper Name</th>
                                <th class="header-cell sortable" data-column="consignee_company">Consignee Company</th>
                                <th class="header-cell sortable" data-column="consignee_name">Consignee Name</th>
                                <th class="header-cell sortable" data-column="ref_no">TPL Ref No</th>
                                <th class="header-cell" data-column="action">Action</th>
                            </tr>
                        </thead>

                        <tbody id="tableBody">
                            @forelse($bookings as $booking)
                                <tr class="data-row text-center">
                                    <td class="data-cell" data-column="book_no">{{ $booking->bookNo }}</td>
                                    <td class="data-cell" data-column="book_date">{{ \Carbon\Carbon::parse($booking->bookDate)->format('d-M-Y') }}</td>
                                    <td class="data-cell" data-column="customer">{{ $booking->customer->customer_name ?? '-' }}</td>
                                    <td class="data-cell" data-column="product">{{ $booking->bookingType }}</td>
                                    <td class="data-cell" data-column="origin">{{ $booking->origin ?? '-' }}</td>
                                    <td class="data-cell" data-column="destination">{{ $booking->destination ?? '-' }}</td>
                                    <td class="data-cell" data-column="weight">{{ $booking->weight ?? '-' }}</td>
                                    <td class="data-cell" data-column="pcs">{{ $booking->pieces ?? '-' }}</td>
                                    <td class="data-cell" data-column="shipper_company">{{ $booking->shipperCompany ?? '-' }}</td>
                                    <td class="data-cell" data-column="shipper_name">{{ $booking->shipperName ?? '-' }}</td>
                                    <td class="data-cell" data-column="consignee_company">{{ $booking->consigneeCompany ?? '-' }}</td>
                                    <td class="data-cell" data-column="consignee_name">{{ $booking->consigneeName ?? '-' }}</td>
                                    <td class="data-cell" data-column="ref_no">{{ $booking->ref_no ?? '-' }}</td>

                                    <td class="data-cell">
                                        <a href="{{ route('invoice.export.create', ['bookNo' => $booking->bookNo]) }}" class="btn btn-sm btn-primary">Create Invoice</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center text-muted py-3">
                                        No uninvoiced bookings found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>

                    <div class="mt-3">
                        {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Inject Chart Data -->
<script>
    const monthLabels = @json(array_keys($monthWise->toArray()));
    const monthData   = @json(array_values($monthWise->toArray()));

    const customerLabels = @json(array_keys($customerWise->toArray()));
    const customerData   = @json(array_values($customerWise->toArray()));
</script>

<!-- Render Charts -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Month Chart
    new Chart(document.getElementById('monthChart'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{ data: monthData }]
        }
    });

    // Customer Chart
    new Chart(document.getElementById('customerChart'), {
        type: 'bar',
        data: {
            labels: customerLabels,
            datasets: [{ data: customerData }]
        }
    });

});
</script>

<!-- Table JS -->
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>

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
                match = [...row.querySelectorAll('td.data-cell')]
                    .some(td => td.innerText.toUpperCase().includes(input));
            } else {
                const cell = row.querySelector(`td[data-column="${selectedColumn}"]`);
                match = cell && cell.innerText.toUpperCase().includes(input);
            }
            row.style.display = match ? '' : 'none';
        });
    };

    window.clearSearchAndReset = () => {
        searchInput.value = '';
        document.querySelectorAll('#dataTable tbody tr').forEach(r => r.style.display = '');
    };

    // Sorting
    document.querySelectorAll('.header-cell.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const col = header.dataset.column;
            const dir = header.classList.contains('sort-asc') ? 'desc' : 'asc';

            document.querySelectorAll('.header-cell.sortable')
                .forEach(h => h.classList.remove('sort-asc', 'sort-desc'));

            header.classList.add('sort-' + dir);

            const rows = Array.from(document.querySelectorAll('#dataTable tbody tr.data-row'));
            rows.sort((a, b) => {
                const aText = a.querySelector(`td[data-column="${col}"]`).innerText;
                const bText = b.querySelector(`td[data-column="${col}"]`).innerText;
                return dir === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            const tbody = document.querySelector('#dataTable tbody');
            rows.forEach(r => tbody.appendChild(r));
        });
    });

    // Search menu toggle
    document.getElementById('searchOptionsButton').addEventListener('click', e => {
        e.stopPropagation();
        document.getElementById('searchOptionsMenu').classList.toggle('show-search-menu');
    });

    document.addEventListener('click', e => {
        if (!document.getElementById('searchOptionsButton').contains(e.target)
            && !document.getElementById('searchOptionsMenu').contains(e.target)) {
            document.getElementById('searchOptionsMenu').classList.remove('show-search-menu');
        }
    });

});
</script>
@endpush
