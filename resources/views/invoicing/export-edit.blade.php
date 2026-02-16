@extends('layouts.master')

@section('title', 'Edit Export Invoice')

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
.table-footer { display:flex; justify-content:flex-end; padding:8px 12px; background:#f7f7f7; border-top:1px solid #ddd; }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Edit Export Invoice</h4>
        <a href="{{ route('invoice.export') }}" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left"></i> Back</a>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body row g-3 px-3">
            <div class="col-md-3"><strong>Invoice No:</strong> {{ $invoice->invoice_no }}</div>
            <div class="col-md-3"><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</div>
            <div class="col-md-3"><strong>Pay Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->pay_due_date)->format('d/m/Y') }}</div>
            <div class="col-md-3"><strong>Pay Mode:</strong> {{ ucfirst($invoice->pay_mode ?? '-') }}</div>
            <div class="col-md-6"><strong>Customer:</strong> {{ $invoice->customer->customer_name }}</div>
            <div class="col-md-12"><strong>Remarks:</strong> {{ $invoice->remarks }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('invoice.export.update.items', $invoice->id) }}">
        @csrf
        <div class="card shadow-sm border-0">
            <div class="table-controls">
                <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">Add Row</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeSelected()">Remove Selected</button>
            </div>

            <div class="table-container">
                <table class="data-table" id="invoice-items-table">
                    <thead>
                        <tr class="header-row">
                            <th class="header-cell"><input type="checkbox" id="select-all" onclick="toggleAll()"></th>
                            <th class="header-cell">Book No.</th>
                            <th class="header-cell">Consignee</th>
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
                            <td class="data-cell"><input type="text" name="items[{{ $i }}][book_no]" value="{{ $item->book_no }}" class="form-control form-control-sm"></td>
                            <td class="data-cell"><input type="text" name="items[{{ $i }}][consignee]" value="{{ $item->consignee }}" class="form-control form-control-sm"></td>
                            <td class="data-cell">
                                <select name="items[{{ $i }}][account_head]" class="form-control form-control-sm">
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
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][currency_rate]" value="{{ $item->currency_rate ?? ($item->currency==='USD'?284:1) }}" class="form-control form-control-sm"></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][gross_weight]" value="{{ $item->gross_weight }}" class="form-control form-control-sm"></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][rate]" value="{{ $item->rate }}" class="form-control form-control-sm"></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][amount]" value="{{ $item->amount }}" class="form-control form-control-sm" readonly></td>
                            <td class="data-cell"><input type="number" step="0.01" name="items[{{ $i }}][freight_rate]" value="{{ $item->freight_rate }}" class="form-control form-control-sm"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <strong>Gross Total (PKR): </strong> <span id="gross_total">0.00</span>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <div>
                <a href="{{ route('invoice.export') }}" class="btn btn-outline-secondary">Cancel</a>
                <a href="{{ route('invoice.export.print.pdf', $invoice->id) }}" target="_blank" class="btn btn-info">
                    <i class="bi bi-printer"></i> Print Invoice
                </a>
            </div>
            <button type="submit" class="btn btn-primary">Update Invoice</button>
        </div>
    </form>
</div>

<script>
let rowIndex = {{ $invoice->items->count() }};
const currencyRates = { 'PKR': 1, 'USD': 284 };

function attachEvents(row){
    row.querySelectorAll('input[name*="[rate]"], input[name*="[freight_rate]"], select[name*="[account_head]"], select[name*="[currency]"], input[name*="[currency_rate]"]').forEach(input=>{
        input.addEventListener('input', ()=> recalcRow(row));
        input.addEventListener('change', ()=> recalcRow(row));
    });
}

function recalcRow(row){
    const accountHead = row.querySelector('select[name*="[account_head]"]').value;
    const currencyRate = parseFloat(row.querySelector('input[name*="[currency_rate]"]').value) || 1;

    let amount = 0;

    const rate = parseFloat(row.querySelector('input[name*="[rate]"]').value) || 0;
    const freightRate = parseFloat(row.querySelector('input[name*="[freight_rate]"]').value) || 0;

    // If account head is Freight → use freightRate
    if(accountHead === 'Freight'){
        amount = freightRate * currencyRate;
    } 
    else {
        amount = rate * currencyRate;
    }

    row.querySelector('input[name*="[amount]"]').value = amount.toFixed(2);
    recalcTotal();
}


function recalcTotal(){
    let total = 0;
    document.querySelectorAll('#invoice-items-body input[name*="[amount]"]').forEach(i=>{
        total += parseFloat(i.value)||0;
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
        <td class="data-cell"><input type="text" name="items[${rowIndex}][consignee]" class="form-control form-control-sm" value="${data.consignee||''}"></td>
        <td class="data-cell">
            <select name="items[${rowIndex}][account_head]" class="form-control form-control-sm" onchange="recalcRow(this.closest('tr'))">
                ${['Freight','Customs','Fuel','Sales Commission','Export Declaration','Other Cost','DO Charges','Clearance Charges','Transportation']
                    .map(head => `<option value="${head}" ${data.account_head===head?'selected':''}>${head}</option>`).join('')}
            </select>
        </td>
        <td class="data-cell">
            <select name="items[${rowIndex}][currency]" class="form-control form-control-sm" onchange="updateCurrencyRate(this)">
                <option value="PKR" ${data.currency==='PKR'?'selected':''}>PKR</option>
                <option value="USD" ${data.currency==='USD'?'selected':''}>USD</option>
            </select>
        </td>
        <td class="data-cell"><input type="number" step="0.01" name="items[${rowIndex}][currency_rate]" class="form-control form-control-sm" value="${data.currency_rate || (data.currency==='USD'?284:1)}"></td>
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
    document.querySelectorAll('#invoice-items-body input[type="checkbox"]').forEach(cb=>cb.checked=master.checked);
}

function updateCurrencyRate(sel){
    const row = sel.closest('tr');
    const rateInput = row.querySelector('input[name*="[currency_rate]"]');
    rateInput.value = currencyRates[sel.value] || 1;
    recalcRow(row);
}

document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('#invoice-items-body tr.data-row').forEach(row => attachEvents(row));
    recalcTotal();
});
</script>
@endsection
