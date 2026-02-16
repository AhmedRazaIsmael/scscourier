@extends('layouts.master')

@section('title', 'Single Label')

@section('content')
@php
    $bookNo = request('bookNo'); // For print/download/share
@endphp

<div class="app-container">
    <!-- Header -->
    <div class="app-hero-header d-flex align-items-center">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">Single Label</h2>
            </div>
        </div>

        {{-- Action Buttons: Share / Print / Download --}}
        @if($bookNo)
        <div class="ms-auto d-lg-flex d-none flex-row">
            <div class="d-flex flex-row gap-1">
                <!-- Share -->
                <a href="#" class="icon-box icon-btn rounded-5" title="Share"
                   onclick="copyToClipboard('{{ url('/single-label?bookNo=' . $bookNo) }}')">
                    <i class="bi bi-share"></i>
                </a>

                <!-- Print -->
                <a href="{{ route('label.single.print.get', ['bookNo' => $bookNo]) }}"
                   class="icon-box icon-btn rounded-5" title="Print" target="_blank">
                    <i class="bi bi-printer"></i>
                </a>

                <!-- Download -->
                <a href="{{ route('label.single.print.get', ['bookNo' => $bookNo, 'download' => 1]) }}"
                   class="icon-box icon-btn rounded-5" title="Download" target="_blank">
                    <i class="bi bi-download"></i>
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Body -->
    <div class="app-body">
        <div class="row gx-4">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Single Label</h5>
                    </div>
                    <div class="card-body">

                        {{-- Errors --}}
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

                        {{-- Form --}}
                        <form method="POST" action="{{ route('label.single.print') }}">
                            @csrf
                            <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label for="bookNo" class="form-label">Enter Book No</label>
                                    <input type="text" name="bookNo" required class="form-control"
                                           id="bookNo"
                                           placeholder="Enter your booking number"
                                           value="{{ old('bookNo', $bookNo) }}">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- Toast (optional, if using for download alert) -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3 mt-5">
            <div id="downloadData" class="toast text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Downloading</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Data successfully downloaded.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Share link copied to clipboard!');
        }).catch(() => {
            alert('Failed to copy the link.');
        });
    }
</script>
@endpush
