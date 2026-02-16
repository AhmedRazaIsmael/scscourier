@extends('layouts.master')
@section('title', '3PL Bulk Booking Upload')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📤 3PL Bulk Booking Upload Wizard</h4>

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
        <div class="mb-3">
    <a href="{{ route('3pl.upload.sample') }}" class="btn btn-info">
        📄 Download Sample CSV
    </a>
</div>
    @if($step == 1)
        <form method="POST" action="{{ route('3pl.upload.step1') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Upload CSV File</label>
                <input type="file" name="csv_file" class="form-control" required>
            </div>
            <button class="btn btn-primary">Next →</button>
        </form>
    @elseif($step == 2)
        <form method="GET" action="{{ route('3pl.upload.step2') }}">
            <h5 class="mb-3">🧩 Map CSV Columns</h5>
            @foreach($targetColumns as $target => $type)
                <div class="mb-2">
                    <label class="form-label">{{ $target }} <span class="text-muted">({{ $type }})</span></label>
                    <select name="mapping[{{ $target }}]" class="form-select" required>
                        <option value="">-- Select Column --</option>
                        @foreach($sourceColumns as $col)
                            <option value="{{ $col }}">{{ $col }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <button class="btn btn-primary mt-3">Next →</button>
        </form>
    @elseif($step == 3)
        <form method="POST" action="{{ route('3pl.upload.final') }}">
            @csrf
            <div class="mb-3">
                <label>Select 3PL Company</label>
                <select name="company_name" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($companies as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>

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
            <button class="btn btn-success mt-3">🚀 Upload Bookings</button>
        </form>
    @endif
</div>
@endsection
