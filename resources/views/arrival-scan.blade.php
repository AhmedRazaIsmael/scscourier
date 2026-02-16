@extends('layouts.master')

@section('title', 'Arrival Scan')

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
        <h2 class="mb-1">Arrival Scan</h2>
      </div>
    </div>
    <!-- Breadcrumb ends -->

    <!-- Header action buttons -->
    <div class="ms-auto d-lg-flex d-none flex-row">
      <div class="d-flex flex-row gap-1">
        <a href="#" class="icon-box icon-btn rounded-5" data-bs-toggle="tooltip" data-bs-placement="bottom"
           title="Share">
          <i class="bi bi-share"></i>
        </a>
        <a href="#" class="icon-box icon-btn rounded-5" data-bs-toggle="tooltip" data-bs-placement="bottom"
           title="Print">
          <i class="bi bi-printer"></i>
        </a>
        <a href="#" class="icon-box icon-btn rounded-5" id="downloadDataToast" data-bs-toggle="tooltip"
           data-bs-placement="bottom" title="Download">
          <i class="bi bi-download"></i>
        </a>
      </div>
    </div>
    <!-- Header action buttons end -->

  </div>
  <!-- App hero header ends -->

  <!-- App body starts -->
  <div class="app-body">

    <!-- Row start -->
    <div class="row gx-4">

      <!-- Multi-column form layout -->
      <div class="col-sm-12">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title">Arrival Scan</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('scan.form', ['type' => 'arrival']) }}" method="get">
              <div class="row gx-4 mb-4">
                <div class="col-md-12">
                  <label for="bookNo" class="form-label">Enter Book No</label>
                  <input type="text" name="bookNo" class="form-control" id="bookNo"
                         value="{{ request('bookNo') }}" placeholder="Enter your book number" required>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Submit</button>
              <button type="reset" class="btn btn-outline-secondary"
                      onclick="window.location='{{ route('scan.form', ['type' => 'arrival']) }}'">
                Reset
              </button>
            </form>
          </div>
        </div>
      </div>
      <!-- Multi-column form layout end -->

    </div>
    <!-- Row end -->

    <!-- Result Section -->
    @php
        $scans = $scans ?? collect();
    @endphp

    @if($scans->count())
      <div class="card mt-3">
        <div class="card-body table-responsive">
          <table class="table table-bordered table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>Book No.</th>
                <th>Scan Date/Time</th>
                <th>Hub</th>
                <th>Scanned By</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($scans as $scan)
                <tr>
                  <td>{{ $scan->book_no }}</td>
                  <td>{{ $scan->created_at->format('d-M-Y H:i:s') }}</td>
                  <td>{{ $scan->hub->code ?? '-' }}</td>
                  <td>{{ strtoupper($scan->user->username ?? '-') }}</td>
                  <td>{{ $scan->status }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @elseif(request('bookNo'))
      <div class="alert alert-warning mt-3">
        No record found for Book No: <strong>{{ request('bookNo') }}</strong>
      </div>
    @endif
    <!-- Result Section end -->

    <!-- Toast message for download data -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3 mt-5">
      <div id="downloadData" class="toast text-bg-primary border-0" role="alert" aria-live="assertive"
           aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Downloading</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          Data successfully downloading.
        </div>
      </div>
    </div>
    <!-- Toast message end -->

  </div>
  <!-- App body ends -->

</div>
<!-- App container ends -->

@include('layouts.footer')
@endsection
