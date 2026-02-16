@extends('layouts.master')
@section('title', 'Pending Shipments')

@section('content')
@php
    $pendingColumns = [
        'preview' => 'Preview',
        'bookNo' => 'Book No',
        'bookDate' => 'Book Date',
        'statusDateTime' => 'Status Date/Time',
        'trackStatus' => 'Track Status',
        'customer_name' => 'Customer',
        'product' => 'Product',
        'itemContent' => 'Item Content',
        'paymentMode' => 'Payment Mode',
        'originCountry' => 'Origin Country',
        'origin' => 'Origin',
        'destinationCountry' => 'Destination Country',
        'destination' => 'Destination',
        'weight' => 'Weight (KG)',
        'pieces' => 'Pieces',
        'orderNo' => 'Order No.',
        'arrivalClearance' => 'Arrival Clearance',
        'ref3plNo' => '3PL Ref No.',
        'ref3plCompany' => '3PL Company',
        'courierCompany' => 'Courier Company',
        'refNo' => 'Ref No.',
        'shipperName' => 'Shipper Name',
        'shipperNumber' => 'Shipper Contact No.',
        'shipperAddress' => 'Shipper Address',
        'consigneeName' => 'Consignee Name',
        'consigneeNumber' => 'Consignee Contact No.',
        'consigneeAddress' => 'Consignee Address',
        'codAmount' => 'COD Amount',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Pending Shipments</h2></div>
        </div>
    </div>

    {{-- Shipments Graph --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title">Shipments Overview</h5></div>
        <div class="card-body">
            <canvas id="shipmentsChart" height="100"></canvas>
        </div>
    </div>

    <div class="app-body">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Pending Shipments Data</h5></div>
            <div class="card-body">
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            @foreach($pendingColumns as $key => $label)
                                <label><input type="radio" name="searchColumn" value="{{ $key }}"> {{ $label }}</label>
                            @endforeach
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>
                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => route('pending.download')])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                @foreach($pendingColumns as $key => $label)
                                    <th class="header-cell sortable" data-column="{{ $key }}">
                                        {{ $label }} <span class="sort-icon"></span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="bookingsTableBody">
                            @forelse ($pendingShipments as $booking)
                                @php 
                                    $thirdParty = $booking->thirdparty ?? null;
                                    $latestStatus = $booking->latestStatusMixed();
                                    $isDelivered = in_array(strtolower(trim($latestStatus->status ?? '')), ['dlv', 'delivered']);
                                @endphp

                                @if(!$isDelivered)
                                    <tr class="data-row">
                                        @foreach($pendingColumns as $key => $label)
                                            <td class="data-cell" data-column="{{ $key }}">
                                                @if($key === 'preview')
                                                    <a href="{{ route('bookings.preview', $booking->id) }}" class="btn btn-sm btn-info" target="_blank">
                                                        Preview
                                                    </a>
                                                @elseif($key === 'bookDate' || $key === 'statusDateTime')
                                                    {{ $key === 'bookDate' 
                                                        ? ($booking->{$key} ? \Carbon\Carbon::parse($booking->{$key})->format('d-m-Y') : '-') 
                                                        : ($latestStatus ? $latestStatus->created_at->format('d-m-Y H:i') : '-') }}
                                               @elseif($key === 'trackStatus')
                                                    {{ $latestStatus?->status ?? '-' }}
                                                @elseif($key === 'customer_name')
                                                    {{ $booking->customer?->customer_name ?? '-' }}
                                                @elseif($key === 'product')
                                                    {{ $booking->customer?->product ?? '-' }}
                                                @elseif($key === 'ref3plCompany')
                                                    {{ $thirdParty->company_name ?? '-' }}
                                                @elseif($key === 'ref3plNo')
                                                    {{ $thirdParty->ref_no ?? '-' }}
                                                @else
                                                    {{ $booking->{$key} ?? '-' }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="{{ count($pendingColumns) }}" class="text-center text-muted py-3">
                                        No pending shipments found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
</div>
                    <div>
                        {{ $pendingShipments->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.dashboard-modals', [
    'columns' => array_keys($pendingColumns),
    'downloadAction'=> route('pending.download')
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('shipmentsChart').getContext('2d');
    
    const originData = @json($pendingShipments->groupBy('origin')->map->count());
    const destinationData = @json($pendingShipments->groupBy('destination')->map->count());

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(originData),
            datasets: [
                {
                    label: 'Origin-wise Shipments',
                    data: Object.values(originData),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                },
                {
                    label: 'Destination-wise Shipments',
                    data: Object.values(destinationData),
                    backgroundColor: 'rgba(255, 99, 132, 0.6)'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Pending Shipments Overview' }
            }
        }
    });
});

// Search and reset functions
function filterTable() {
    const input = document.getElementById('searchInput').value.toUpperCase();
    const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
    const rows = document.querySelectorAll('#dataTable tbody tr.data-row');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;
        if(selectedColumn === 'all') {
            match = [...cells].some(td => td.innerText.toUpperCase().includes(input));
        } else {
            const index = [...document.querySelectorAll('.header-cell')].findIndex(h => h.dataset.column === selectedColumn);
            match = cells[index] && cells[index].innerText.toUpperCase().includes(input);
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
@endpush
@endsection
