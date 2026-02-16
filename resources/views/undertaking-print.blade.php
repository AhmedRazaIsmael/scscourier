@extends('layouts.master')

@section('title', 'Undertaking Print')

@section('content')
<div class="app-container">

    <!-- Header -->
    <div class="app-hero-header d-flex align-items-center">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Undertaking Print</h2>
            </div>
        </div>

        <div class="ms-auto d-lg-flex d-none flex-row">
            <div class="d-flex flex-row gap-1">
                <button type="button" id="printBtn" class="icon-box icon-btn rounded-5" data-bs-toggle="tooltip" title="Print">
                    <i class="bi bi-printer"></i>
                </button>
                <button type="button" id="downloadBtn" class="icon-box icon-btn rounded-5" data-bs-toggle="tooltip" title="Download">
                    <i class="bi bi-download"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="app-body">
        <div class="row gx-4">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Undertaking Print</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                        <form id="undertakingForm" action="{{ route('undertaking.generate') }}" method="GET" target="_blank">
                            <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label for="bookNo" class="form-label">Enter Book No</label>
                                    <input 
                                        type="text" 
                                        name="bookNo" 
                                        id="bookNo" 
                                        value="{{ old('bookNo', $bookNo ?? '') }}"
                                        class="form-control" 
                                        placeholder="Enter your book number" 
                                        required
                                    >
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </form>
                    </div>
                </div>

                @if(isset($booking) && $booking)
                    <div class="alert alert-success mt-3">
                        <strong>Booking Found:</strong> {{ $booking->bookNo }} ({{ $booking->customer->customer_name ?? 'N/A' }})
                    </div>
                @elseif(isset($bookNo) && !$booking)
                    <div class="alert alert-danger mt-3">
                        No booking found for Book No: {{ $bookNo }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Toast -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3 mt-5">
            <div id="downloadToast" class="toast text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Downloading</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Undertaking PDF is downloading.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('undertakingForm');
    const printBtn = document.getElementById('printBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const toastEl = document.getElementById('downloadToast');
    const toast = new bootstrap.Toast(toastEl);

    // 🖨️ Print (open PDF in browser)
    printBtn.addEventListener('click', function () {
        if (!form.bookNo.value) {
            alert('Please enter a Book No first.');
            return;
        }
        form.target = '_blank';
        form.submit();
    });

    // ⬇️ Download (force download)
    downloadBtn.addEventListener('click', function () {
        if (!form.bookNo.value) {
            alert('Please enter a Book No first.');
            return;
        }
        const url = "{{ route('undertaking.generate') }}" + "?bookNo=" + form.bookNo.value;
        window.open(url, '_blank');
        toast.show();
    });
});
</script>
@endpush
