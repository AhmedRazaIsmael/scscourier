@extends('layouts.master')

@section('title', 'POD Single Label')

@section('content')
<div class="app-container">
    <div class="app-hero-header d-flex align-items-center">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">POD Single Label</h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        <div class="row gx-4">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Generate Single POD Label</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('label.single.pod.generate') }}" method="GET" target="_blank">
    <div class="mb-3">
        <label for="bookNo" class="form-label">Enter Book No</label>
        <input type="text" name="bookNo" class="form-control" id="bookNo" placeholder="" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
