@extends('layouts.master')
@section('title', 'Create Invoice')

@section('content')
<div class="container mt-4">
    <h3>Create Invoice</h3>

    {{-- ✅ Success / Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('invoice.storeFromBooking') }}">
        @csrf
        <input type="hidden" name="booking_type" value="{{ $booking->bookingType }}">

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Booking No *</label>
                <input type="text" name="book_no_display" class="form-control" value="{{ $booking->bookNo }}" readonly>
                <input type="hidden" name="book_no" value="{{ $booking->bookNo }}">
            </div>
            <div class="col-md-3">
                <label>Invoice No *</label>
                <input type="text" name="invoice_no" class="form-control" value="{{ $invoice_no }}" required>
            </div>
            <div class="col-md-3">
                <label>Invoice Date *</label>
                <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <label>Ref No</label>
                <input type="text" name="ref_no" class="form-control" value="{{ $invoiceData['ref_no'] ?? '' }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Currency Type</label>
                <select name="currency" id="currency" class="form-select">
                    <option value="PKR" selected>PKR</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Conversion Rate</label>
                <input type="number" id="conversion_rate" name="conversion_rate" class="form-control" value="1" readonly>
            </div>
            <div class="col-md-3">
                <label>Rate</label>
                <input type="number" id="rate" name="rate" class="form-control" value="0">
            </div>
            <div class="col-md-3">
                <label>Sale Amount *</label>
                <input type="number" id="sale_amount" name="sale_amount" class="form-control" value="0" readonly required>
            </div>
        </div>

        <div class="mb-3">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control">{{ $invoiceData['remarks'] ?? '' }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Invoice</button>
    </form>
</div>

<script>
document.getElementById('currency').addEventListener('change', function() {
    const currency = this.value;
    document.getElementById('conversion_rate').value = (currency === 'PKR') ? 1 : 280;
    calculateSaleAmount();
});

document.getElementById('rate').addEventListener('input', calculateSaleAmount);

function calculateSaleAmount() {
    const rate = parseFloat(document.getElementById('rate').value) || 0;
    const conversion = parseFloat(document.getElementById('conversion_rate').value) || 1;
    document.getElementById('sale_amount').value = rate * conversion;
}
</script>
@endsection
