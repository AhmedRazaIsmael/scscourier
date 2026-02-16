@extends('layouts.master')
@section('title', 'Edit Dimensional Weight')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $bookingColumns = [
        'bookNo' => 'Book No.',
        'bookDate' => 'Book Date',
        'company' => 'Company',
        'customer' => 'Customer',
        'product' => 'Product',
        'service' => 'Service',
        'itemContent' => 'Item Content',
        'paymentMode' => 'Payment Mode',
        'origin' => 'Origin',
        'destination' => 'Destination',
        'weight' => 'Weight (KG)',
        'pieces' => 'Pieces',
        'length' => 'Length',
        'width' => 'Width',
        'height' => 'Height',
        'dimensionalWeight' => 'Dimensional Weight',
        'orderNo' => 'Order No.',
        'shipperName' => 'Shipper Name',
        'shipperNumber' => 'Shipper Contact No.',
        'shipperAddress' => 'Shipper Address',
        'consigneeName' => 'Consignee Name',
        'consigneeNumber' => 'Consignee Contact No.',
        'consigneeAddress' => 'Consignee Address',
    ];
@endphp

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-people fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Edit Dimensional Weight</h2></div>
        </div>
    </div>

    <div class="app-body">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Dimensional Weight Data</h5></div>

            <div class="card-body">
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
                        @include('layouts.actions-dropdown', [
                            'downloadRoute' => route('bookings.download'),
                            'showChart' => true
                        ])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                @foreach($bookingColumns as $key => $label)
                                    <th class="header-cell sortable" data-column="{{ $key }}">{{ $label }} <span class="sort-icon"></span></th>
                                @endforeach
                            </tr>
                        </thead>
                       <tbody id="bookingsTableBody">
    @forelse($bookings as $booking)
        <tr class="data-row">
            @foreach($bookingColumns as $key => $label)
                @php
                    $value = '-';
                    if($key == 'bookNo') {
                        $value = $booking->$key ?? '-';
                    } elseif($key == 'customer') {
                        $value = $booking->customer?->customer_name ?? '-';
                    } elseif($key == 'product') {
                        $value = $booking->bookingType ?? '-';
                    } else {
                        $value = $booking->$key ?? '-';
                    }
                @endphp

                @if($key == 'bookNo')
                    <td class="data-cell" data-column="{{ $key }}">
                        <a href="#" class="edit-modal-btn text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#editBookingModal{{ $booking->id }}">
                            {{ $value }}
                        </a>
                    </td>
                @else
                    <td class="data-cell" data-column="{{ $key }}">{{ $value }}</td>
                @endif
            @endforeach
        </tr>
        {{-- Modal same as before --}}
        <div class="modal fade" id="editBookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('dim.weight.update', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Dimensional Weight - {{ $booking->bookNo }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Weight (KG)</label>
                                    <input type="number" step="0.01" name="weight" class="form-control" value="{{ $booking->weight ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Length</label>
                                    <input type="number" step="0.01" name="length" class="form-control" value="{{ $booking->length ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Width</label>
                                    <input type="number" step="0.01" name="width" class="form-control" value="{{ $booking->width ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Height</label>
                                    <input type="number" step="0.01" name="height" class="form-control" value="{{ $booking->height ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Dimensional Weight</label>
                                    <input type="number" step="0.01" name="dimensionalWeight" class="form-control" value="{{ $booking->dimensionalWeight ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <tr>
            <td colspan="{{ count($bookingColumns)+1 }}" class="text-center text-muted py-3">No bookings found</td>
        </tr>
    @endforelse
</tbody>
     </table>

</div>
         <!-- Pagination -->
                <div class="mt-3">
                    {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dashboard Modals --}}
@include('layouts.dashboard-modals', [
    'columns' => array_keys($bookingColumns),
    'chartModel' => 'Booking',
    // 'chartAction' => route('bookings.chart'),
    // 'filterAction' => route('bookings.index'),
    // 'rowFilterAction' => route('bookings.rowFilter'),
    // 'sortAction' => route('bookings.index'),
    // 'aggregateAction' => route('bookings.aggregate'),
    // 'computeAction' => route('bookings.compute'),
    'downloadAction' => route('bookings.download')
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
    // Client-side search/filter
    window.filterTable = () => {
        const input = document.getElementById('searchInput').value.toUpperCase();
        const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
        document.querySelectorAll('#dataTable tbody tr.data-row').forEach(row => {
            let match = selectedColumn === 'all'
                ? [...row.querySelectorAll('td.data-cell')].some(td => td.innerText.toUpperCase().includes(input))
                : (row.querySelector(`td[data-column="${selectedColumn}"]`)?.innerText.toUpperCase().includes(input) || false);
            row.style.display = match ? '' : 'none';
        });
    };

    window.clearSearchAndReset = () => {
        document.getElementById('searchInput').value = '';
        filterTable();
    };

    document.getElementById('searchOptionsButton').addEventListener('click', e => {
        e.stopPropagation();
        document.getElementById('searchOptionsMenu').classList.toggle('show-search-menu');
    });
    document.addEventListener('click', e => {
        if(!document.getElementById('searchOptionsButton').contains(e.target) && !document.getElementById('searchOptionsMenu').contains(e.target)){
            document.getElementById('searchOptionsMenu').classList.remove('show-search-menu');
        }
    });

    // Sorting
    document.querySelectorAll('.header-cell.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const col = header.dataset.column;
            const dir = header.classList.contains('sort-asc') ? 'desc' : 'asc';
            document.querySelectorAll('.header-cell.sortable').forEach(h => h.classList.remove('sort-asc','sort-desc'));
            header.classList.add('sort-'+dir);

            const rows = Array.from(document.querySelectorAll('#dataTable tbody tr.data-row'));
            rows.sort((a,b) => {
                const aText = a.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                const bText = b.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                return dir==='asc'?aText.localeCompare(bText):bText.localeCompare(aText);
            });
            rows.forEach(r => document.querySelector('#dataTable tbody').appendChild(r));
        });
    });
</script>
@endpush
@endsection
