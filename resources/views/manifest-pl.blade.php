@extends('layouts.master')

@section('title', 'Manifest Wise P/L')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $manifestColumns = [
        'awb' => 'AWB No',
        'bag' => 'Bag',
        'airline' => 'Airline',
        'date' => 'Date',
        'flight_no' => 'Flight No',
        'pcs' => 'Pcs',
        'airline_wtt' => 'Airline Wtt',
        'pl_wtt' => '3PL Wtt',
        'diff' => 'Diff Wtt',
        'count' => 'Count',
        'flight_cost' => 'Flight Cost',
        'pl_cost' => '3PL Cost',
        'total_cost' => 'Total Cost',
        'revenue' => 'Revenue',
        'profit_loss' => 'Profit / Loss',
        'profit_percent' => 'Profit / Loss %'
    ];
@endphp

<div class="container mt-4">
    <h4 class="mb-4">Manifest Wise P/L</h4>

    <div class="card p-3">
        {{-- Search & Controls --}}
        <div class="table-controls modern-controls">
            <div class="search-group-container">
                <button class="search-options-button" id="searchOptionsButton">🔍</button>
                <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">
                <div class="search-options-menu" id="searchOptionsMenu">
                    @foreach($manifestColumns as $key => $label)
                        <label><input type="radio" name="searchColumn" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}> {{ $label }}</label><br>
                    @endforeach
                    <label><input type="radio" name="searchColumn" value="all"> All Columns</label>
                </div>
                <button class="go-button" onclick="filterTable()">Go</button>
            </div>
            <div class="right-controls">
                <button class="reset-button" onclick="clearSearchAndReset()">Reset</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-container">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr class="header-row">
                        @foreach($manifestColumns as $key => $label)
                            <th class="header-cell sortable" data-column="{{ $key }}">{{ $label }} <span class="sort-icon"></span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($manifestData as $row)
                        <tr class="data-row">
                            @foreach($manifestColumns as $key => $label)
                                <td class="data-cell" data-column="{{ $key }}">
                                    @if(in_array($key, ['flight_cost','pl_cost','total_cost','revenue','profit_loss']))
                                        {{ number_format($row[$key]) }}
                                    @elseif($key === 'profit_percent')
                                        {{ number_format($row[$key],2) }}
                                    @else
                                        {{ $row[$key] }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($manifestColumns) }}" class="text-center text-muted py-3">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="table-footer">
                <span class="total-count">Total {{ count($manifestData) }}</span>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.getElementById('searchOptionsButton');
    const menu = document.getElementById('searchOptionsMenu');

    // Toggle search options
    searchBtn.addEventListener('click', e => { e.stopPropagation(); menu.classList.toggle('show-search-menu'); });
    document.addEventListener('click', e => {
        if (!searchBtn.contains(e.target) && !menu.contains(e.target)) menu.classList.remove('show-search-menu');
    });

    // Sorting
    document.querySelectorAll('.header-cell.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const col = header.dataset.column;
            const dir = header.classList.contains('sort-asc') ? 'desc' : 'asc';
            document.querySelectorAll('.header-cell.sortable').forEach(h => h.classList.remove('sort-asc','sort-desc'));
            header.classList.add('sort-' + dir);

            const rows = Array.from(document.querySelectorAll('#dataTable tbody tr.data-row'));
            rows.sort((a,b) => {
                const aText = a.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                const bText = b.querySelector(`td[data-column="${col}"]`)?.innerText ?? '';
                return dir === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });
            rows.forEach(r => document.querySelector('#dataTable tbody').appendChild(r));
        });
    });
});

// Search/Filter
function filterTable() {
    const input = document.getElementById('searchInput').value.toUpperCase();
    const selectedColumn = document.querySelector('input[name="searchColumn"]:checked').value;
    const rows = document.querySelectorAll('#dataTable tbody tr.data-row');

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
