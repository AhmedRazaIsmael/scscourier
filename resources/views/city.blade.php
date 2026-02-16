@extends('layouts.master')
@section('title', 'City')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-geo-alt fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">City Management</h2></div>
        </div>
        <a href="{{ route('city.create') }}" class="btn btn-primary ms-auto">+ Add City</a>
    </div>

    <div class="app-body">
        {{-- =================== DATA TABLE =================== --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">City List</h5></div>
            <div class="card-body">
                <!-- Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search cities..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="code"> City Code</label>
                            <label><input type="radio" name="searchColumn" value="name"> City</label>
                            <label><input type="radio" name="searchColumn" value="state"> State</label>
                            <label><input type="radio" name="searchColumn" value="country"> Country</label>
                            <label><input type="radio" name="searchColumn" value="province"> Province</label>
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => route('city.download')])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <table class="data-table" id="cityTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="code">City Code <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="name">City <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="country">Country <span class="sort-icon"></span></th>
                                {{-- <th class="header-cell sortable" data-column="province">Province <span class="sort-icon"></span></th> --}}
                                <th class="header-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cities as $city)
                            <tr class="data-row">
                                <td class="data-cell" data-column="code">{{ $city->code }}</td>
                                <td class="data-cell" data-column="name">{{ $city->name }}</td>
                                <td class="data-cell" data-column="country">{{ $city->country->name ?? '-' }}</td>
                                {{-- <td class="data-cell" data-column="province">{{ $city->province ?? '-' }}</td> --}}
                                <td class="data-cell">
                                    <a href="{{ route('city.edit', $city->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('city.destroy', $city->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this city?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No cities found</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="table-footer">
                        <span class="total-count">Total: {{ $cities->total() }}</span>
                        {{ $cities->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include dashboard modals as in 3PL --}}
@include('layouts.dashboard-modals', [
    'columns' => ['code','name','country','state','province'],
    'chartModel' => 'City',
    // 'chartAction' => route('city.chart'),
    // 'filterAction' => route('city.index'),
    // 'rowFilterAction' => route('city.rowFilter'),
    // 'sortAction' => route('city.index'),
    // 'aggregateAction' => route('city.aggregate'),
    // 'computeAction' => route('city.compute'),
    'downloadAction' => route('city.download')
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
@endpush
@endsection
