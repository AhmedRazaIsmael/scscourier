@extends('layouts.master')

@section('title', 'Invoice Recovery Detail')

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
<style>
    /* 3PL-style table layout */
    .table-container {
        overflow-x: auto;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .data-table th, .data-table td {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .header-row th {
        background-color: #e9ecef;
        font-weight: 600;
    }
    .data-row:hover {
        background-color: #f1f7ff;
    }
    .search-group-container {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid py-4">

    {{-- Message container --}}
    <div id="messageContainer"></div>

    {{-- Recovery Form --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">
            Invoice Recovery - {{ $invoice->invoice_no }}
        </div>
        <div class="card-body">
            <form id="recoveryForm">
                @csrf
                <input type="hidden" id="invoice_id" value="{{ $invoice->id }}">
                <input type="hidden" id="invoice_type" value="{{ $invoice instanceof \App\Models\ExportInvoice ? 'export' : 'import' }}">
                <input type="hidden" id="total_invoice_amount" value="{{ $invoiceItems->sum('amount') }}">
                <input type="hidden" id="total_recovered_amount" value="{{ $previousRecoveries->sum('recovery_amount') }}">

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label>Invoice No.</label>
                        <input type="text" class="form-control" value="{{ $invoice->invoice_no }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Customer</label>
                        <input type="text" class="form-control" value="{{ $invoice->customer->customer_name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Recovery Person</label>
                        <input type="text" id="recovery_person" class="form-control" placeholder="Enter recovery person" required>
                    </div>
                    <div class="col-md-3">
                        <label>Receiving Path</label>
                        <select id="receiving_path" class="form-select" required>
                            <option value="">--SELECT--</option>
                            <option value="Bank Alhabib">Bank Alhabib</option>
                            <option value="Cash">Cash</option>
                            <option value="Meezan Bank Ltd">Meezan Bank Ltd</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label>Invoice Amount</label>
                        <input type="text" id="invoice_amount" class="form-control" value="{{ $invoiceItems->sum('amount') }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Recovery Amount</label>
                        <input type="number" id="recovery_amount" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Balance</label>
                        <input type="text" id="balance" class="form-control" value="{{ $invoiceItems->sum('amount') - $previousRecoveries->sum('recovery_amount') }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Remarks</label>
                        <input type="text" id="remarks" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Recovery</button>
                    <button type="button" class="btn btn-outline-secondary" id="printInvoiceBtn">🖨️ Print Invoice</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Previous Recoveries Table --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title">Previous Recoveries</h5></div>
        <div class="card-body">

            <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                <div class="search-group-container">
                    <button class="search-options-button" id="searchOptionsButton">🔍</button>
                    <input type="text" id="searchInput" placeholder="Search recoveries..." class="redesigned-search-input">
                    <button class="go-button btn btn-outline-secondary" onclick="filterTable()">Go</button>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr class="header-row">
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Recovery Person</th>
                            <th>Recovery Amount</th>
                            <th>Remarks</th>
                            <th>Inserted By</th>
                            <th>Inserted Date/Time</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($previousRecoveries as $r)
                        <tr class="data-row">
                            <td>{{ $r->invoice_no }}</td>
                            <td>{{ $r->customer_name }}</td>
                            <td>{{ $r->recovery_person }}</td>
                            <td>{{ number_format($r->recovery_amount, 2) }}</td>
                            <td>{{ $r->remarks }}</td>
                            <td>{{ \App\Models\User::find($r->insert_by)->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d-M-Y H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No previous recoveries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
document.getElementById('printInvoiceBtn').addEventListener('click', function() {
    const invoiceNo = "{{ $invoice->invoice_no }}";
    const customerName = "{{ $invoice->customer->customer_name ?? '-' }}";
    const invoiceAmount = parseFloat(document.getElementById('invoice_amount').value).toFixed(2);
    const totalRecovered = parseFloat(document.getElementById('total_recovered_amount').value).toFixed(2);
    const balance = (invoiceAmount - totalRecovered).toFixed(2);

    // Grab latest recovery info from form
    const recoveryPerson = document.getElementById('recovery_person').value || '-';
    const receivingPath = document.getElementById('receiving_path').value || '-';
    const recoveryAmount = parseFloat(document.getElementById('recovery_amount').value || 0).toFixed(2);
    const remarks = document.getElementById('remarks').value || '-';

    const printWindow = window.open('', '', 'width=900,height=700');

    printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invoice Recovery - ${invoiceNo}</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; color: #000; }
            .page { width: 100%; box-sizing: border-box; page-break-inside: avoid; }
            .box { border: 1px solid #000; padding: 10px; margin-bottom: 15px; position: relative; }
            .box-title { font-weight: bold; background: #000; color: #fff; text-align: center; padding: 5px; position: absolute; top: 0; left: 0; width: 100%; }
            table { width: 100%; border-collapse: collapse; margin-top: 25px; }
            th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; font-size: 12px; }
            th { background: #f2f2f2; }
            .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
            .header img { height: 70px; }
            .totals { margin-top: 15px; float: right; }
            .totals td { font-weight: bold; padding: 4px 8px; }
        </style>
    </head>
    <body>
        <div class="page">

            <!-- Header -->
            <div class="header">
                <div>
                    <img src="{{ asset('dashboard-assets/images/logo3.png') }}" alt="Logo">
                </div>
                <div style="font-size:12px; line-height:1.3;">
                    <strong>ABC EXPRESS</strong><br>
                    North Nazimabad<br>
                    Karachi<br>
                    Email: accounts@airborncx.com<br>
                    Phone: +92-339-2472676
                </div>
            </div>

            <h4 style="text-align:center; margin-top:20px;">Invoice Recovery</h4>

            <!-- Invoice Details -->
            <div class="box">
                <div class="box-title">Invoice Details</div>
                <table>
                    <tr><td>Invoice No:</td><td>${invoiceNo}</td></tr>
                    <tr><td>Customer:</td><td>${customerName}</td></tr>
                    <tr><td>Invoice Amount:</td><td>${invoiceAmount}</td></tr>
                    <tr><td>Total Recovered:</td><td>${totalRecovered}</td></tr>
                    <tr><td>Balance:</td><td>${balance}</td></tr>
                </table>
            </div>


            <!-- Optionally, previous recoveries table -->
            <div class="box">
                <div class="box-title">Previous Recoveries</div>
                <table>
                    <thead>
                        <tr>
                            <th>Recovery Person</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previousRecoveries as $r)
                        <tr>
                            <td>{{ $r->recovery_person }}</td>
                            <td>{{ number_format($r->recovery_amount,2) }}</td>
                            <td>{{ $r->remarks }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d-M-Y H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </body>
    </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
});

document.addEventListener('DOMContentLoaded', function(){

    const totalInvoice = parseFloat(document.getElementById('total_invoice_amount').value);
    let totalRecovered = parseFloat(document.getElementById('total_recovered_amount').value);
    const recoveryInput = document.getElementById('recovery_amount');
    const balanceInput = document.getElementById('balance');
    const tableBody = document.getElementById('tableBody');
    const form = document.getElementById('recoveryForm');
    const messageContainer = document.getElementById('messageContainer');

    // Update balance dynamically
    recoveryInput.addEventListener('input', function() {
        const recoveryAmount = parseFloat(this.value) || 0;
        balanceInput.value = (totalInvoice - totalRecovered - recoveryAmount).toFixed(2);
    });

    form.addEventListener('submit', function(e){
        e.preventDefault();

        const recoveryAmount = parseFloat(recoveryInput.value) || 0;
        const recoveryPerson = document.getElementById('recovery_person').value;
        const receivingPath = document.getElementById('receiving_path').value;
        const remarks = document.getElementById('remarks').value;
        const invoiceId = document.getElementById('invoice_id').value;
        const invoiceType = document.getElementById('invoice_type').value;

        if(totalRecovered >= totalInvoice){
            alert('Invoice fully recovered. No further recovery allowed.');
            recoveryInput.disabled = true;
            return;
        }
        if(totalRecovered + recoveryAmount > totalInvoice){
            alert('Recovery exceeds invoice total. Please enter a valid amount.');
            return;
        }

        fetch("{{ route('invoice.recovery.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                invoice_ref_id: invoiceId,
                invoice_type: invoiceType,
                recovery_person: recoveryPerson,
                receiving_path: receivingPath,
                recovery_amount: recoveryAmount,
                remarks: remarks
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                // ✅ Show success message like session('success')
                messageContainer.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Recovery saved successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                // Reset form
                form.reset();

                // Update totals
                totalRecovered += recoveryAmount;
                balanceInput.value = (totalInvoice - totalRecovered).toFixed(2);

                // Disable input if fully recovered
                if(totalRecovered >= totalInvoice){
                    recoveryInput.disabled = true;
                }

                // Prepend new recovery to table
                const newRow = document.createElement('tr');
                newRow.classList.add('data-row');
                newRow.innerHTML = `
                    <td>${data.recovery.invoice_no}</td>
                    <td>${data.recovery.customer_name}</td>
                    <td>${data.recovery.recovery_person}</td>
                    <td>${parseFloat(data.recovery.recovery_amount).toFixed(2)}</td>
                    <td>${data.recovery.remarks || '-'}</td>
                    <td>${data.recovery.inserted_by_name || '-'}</td>
                    <td>${data.recovery.created_at}</td>
                `;
                tableBody.prepend(newRow);
                tableBody.parentElement.scrollIntoView({ behavior: 'smooth' });

            } else {
                messageContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${data.message || 'Error occurred. Please try again.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
        })
        .catch(err => console.error(err));
    });

    // Table search/filter
    document.getElementById('searchOptionsButton').addEventListener('click', function() {
        const menu = document.getElementById('searchOptionsMenu');
        if(menu) menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });

    window.filterTable = function() {
        const filter = document.getElementById('searchInput').value.toUpperCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toUpperCase().includes(filter) ? '' : 'none';
        });
    };
});
</script>
@endpush
