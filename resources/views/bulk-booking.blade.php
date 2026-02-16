@extends('layouts.master')

@section('title', 'Bulk Booking Attachments')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Bulk Booking Attachments Upload</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('messages'))
        <div class="alert alert-warning">
            <ul>
                @foreach(session('messages') as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($step == 1)
        <!-- STEP 1: Upload CSV -->
        <form method="POST" action="{{ route('booking.import.step1.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Upload CSV File</label>
                <input type="file" name="csv_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Next →</button>
        </form>
        
    @elseif($step == 2)
        <!-- STEP 2: Map CSV Columns -->
        <form method="GET" action="{{ route('booking.import.step2') }}">
            <h5 class="mb-3">Step 2: Map CSV Columns</h5>
            <div class="row">
                @foreach($targetColumns as $target)
                    <div class="col-md-4">
                        <label>{{ ucfirst(str_replace('_', ' ', $target)) }}</label>
                        <select name="mapping[{{ $target }}]" class="form-select" required>
                            <option value="">-- Select Column --</option>
                            @foreach($sourceColumns as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary mt-3">Next →</button>
        </form>

    @elseif($step == 3)
        <!-- STEP 3: Preview + Submit -->
        <form method="POST" action="{{ route('booking.import.step3') }}">
            @csrf
            <div class="mb-3">
                <label>Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                    @endforeach
                </select>
            </div>

            <h5 class="mt-4">Preview Data</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            @foreach($columns as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                @foreach($columns as $col)
                                    <td>{{ $row[$col] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success mt-3">Submit Attachments</button>
        </form>
    @endif
    
</div>

@endsection
