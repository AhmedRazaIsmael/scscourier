@extends('layouts.master')
@section('title', 'Customer')

@section('content')

<div class="app-container">
    <div class="app-hero-header d-flex align-items-center flex-wrap mb-4">
        <div class="d-flex align-items-center me-3">
            <div class="icon-box md border bg-white rounded-5 me-2">
                <i class="bi bi-person fs-5 text-primary"></i>
            </div>
            <div><h2 class="mb-1">Customer Management</h2></div>
        </div>
        <a href="{{ route('customer.create') }}" class="btn btn-primary add-customer-btn mt-2 mt-md-0 ms-md-auto">+ Add Customer</a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title">Customer List</h5></div>
        <div class="card-body">
            <!-- Table Controls -->
            <div class="table-controls">
                <div class="search-group-container">
                    <button class="search-options-button" id="searchOptionsButton">🔍</button>
                    <input type="text" id="searchInput" placeholder="Search customers..." class="redesigned-search-input">
                    <div class="search-options-menu" id="searchOptionsMenu">
                        <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                        <label><input type="radio" name="searchColumn" value="code"> Customer Code</label>
                        <label><input type="radio" name="searchColumn" value="name"> Name</label>
                        <label><input type="radio" name="searchColumn" value="email"> Email</label>
                        <label><input type="radio" name="searchColumn" value="phone"> Phone</label>
                        <label><input type="radio" name="searchColumn" value="city"> City</label>
                        <label><input type="radio" name="searchColumn" value="country"> Country</label>
                    </div>
                    <button class="go-button" onclick="filterTable()">Go</button>
                </div>

                <div class="right-controls d-flex align-items-center gap-2">
                    {{-- ✅ Include main actions dropdown --}}
                @include('layouts.actions-dropdown', ['downloadRoute' => route('customer.download', ['format' => 'xlsx'])])

    
                    <button class="reset-button btn btn-outline-secondary" onclick="clearSearchAndReset()">Reset</button>
                </div>
            </div>

            {{-- 🧮 Aggregate / Compute / Control Break Summary --}}
            @if(!empty($aggregateResult) || !empty($computeExpression) || !empty($controlBreak))
                <div class="alert alert-info mb-3">
                    <h6 class="mb-2"><i class="bi bi-bar-chart"></i> Grid Summary</h6>
                
                    {{-- ✅ Aggregate Info --}}
                    @if(!empty($aggregateResult))
                        <p>
                            <strong>Applied Function:</strong>
                            {{ $appliedFunction ?? strtoupper(request('aggregate_function')) ?? 'N/A' }}
                            @if(isset($appliedFunction) && in_array(strtolower(request('aggregate_function')), ['sum', 'avg']) && strtolower($appliedFunction) === 'count')
                                <span class="text-muted">(auto-converted for non-numeric column)</span>
                            @endif
                            <br>
                            <strong>Result:</strong> {{ $aggregateResult }}
                        </p>
                    @endif
                    
                    {{-- 🧠 Custom Compute Expression --}}
                    @if(!empty($computeExpression))
                        <p><strong>Computed Expression:</strong> {{ $computeExpression }}</p>
                    @endif
                    
                    {{-- 👥 Control Break --}}
                    @if(!empty($controlBreak))
                        <p><strong>Group By (Control Break):</strong> {{ implode(', ', $controlBreak) }}</p>
                    @endif
                </div>
            @endif


            <!-- Table -->
            <div class="table-container">
                <table class="data-table" id="customerTable">
                    <thead>
                        <tr class="header-row">
                            <th class="header-cell sortable" data-column="code">Customer Code <span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="name">Name<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="email">Email<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="phone">Phone<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="product">Product<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="address">Address<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="ntn">NTN<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="nic">NIC<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="website">Website<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="salesPerson">Sales Person<span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="country">Country <span class="sort-icon"></span></th>
                            <th class="header-cell sortable" data-column="city">City <span class="sort-icon"></span></th>
                            <th class="header-cell">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="customerTableBody">
                        @forelse($customers as $customer)
                            <tr class="data-row">
                                <td class="data-cell" data-column="code">{{ $customer->code }}</td>
                                <td class="data-cell" data-column="name">{{ $customer->customer_name }}</td>
                                <td class="data-cell" data-column="email">{{ $customer->email_1 }}</td>
                                <td class="data-cell" data-column="phone">{{ $customer->contact_no_1 }}</td>
                                <td class="data-cell" data-column="product">{{ $customer->product ?? '-' }}</td>
                                <td class="data-cell" data-column="address">{{ $customer->address_1 ?? '-' }}</td>
                                <td class="data-cell" data-column="ntn">{{ $customer->ntn ?? '-' }}</td>
                                <td class="data-cell" data-column="nic">{{ $customer->nic ?? '-' }}</td>
                                <td class="data-cell" data-column="website">{{ $customer->website ?? '-' }}</td>
                                <td class="data-cell" data-column="salesPerson">{{ $customer->sales_person ?? '-' }}</td>
                                <td class="data-cell" data-column="country">{{ $customer->country->name ?? '-' }}</td>
                                <td class="data-cell" data-column="city">{{ $customer->city->name ?? '-' }}</td>
                                <td class="data-cell">
                                    <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('customer.destroy', $customer->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this customer?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="13" class="text-center text-muted py-3">No customers found</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="table-footer">
                    <span class="total-count">Total: {{ $customers->total() }}</span>
                    {{ $customers->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include dashboard modals --}}
@include('layouts.dashboard-modals', [
    'columns' => ['code','name','email','phone','product','address','ntn','nic','website','salesPerson','country','city'],
    'chartModel' => 'Customer',
    'downloadAction' => route('customer.download', ['format' => 'csv'])
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
@endpush

@endsection
