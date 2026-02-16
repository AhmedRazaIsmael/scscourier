@extends('layouts.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.table-container { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow:auto; max-height:80vh; position:relative; }
.data-table { width:100%; border-collapse:collapse; }
.header-row { background:#fff; border-bottom:2px solid #ddd; }
.header-cell { text-align:center; padding:10px 12px; font-weight:600; color:#333; background-color:#f9fafb; }
.data-row { border-bottom:1px solid #eee; transition:0.2s; }
.data-row:hover { background:#f5f5f5; }
.data-cell { padding:8px 10px; text-align:center; color:#555; }
.table-controls { display:flex; justify-content:space-between; align-items:center; padding:10px 15px; background:#f0f2f5; border-bottom:1px solid #ddd; }
</style>

<div class="app-container py-4">
    <form action="{{ route('invoice.export.store') }}" method="POST">
        @csrf

        <!-- Invoice Header -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <strong><i class="bi bi-file-earmark-arrow-up"></i> Export Invoice</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Invoice No.</label>
                        <input type="text" name="invoice_no" class="form-control" value="{{ $invoice_no }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Date *</label>
                        <input type="date" name="invoice_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pay Due Date *</label>
                        <input type="date" name="pay_due_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pay Mode</label>
                        <select name="pay_mode" class="form-select">
                            <option value="">--SELECT--</option>
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer *</label>
                        <select id="customer-select" name="customer_id" class="form-select" required>
                            <option value="">--Select--</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="table-controls">
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">Add Row</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeSelected()">Remove Selected</button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="items-table">
                    <thead>
                        <tr class="header-row">
                            <th class="header-cell"><input type="checkbox" id="select-all" onclick="toggleAll()"></th>
                            <th class="header-cell">Book No.</th>
                            <th class="header-cell">Account Head</th>
                            <th class="header-cell">Currency</th>
                            <th class="header-cell">Currency Rate</th>
                            <th class="header-cell">Gross Weight</th>
                            <th class="header-cell">Rate</th>
                            <th class="header-cell">Amount</th>
                            <th class="header-cell">Freight Rate</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body">
                        <!-- Dynamic rows -->
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-end">
                <strong>Gross Total (PKR): </strong> <span id="gross_total">0.00</span>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('invoice.export') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </form>
</div>

<script>
let rowIndex = 0;
let customerBookings = [];
const accountHeads = ['Freight','Export Declaration','Other Cost','DO Charges','Clearance Charges','Transportation'];
const currencyRates = { 'PKR': 1, 'USD': 284 };

// Add a new row
function addRow(data={}) {
    const tbody = document.getElementById('invoice-items-body');
    const row = document.createElement('tr');
    row.classList.add('data-row');

    let bookNoCell = '<input type="text" class="form-control form-control-sm">';
    if(customerBookings.length > 0){
        bookNoCell = `<select name="items[${rowIndex}][book_no]" class="form-control form-control-sm" onchange="onBookSelect(this)">
            <option value="">--Select Book No--</option>
            ${customerBookings.map(b => `<option value="${b.bookNo}" ${data.book_no === b.bookNo ? 'selected':''}>${b.bookNo}</option>`).join('')}
        </select>`;
    }

    row.innerHTML = `
        <td class="data-cell"><input type="checkbox" name="row_select[]"></td>
        <td class="data-cell">${bookNoCell}</td>
        <td class="data-cell">
            <select name="items[${rowIndex}][account_head]" class="form-control form-control-sm" onchange="recalcRow(this.closest('tr'))">
                <option value="">--Select Account Head--</option>
                ${accountHeads.map(head => `<option value="${head}" ${data.account_head === head ? 'selected':''}>${head}</option>`).join('')}
            </select>
        </td>
        <td class="data-cell">
            <select name="items[${rowIndex}][currency]" class="form-control form-control-sm" onchange="updateCurrencyRate(this)">
                <option value="PKR" ${data.currency==='PKR'?'selected':''}>PKR</option>
                <option value="USD" ${data.currency==='USD'?'selected':''}>USD</option>
            </select>
        </td>
        <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][currency_rate]" class="form-control form-control-sm" value="${data.currency_rate || 1}"></td>
        <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][gross_weight]" class="form-control form-control-sm" value="${data.gross_weight || data.weight || ''}"></td>
        <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][rate]" class="form-control form-control-sm" value="${data.rate||''}"></td>
        <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][amount]" class="form-control form-control-sm" readonly value="${data.amount||''}"></td>
        <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][freight_rate]" class="form-control form-control-sm" value="${data.freight_rate||''}"></td>
    `;
    tbody.appendChild(row);
    attachEvents(row);
    rowIndex++;
}

// Attach input events
function attachEvents(row){
    row.querySelectorAll('input[name*="gross_weight"],input[name*="rate"],input[name*="freight_rate"]').forEach(input=>{
        input.addEventListener('input',()=>recalcRow(row));
    });
    row.querySelector('select[name*="[account_head]"]').addEventListener('change',()=>recalcRow(row));
}

// Fetch bookings on customer select
document.getElementById('customer-select').addEventListener('change', function(){
    const customerId = this.value;
    if(!customerId) return;

    fetch(`/customer-export-bookings/${customerId}`)
        .then(res=>res.json())
        .then(data=>{
            customerBookings = data;
            const tbody = document.getElementById('invoice-items-body');
            tbody.innerHTML = '';
            rowIndex = 0;
            addRow();
        });
});

// Populate row when Book No selected
function onBookSelect(sel){
    const row = sel.closest('tr');
    const bookNo = sel.value;
    const selected = customerBookings.find(b => b.bookNo === bookNo);
    if(!selected) return;

    row.querySelector('select[name*="[account_head]"]').value = selected.account_head || '';
    row.querySelector('input[name*="[currency_rate]"]').value = currencyRates[selected.currency] || 1;
    row.querySelector('select[name*="[currency]"]').value = selected.currency || 'PKR';
    row.querySelector('input[name*="[gross_weight]"]').value = selected.gross_weight || selected.weight || '';
    row.querySelector('input[name*="[rate]"]').value = selected.rate || '';
    row.querySelector('input[name*="[freight_rate]"]').value = selected.freight_rate || '';

    recalcRow(row);
}

// Update currency rate
function updateCurrencyRate(sel){
    const row = sel.closest('tr');
    row.querySelector('input[name*="[currency_rate]"]').value = currencyRates[sel.value] || 1;
    recalcRow(row);
}

// Recalculate row amount
function recalcRow(row){
    const accountHead = (row.querySelector('select[name*="[account_head]"]').value || '').trim().toLowerCase();
    const currencyRate = parseFloat(row.querySelector('input[name*="[currency_rate]"]').value) || 1;
    let amount = 0;

    if(accountHead === 'freight'){
        const freightRate = parseFloat(row.querySelector('input[name*="[freight_rate]"]').value) || 0;
        amount = freightRate * currencyRate;
    } else {
        const rate = parseFloat(row.querySelector('input[name*="[rate]"]').value) || 0;
        amount = rate * currencyRate;
    }

    row.querySelector('input[name*="[amount]"]').value = amount.toFixed(2);
    recalcTotal();
}

// Recalculate total
function recalcTotal(){
    let total = 0;
    document.querySelectorAll('#invoice-items-body input[name*="[amount]"]').forEach(i=>{
        total += parseFloat(i.value)||0;
    });
    document.getElementById('gross_total').innerText = total.toFixed(2);
}

// Remove selected rows
function removeSelected(){
    document.querySelectorAll('#invoice-items-body input[type="checkbox"]:checked').forEach(cb=>cb.closest('tr').remove());
    recalcTotal();
}

// Toggle all
function toggleAll(){
    const master = document.getElementById('select-all');
    document.querySelectorAll('#invoice-items-body input[type="checkbox"]').forEach(cb=>cb.checked=master.checked);
    recalcTotal();
}

// Initialize with one empty row
addRow();
</script>
@endsection
