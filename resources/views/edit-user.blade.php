@extends('layouts.master')

@section('title', 'Edit User')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-people fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Edit User</h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        <div class="row gx-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Update User Information</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
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

                        <form method="POST" action="{{ route('users.update', $user->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="row gx-4 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control">
                                    <small class="text-muted">Leave blank to keep current password</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>

                                {{-- Permissions --}}
                                <div class="col-md-12 mt-3">
                                    <label class="form-label">Access Permissions</label>
                                    @php
                                        $permissions = [
                                            'dashboard' => [],
                                            'book-tracking' => [],
                                            'booking' => [
                                                'booking.domestic' => 'Domestic',
                                                'booking.export' => 'Export',
                                                'booking.import' => 'Import',
                                                'booking.cross-border' => 'Cross Border',
                                                'booking.bulk-attachments' => 'Bulk Booking Attachments',
                                            ],
                                            'label-print' => [
                                                'label-print.single' => 'Single Label',
                                                'label-print.bulk' => 'Bulk Label',
                                                'label-print.pdo-single' => 'POD Single Label',
                                                'label-print.pdo-bulk' => 'POD Bulk Label',
                                                'label-print.sticker' => 'Sticker Label',
                                                'label-print.undertaking' => 'Undertaking Label',
                                            ],
                                            'operation' => [
                                                'operation.3pl-booking' => '3PL Booking',
                                                'operation.3pl-upload' => '3PL Bulk Upload',
                                                'operation.scanning.arrival' => 'Arrival Scan',
                                                'operation.scanning.delivery' => 'Out For Delivery',
                                                'operation.assigning' => 'Assigning Counter Partner',
                                                'operation.edit-weight' => 'Edit Dimensional Weight',
                                                'operation.shipment-status' => 'Shipment Status',
                                                'operation.bulk-status' => 'Bulk Booking Status',
                                            ],
                                            'reports' => [
                                                'reports.pending' => 'Pending Shipments',
                                                'reports.booking-edit' => 'Booking Edit',
                                                'reports.search-data' => 'Search Data',
                                                'reports.booking-void' => 'Booking Void',
                                                'reports.analysis' => 'Booking Analysis',
                                                'reports.sales-funnel' => 'Sales Funnel',
                                                'reports.manifest' => 'Manifest P/L',
                                                'reports.void-booking' => 'Void Booking',
                                                'reports.attachments' => 'Booking Attachments',
                                            ],
                                            'financials' => [
                                                'financials.shipment-cost' => 'Shipment Costing',
                                                'financials.invoicing' => 'Invoicing',
                                                'financials.dashboard' => 'Financial Dashboard',
                                                'financials.shipment-sale' => 'Shipment Sale',
                                            ],
                                            'master-setup' => [
                                                'master-setup.city' => 'Add City',
                                                'master-setup.customer' => 'Add Customer',
                                                'master-setup.user' => 'Add User',
                                            ],
                                        ];
                                        $oldPermissions = old('permissions', $user->permissions ?? []);
                                    @endphp

                                    <div class="permission-tree">
                                        @foreach($permissions as $parent => $children)
                                            <div class="mb-3 border p-2 rounded">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input parent-perm" id="perm_{{ $parent }}" name="permissions[]" value="{{ $parent }}" {{ in_array($parent, $oldPermissions) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="perm_{{ $parent }}">{{ ucfirst(str_replace('-', ' ', $parent)) }}</label>
                                                </div>

                                                @if(!empty($children))
                                                    <div class="ms-4 mt-2">
                                                        @foreach($children as $childValue => $childLabel)
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input child-perm child-of-{{ $parent }}" id="perm_{{ str_replace('.', '_', $childValue) }}" name="permissions[]" value="{{ $childValue }}" {{ in_array($childValue, $oldPermissions) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="perm_{{ str_replace('.', '_', $childValue) }}">{{ $childLabel }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <button type="submit" class="btn btn-primary">Update User</button>
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.permission-tree .form-check-label { cursor: pointer; }
.permission-tree .border { background-color: #fafafa; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Parent checkbox logic
    document.querySelectorAll('.parent-perm').forEach(parent => {
        parent.addEventListener('change', function() {
            const parentKey = this.value;
            const children = document.querySelectorAll('.child-of-' + parentKey);
            children.forEach(child => child.checked = this.checked);
        });
    });

    // Child checkbox logic
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
