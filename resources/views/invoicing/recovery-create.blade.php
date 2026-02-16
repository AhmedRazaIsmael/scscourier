@extends('layouts.master')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="ti ti-plus"></i> Create Invoice Recovery
        </div>

        <div class="card-body">
            <form action="{{ route('invoice.recovery.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Invoice No</label>
                        <input type="text" name="invoice_no" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Invoice Type</label>
                        <select name="invoice_type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="export">Export</option>
                            <option value="import">Import</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Recovery Person</label>
                        <input type="text" name="recovery_person" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Receiving Path</label>
                        <input type="text" name="receiving_path" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Recovery Amount</label>
                        <input type="number" step="0.01" name="recovery_amount" class="form-control" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-save"></i> Save Recovery
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
