@extends('layouts.master')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between">
                    <span>Edit Recovery</span>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">← Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('invoice.recovery.update', $recovery->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Recovery Person</label>
                            <input type="text" name="recovery_person" class="form-control" value="{{ $recovery->recovery_person }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Receiving Path</label>
                            <input type="text" name="receiving_path" class="form-control" value="{{ $recovery->receiving_path }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Recovery Amount</label>
                            <input type="number" step="0.01" name="recovery_amount" class="form-control" value="{{ $recovery->recovery_amount }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ $recovery->remarks }}</textarea>
                        </div>

                        <div class="text-end">
                            <button class="btn btn-success" type="submit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
