@extends('layouts.master')
@section('title') {{ 'Booking Analysis' }} @endsection

@section('content')
<div class="page-content">
    <div class="page-container">

        <!-- Header -->
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2 mb-3">
            <div class="flex-grow-1">
                <h4 class="fs-18 text-uppercase fw-bold mb-0">Booking Analysis</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Reports</a></li>
                    <li class="breadcrumb-item active">Booking Analysis</li>
                </ol>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>From Date</label>
                <input type="date" class="form-control" name="fromDate" value="{{ request('fromDate', $fromDate) }}">
            </div>
            <div class="col-md-3">
                <label>To Date</label>
                <input type="date" class="form-control" name="toDate" value="{{ request('toDate', $toDate) }}">
            </div>
            <div class="col-md-3">
                <label>Origin</label>
                <select class="form-select" name="origin">
                    <option value="">-- All --</option>
                    @foreach ($origins ?? [] as $origin)
                        <option value="{{ $origin }}" {{ request('origin') == $origin ? 'selected' : '' }}>{{ $origin }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Destination</label>
                <select class="form-select" name="destination">
                    <option value="">-- All --</option>
                    @foreach ($destinations ?? [] as $destination)
                        <option value="{{ $destination }}" {{ request('destination') == $destination ? 'selected' : '' }}>{{ $destination }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Customer</label>
                <select class="form-select" name="customer_id">
                    <option value="">-- All --</option>
                    @foreach ($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->customer_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Territory</label>
                <select class="form-select" name="territory">
                    <option value="">-- All --</option>
                    @foreach ($territories ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('territory') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Product</label>
                <select class="form-select" name="product">
                    <option value="">-- All --</option>
                    @foreach ($products ?? [] as $product)
                        <option value="{{ $product }}" {{ request('product') == $product ? 'selected' : '' }}>{{ $product }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">Submit</button>
            </div>
        </form>

        <!-- Show Analysis only if filters applied -->
        @if($filtersApplied)
        <div class="row">
            <!-- Product Wise -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>Product Wise Analysis</strong></div>
                    <div class="card-body"><canvas id="productWiseChart"></canvas></div>
                </div>
            </div>
            <!-- Weight Wise -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong>Weight Wise Analysis</strong></div>
                    <div class="card-body"><canvas id="weightWiseChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Daily Line -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><strong>Daily Booking Trend</strong></div>
                    <div class="card-body"><canvas id="dailyChart"></canvas></div>
                </div>
            </div>
        </div>
        @endif

        @include('layouts.footer')
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if($filtersApplied)
<script>
    new Chart(document.getElementById('productWiseChart'), {
        type: 'pie',
        data: {
            labels: ['Import', 'Export'],
            datasets: [{
                data: [{{ $productWise['import'] }}, {{ $productWise['export'] }}],
                backgroundColor: ['#36A2EB', '#FF6384']
            }]
        }
    });

    new Chart(document.getElementById('weightWiseChart'), {
        type: 'pie',
        data: {
            labels: ['Import', 'Export'],
            datasets: [{
                data: [{{ $weightWise['import'] }}, {{ $weightWise['export'] }}],
                backgroundColor: ['#36A2EB', '#FF6384']
            }]
        }
    });

    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyLabels) !!},
            datasets: [{
                label: 'Bookings',
                data: {!! json_encode($dailyCounts) !!},
                borderColor: '#4BC0C0',
                fill: false
            }]
        }
    });
</script>
@endif
@endpush

