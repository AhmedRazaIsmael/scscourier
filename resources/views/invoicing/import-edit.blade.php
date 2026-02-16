@extends('layouts.master')

@section('title', 'Edit Import Invoice')

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
    <form method="POST" action="{{ route('invoice.import.update.items', $invoice->id) }}">
        @csrf

        <!-- Invoice Header -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header"><strong>Edit Import Invoice</strong></div>
            <div class="card-body row g-3">
                <div class="col-md-3"><label>Invoice No:</label> <input type="text" class="form-control" value="{{ $invoice->invoice_no }}" readonly></div>
                <div class="col-md-3"><label>Invoice Date:</label> <input type="date" class="form-control" value="{{ $invoice->invoice_date?->format('Y-m-d') }}" readonly></div>
                <div class="col-md-3"><label>Pay Due Date:</label> <input type="date" class="form-control" value="{{ $invoice->pay_due_date?->format('Y-m-d') }}" readonly></div>
                <div class="col-md-3"><label>Pay Mode:</label> <input type="text" class="form-control" value="{{ ucfirst($invoice->pay_mode ?? '-') }}" readonly></div>
                <div class="col-md-6"><label>Customer:</label> <input type="text" class="form-control" value="{{ $invoice->customer->customer_name ?? '-' }}" readonly></div>
                <div class="col-md-12"><label>Remarks:</label> <textarea class="form-control" readonly>{{ $invoice->remarks }}</textarea></div>
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
                <table class="data-table" id="invoice-items-table">
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
                        @foreach($invoice->items as $i => $item)
                        <tr class="data-row">
                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                            <td class="data-cell"><input type="checkbox" name="row_select[]"></td>
                            <td class="data-cell"><input type="text" name="items[{{ $i }}][book_no]" class="form-control form-control-sm" value="{{ $item->book_no }}"></td>
                            <td class="data-cell">
                                <select name="items[{{ $i }}][account_head]" class="form-control form-control-sm" onchange="recalcRow(this.closest('tr'))">
                                    @foreach(['Freight','Export Declaration','Other Cost','DO Charges','Clearance Charges','Transportation'] as $head)
    <option value="{{ $head }}" {{ $item->account_head === $head ? 'selected':'' }}>{{ $head }}</option>
@endforeach
                                </select>
                            </td>
                            <td class="data-cell">
                                <select name="items[{{ $i }}][currency]" class="form-control form-control-sm" onchange="updateCurrencyRate(this)">
                                    <option value="PKR" {{ $item->currency==='PKR'?'selected':'' }}>PKR</option>
                                    <option value="USD" {{ $item->currency==='USD'?'selected':'' }}>USD</option>
                                </select>
                            </td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][currency_rate]" class="form-control form-control-sm" value="{{ $item->currency_rate }}"></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][gross_weight]" class="form-control form-control-sm" value="{{ $item->gross_weight }}"></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][rate]" class="form-control form-control-sm" value="{{ $item->rate }}"></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][amount]" class="form-control form-control-sm" readonly value="{{ $item->amount }}"></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][freight_rate]" class="form-control form-control-sm" value="{{ $item->freight_rate }}"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-end">
                <strong>Gross Total (PKR): </strong> <span id="gross_total">0.00</span>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <div>
                <a href="{{ route('invoice.import') }}" class="btn btn-outline-secondary">Cancel</a>
                <a href="{{ route('invoice.import.print.pdf', $invoice->id) }}" target="_blank" class="btn btn-info">
                    <i class="bi bi-printer"></i> Print Invoice</a>
            </div>
            <button type="submit" class="btn btn-primary">Update Invoice</button>
        </div>
    </form>
</div>

<script>
const currencyRates = { 'PKR':1, 'USD':284 }; // adjust USD rate if needed
let rowIndex = {{ $invoice->items->count() }};

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#invoice-items-body tr.data-row').forEach(row => attachEvents(row));
    recalcTotal();
});

function attachEvents(row){
    row.querySelectorAll('input[name*="[rate]"], input[name*="[freight_rate]"], input[name*="[currency_rate]"]').forEach(input=>{
        input.addEventListener('input', ()=> recalcRow(row));
    });
    row.querySelectorAll('select[name*="[account_head]"], select[name*="[currency]"]').forEach(sel=>{
        sel.addEventListener('change', ()=> recalcRow(row));
    });
}

function recalcRow(row){
    const accountHead = row.querySelector('select[name*="[account_head]"]').value;
    const currencyRate = parseFloat(row.querySelector('input[name*="[currency_rate]"]').value) || 1;

    let amount = 0;
    if(accountHead === 'Freight'){
        const freightRate = parseFloat(row.querySelector('input[name*="[freight_rate]"]').value) || 0;
        amount = freightRate * currencyRate;
    } else {
        const rate = parseFloat(row.querySelector('input[name*="[rate]"]').value) || 0;
        amount = rate * currencyRate;
    }
    row.querySelector('input[name*="[amount]"]').value = amount.toFixed(2);
    recalcTotal();
}

function updateCurrencyRate(sel){
    const row = sel.closest('tr');
    row.querySelector('input[name*="[currency_rate]"]').value = currencyRates[sel.value] || 1;
    recalcRow(row);
}

function recalcTotal(){
    let total = 0;
    document.querySelectorAll('#invoice-items-body input[name*="[amount]"]').forEach(i=>{
        total += parseFloat(i.value) || 0;
    });
    document.getElementById('gross_total').innerText = total.toFixed(2);
}

function addRow(data={}){
    const tbody = document.getElementById('invoice-items-body');
    const row = document.createElement('tr');
    row.classList.add('data-row');

   row.innerHTML = `
    <td class="data-cell"><input type="checkbox" name="row_select[]"></td>
    <td class="data-cell"><input type="text" name="items[${rowIndex}][book_no]" class="form-control form-control-sm" value="${data.book_no||''}"></td>
    <td class="data-cell">
        <select name="items[${rowIndex}][account_head]" class="form-control form-control-sm" onchange="recalcRow(this.closest('tr'))">
            ${['Freight','Export Declaration','Other Cost','DO Charges','Clearance Charges','Transportation']
                .map(head => `<option value="${head}" ${data.account_head===head?'selected':''}>${head}</option>`).join('')}
        </select>
    </td>
    <td class="data-cell">
        <select name="items[${rowIndex}][currency]" class="form-control form-control-sm" onchange="updateCurrencyRate(this)">
            <option value="PKR" ${data.currency==='PKR'?'selected':''}>PKR</option>
            <option value="USD" ${data.currency==='USD'?'selected':''}>USD</option>
        </select>
    </td>
    <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][currency_rate]" class="form-control form-control-sm" value="${data.currency_rate||1}"></td>
    <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][gross_weight]" class="form-control form-control-sm" value="${data.gross_weight||''}"></td>
    <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][rate]" class="form-control form-control-sm" value="${data.rate||''}"></td>
    <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][amount]" class="form-control form-control-sm" readonly value="0.00"></td>
    <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][freight_rate]" class="form-control form-control-sm" value="${data.freight_rate||''}"></td>
`;


    tbody.appendChild(row);
    attachEvents(row);
    recalcRow(row);
    rowIndex++;
}

function removeSelected(){
    document.querySelectorAll('#invoice-items-body input[type="checkbox"]:checked').forEach(cb=>{
        cb.closest('tr').remove();
    });
    recalcTotal();
}

function toggleAll(){
    const master = document.getElementById('select-all');
    document.querySelectorAll('#invoice-items-body input[type="checkbox"]').forEach(cb=>{
        cb.checked = master.checked;
    });
}
</script>
@endsection
