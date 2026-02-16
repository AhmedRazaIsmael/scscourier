@extends('layouts.master')

@section('title', 'User Management')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-people fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">User Management</h2>
            </div>
        </div>
    </div>

    <div class="app-body">

        {{-- =================== FORM =================== --}}
        <div class="row gx-4 mb-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Add User</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="m-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf
                            <div class="row gx-4 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>

                               <div class="col-md-12 mt-4">
    <label class="form-label fw-bold fs-5 mb-3 d-block">
        <i class="bi bi-shield-lock me-2 text-primary"></i> Access Permissions
    </label>

    @php
        $permissions = [
            'dashboard' => [],
            'book-tracking' => [],
            'booking' => [
                'booking.domestic' => 'Domestic',
                'booking.export' => 'Export',
                'booking.import' => 'Import',
                'booking.cross-border' => 'Cross Border',
                'booking.bulk-attachments' => 'Bulk Booking Attachments'
            ],
            'label-print' => [
                'label-print.single' => 'Single Label',
                'label-print.bulk' => 'Bulk Label',
                'label-print.pdo-single' => 'POD Single Label',
                'label-print.pdo-bulk' => 'POD Bulk Label',
                'label-print.sticker' => 'Sticker Label',
                'label-print.undertaking' => 'Undertaking Label'
            ],
            'shipments' => [
                'shipments.all-shipment' => 'All Shipments',
            ],
            'operation' => [
                'operation.3pl-booking' => '3PL Booking',
                'operation.3pl-upload' => '3PL Bulk Upload',
                'operation.scanning.arrival' => 'Arrival Scan',
                'operation.scanning.delivery' => 'Out For Delivery',
                'operation.assigning' => 'Assign Counter Partner',
                'operation.edit-weight' => 'Edit Dimensional Weight',
                'operation.shipment-status' => 'Shipment Status',
                'operation.bulk-status' => 'Bulk Booking Status'
            ],
            'reports' => [
                'reports.pending' => 'Pending Shipments',
                'reports.booking-edit' => 'Booking Edit',
                'reports.search-data' => 'Search Data',
                'reports.booking-void' => 'Booking Void',
                'reports.analysis' => 'Booking Analysis',
                'reports.sales-funnel' => 'Sales Funnel',
                'reports.manifest' => 'Manifest P/L',
                'reports.attachments' => 'Booking Attachments'
            ],
            'financials' => [
                'financials.shipment-cost' => 'Shipment Costing',
                'financials.invoicing' => 'Invoicing',
                'financials.dashboard' => 'Financial Dashboard',
                'financials.shipment-sale' => 'Shipment Sale'
            ],
            'master-setup' => [
                'master-setup.city' => 'Add City',
                'master-setup.customer' => 'Add Customer',
                'master-setup.user' => 'Add User'
            ],
        ];
        $oldPermissions = old('permissions', []);
    @endphp

    <div class="accordion permission-accordion" id="permissionAccordion">
        @foreach($permissions as $parent => $children)
            <div class="accordion-item mb-2 border-0 shadow-sm">
                <h2 class="accordion-header" id="heading_{{ $parent }}">
                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $parent }}" aria-expanded="false">
                        <input type="checkbox" class="form-check-input me-2 parent-perm" id="perm_{{ $parent }}" name="permissions[]" value="{{ $parent }}" {{ in_array($parent, $oldPermissions) ? 'checked' : '' }}>
                        <strong>{{ ucfirst(str_replace('-', ' ', $parent)) }}</strong>
                    </button>
                </h2>
                <div id="collapse_{{ $parent }}" class="accordion-collapse collapse" data-bs-parent="#permissionAccordion">
                    <div class="accordion-body py-2 ps-5">
                        @if(!empty($children))
                            <div class="row">
                                @foreach($children as $childValue => $childLabel)
                                    <div class="col-md-4 col-sm-6 mb-1">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input child-perm child-of-{{ $parent }}" id="perm_{{ str_replace('.', '_', $childValue) }}" name="permissions[]" value="{{ $childValue }}" {{ in_array($childValue, $oldPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ str_replace('.', '_', $childValue) }}">{{ $childLabel }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">No sub-permissions available</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
 
                            </div>
                            <button type="submit" class="btn btn-primary">Create User</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== DATA TABLE (3PL STYLE) =================== --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Users List</h5>
            </div>
            <div class="card-body">
                <!-- Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search users..." class="redesigned-search-input">
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="name"> Name</label>
                            <label><input type="radio" name="searchColumn" value="email"> Email</label>
                            <label><input type="radio" name="searchColumn" value="permissions"> Permissions</label>
                        </div>
                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>
                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => route('users.download')])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="name">Name <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="email">Email <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="permissions">Permissions <span class="sort-icon"></span></th>
                                <th class="header-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @forelse($users as $user)
                                <tr class="data-row">
                                    <td class="data-cell" data-column="name">{{ $user->name }}</td>
                                    <td class="data-cell" data-column="email">{{ $user->email }}</td>
                                    <td class="data-cell" data-column="permissions">{{ implode(', ', $user->permissions ?? []) }}</td>
                                    <td class="data-cell">
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div>
                        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
<style>
.permission-accordion .accordion-button {
    background-color: #f9fafb;
    border-radius: 6px;
    font-weight: 500;
    color: #333;
}
.permission-accordion .accordion-button.collapsed {
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
}
.permission-accordion .accordion-item {
    border-radius: 6px;
}
.permission-accordion .accordion-body {
    background: #fcfcfc;
    border-left: 3px solid #007bff1a;
}
.permission-accordion .form-check-label {
    cursor: pointer;
}
.permission-accordion input[type=checkbox] {
    transform: scale(1.1);
    margin-right: 6px;
}
</style>
@endpush


@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Parent-child permission logic
    document.querySelectorAll('.parent-perm').forEach(parent => {
        parent.addEventListener('change', function() {
            const parentKey = this.value;
            const children = document.querySelectorAll('.child-of-' + parentKey);
            children.forEach(child => child.checked = this.checked);
        });
    });

    document.querySelectorAll('.child-perm').forEach(child => {
        child.addEventListener('change', function() {
            const parentKey = this.className.match(/child-of-([a-zA-Z0-9\-_]+)/)[1];
            const parent = document.querySelector('#perm_' + parentKey);
            const anyChecked = Array.from(document.querySelectorAll('.child-of-' + parentKey)).some(ch => ch.checked);
            parent.checked = anyChecked;
        });
    });
});
</script>
@endpush
@endsection
