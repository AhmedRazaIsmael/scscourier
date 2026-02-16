@extends('layouts.master')

@section('title', 'Sticker Single Label')

@section('content')
    <!-- App container starts -->
    <div class="app-container">

        <!-- App hero header starts -->
        <div class="app-hero-header d-flex align-items-center">
            <!-- Breadcrumb starts -->
            <div class="d-flex align-items-center">
                <div class="me-3 icon-box md border bg-white rounded-5">
                    <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
                </div>
                <div>
                    <h2 class="mb-1">Sticker Label</h2>
                </div>
            </div>
            <!-- Breadcrumb ends -->

            <!-- Action buttons -->
            <div class="ms-auto d-lg-flex d-none flex-row">
                {{-- <div class="d-flex flex-row gap-1">
                    <a href="#" class="icon-box icon-btn rounded-5" id="shareBtn" data-bs-toggle="tooltip" title="Share">
                        <i class="bi bi-share"></i>
                    </a>

                    <button type="button" id="printBtn" class="icon-box icon-btn rounded-5" data-bs-toggle="tooltip" title="Print">
                        <i class="bi bi-printer"></i>
                    </button>

                    <button type="button" id="downloadBtn" class="icon-box icon-btn rounded-5" data-bs-toggle="tooltip" title="Download">
                        <i class="bi bi-download"></i>
                    </button>
                </div> --}}
            </div>
        </div>
        <!-- App Hero header ends -->

        <!-- App body starts -->
        <div class="app-body">
            <div class="row gx-4">
                <!-- Form Card -->
                <div class="col-sm-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Sticker Label</h5>
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
                            <form id="stickerLabelForm" action="{{ route('label.sticker.print') }}" method="POST" target="_blank">
                                @csrf
                                <div class="row gx-4 mb-4">
                                    <div class="col-md-12">
                                        <label for="bookNo" class="form-label">Enter Book No</label>
                                        <input type="text" name="bookNo" id="bookNo" required class="form-control" placeholder="Enter your book number">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary">Reset</button>
                            </form>
                        </div>
                    </div>
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
                        Sticker Label PDF is downloading.
                    </div>
                </div>
            </div>
        </div>
        <!-- App body ends -->
    </div>
    <!-- App container ends -->

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const printBtn = document.getElementById('printBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const form = document.getElementById('stickerLabelForm');
    const toastEl = document.getElementById('downloadToast');
    const toast = new bootstrap.Toast(toastEl);

    // 🖨️ Print PDF in new tab
    printBtn.addEventListener('click', function () {
        if (!form.bookNo.value) {
            alert('Please enter a Book No first.');
            return;
        }
        form.target = '_blank';
        form.submit(); // open stream PDF in new tab
    });

    // ⬇️ Download PDF version
    downloadBtn.addEventListener('click', function () {
        if (!form.bookNo.value) {
            alert('Please enter a Book No first.');
            return;
        }

        const downloadUrl = "{{ route('label.single.print.get') }}" + "?bookNo=" + form.bookNo.value + "&download=1";
        window.open(downloadUrl, '_blank');
        toast.show();
    });
});
</script>
@endpush
