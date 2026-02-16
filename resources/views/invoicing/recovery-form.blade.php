@extends('layouts.master')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <span><i class="ti ti-credit-card"></i> Invoice Recovery - {{ $invoice->invoice_no }}</span>
                    <a href="{{ route('invoice.recovery.index') }}" class="btn btn-sm btn-secondary">← Back</a>
                </div>

                <div class="card-body">
                    <form action="{{ route('invoice.recovery.store', $invoice->id) }}" method="POST">
                        @csrf

                        {{-- Invoice No (editable if you want; change to readonly if you prefer) --}}
                        <div class="mb-3">
                            <label class="form-label">Invoice No.</label>
                            <input type="text" name="invoice_no" class="form-control" value="{{ $invoice->invoice_no }}">
                        </div>

                        {{-- Customer --}}
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <input type="text" name="customer" class="form-control" value="{{ $invoice->customer->name ?? $invoice->customer ?? '' }}">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Amount</label>
                                <input type="number" step="0.01" name="invoice_amount" class="form-control" value="{{ $invoice->invoice_amount }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Already Recovered</label>
                                <input type="text" class="form-control" value="{{ number_format($recovered_total,2) }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Balance</label>
                            <input type="number" step="0.01" name="balance" class="form-control" value="{{ $balance }}">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Recovery Person <span class="text-danger">*</span></label>
                                <input type="text" name="recovery_person" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Receiving Path <span class="text-danger">*</span></label>
                                <input type="text" name="receiving_path" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Recovery Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="recovery_amount" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="text-end">
                            <button class="btn btn-success" type="submit">Save Recovery</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Existing recoveries --}}
            <div class="card mt-4 shadow-sm">
                <div class="card-header">Existing Recoveries</div>
                <div class="table-responsive">
                    <table class="table table-bordered text-center mb-0">
                        <thead>
                            <tr>
                                <th>Person</th>
                                <th>Path</th>
                                <th>Amount</th>
                                <th>Remarks</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recoveries as $rec)
                                <tr>
                                    <td>{{ $rec->recovery_person }}</td>
                                    <td>{{ $rec->receiving_path }}</td>
                                    <td>{{ number_format($rec->recovery_amount,2) }}</td>
                                    <td>{{ $rec->remarks }}</td>
                                    <td>{{ $rec->created_at->format('d-M-Y') }}</td>
                                    <td>
                                        <a href="{{ route('invoice.recovery.edit', $rec->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('invoice.recovery.update', $rec->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button class="btn btn-sm btn-danger" type="submit" name="delete" value="1">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No recoveries yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
