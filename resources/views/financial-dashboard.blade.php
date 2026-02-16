@extends('layouts.master')

@section('title', 'Financial Dashboard')

@section('content')
<div class="container-fluid">

    <h1 class="mb-4">Financial Dashboard - {{ $year }}</h1>

    {{-- Filters --}}
    <form method="GET" class="row mb-4">
        <div class="col-md-3">
            <label>From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-3">
            <label>To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-2">
            <label>Product</label>
            <select name="product" class="form-control">
                <option value="">All</option>
                @foreach($products as $p)
                <option value="{{ $p }}" @selected(request('product')==$p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Customer</label>
            <select name="customer" class="form-control">
                <option value="">All</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(request('customer')==$c->id)>{{ $c->customer_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Sales Person</label>
            <select name="sales_person" class="form-control">
                <option value="">All</option>
                @foreach($salesPersons as $id => $name)
                    <option value="{{ $id }}" @selected(request('sales_person') == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12 mt-3">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    {{-- Yearly Charts --}}
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>Yearly Invoice</h5>
                <p>Import: <strong>{{ $yearlyInvoice['import'] }}</strong></p>
                <p>Export: <strong>{{ $yearlyInvoice['export'] }}</strong></p>
                <canvas id="yearlyInvoiceChart"></canvas>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>Yearly Recovery</h5>
                <p>Total: <strong>{{ $yearlyRecovery }}</strong></p>
                <canvas id="yearlyRecoveryChart"></canvas>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>Yearly Expense</h5>
                <p>Total: <strong>{{ $yearlyExpense }}</strong></p>
                <canvas id="yearlyExpenseChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Monthly Charts --}}
    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <div class="card p-3">
                <h5>Monthly Invoice</h5>
                <canvas id="monthlyInvoiceChart"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card p-3">
                <h5>Monthly Recovery & Expense</h5>
                <canvas id="monthlyRecoveryExpenseChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Product, Sales Person, Customer Charts --}}
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>Product Wise</h5>
                <canvas id="productChart"></canvas>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>Sales Person Wise</h5>
                <canvas id="salesPersonChart"></canvas>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>Customer Wise</h5>
                <canvas id="customerChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Yearly Invoice Pie
    new Chart(document.getElementById('yearlyInvoiceChart'), {
        type: 'pie',
        data: {
            labels: ['Import', 'Export'],
            datasets: [{
                label: 'Invoice Amount',
                data: [{{ (float)$yearlyInvoice['import'] }}, {{ (float)$yearlyInvoice['export'] }}],
                backgroundColor: ['#007bff','#28a745']
            }]
        }
    });

    // Yearly Recovery Pie
    new Chart(document.getElementById('yearlyRecoveryChart'), {
        type: 'doughnut',
        data: {
            labels: ['Recovery'],
            datasets: [{
                label: 'Recovery Amount',
                data: [{{ (float)$yearlyRecovery }}],
                backgroundColor: ['#ffc107']
            }]
        }
    });

    // Yearly Expense Pie
    new Chart(document.getElementById('yearlyExpenseChart'), {
        type: 'doughnut',
        data: {
            labels: ['Expense'],
            datasets: [{
                label: 'Expense Amount',
                data: [{{ (float)$yearlyExpense }}],
                backgroundColor: ['#dc3545']
            }]
        }
    });

    // Monthly Invoice Bar
    new Chart(document.getElementById('monthlyInvoiceChart'), {
        type: 'bar',
        data: {
            labels: @json($months->toArray()),
            datasets: [
                {
                    label: 'Import',
                    data: @json(array_values($monthlyImport->toArray())),
                    backgroundColor: '#007bff'
                },
                {
                    label: 'Export',
                    data: @json(array_values($monthlyExport->toArray())),
                    backgroundColor: '#28a745'
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Monthly Recovery & Expense Bar
    new Chart(document.getElementById('monthlyRecoveryExpenseChart'), {
        type: 'bar',
        data: {
            labels: @json($months->toArray()),
            datasets: [
                {
                    label: 'Recovery',
                    data: @json(array_values($monthlyRecovery->toArray())),
                    backgroundColor: '#ffc107'
                },
                {
                    label: 'Expense',
                    data: @json(array_values($monthlyExpense->toArray())),
                    backgroundColor: '#dc3545'
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Product Wise Pie
    new Chart(document.getElementById('productChart'), {
        type: 'pie',
        data: {
            labels: @json(array_keys($productInvoice->toArray())),
            datasets: [{
                label: 'Invoice',
                data: @json(array_values($productInvoice->toArray())),
                backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#fd7e14']
            }]
        }
    });

    // Sales Person Wise Pie
    new Chart(document.getElementById('salesPersonChart'), {
        type: 'pie',
        data: {
            labels: @json(array_keys($salesPersonInvoice->toArray())),
            datasets: [{
                label: 'Invoice',
                data: @json(array_values($salesPersonInvoice->toArray())),
                backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#fd7e14']
            }]
        }
    });

    // Customer Wise Pie
    new Chart(document.getElementById('customerChart'), {
        type: 'pie',
        data: {
            labels: @json(array_keys($customerInvoice->toArray())),
            datasets: [{
                label: 'Invoice',
                data: @json(array_values($customerInvoice->toArray())),
                backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#fd7e14']
            }]
        }
    });
</script>
@endpush
