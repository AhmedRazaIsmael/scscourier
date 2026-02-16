@extends('layouts.master')

@section('title', 'Booking Upload Wizard')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Booking Upload Wizard</h4>

    {{-- Success & Error Messages --}}
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

    {{-- STEP 1: Upload CSV --}}
    <div class="mb-3">
    <a href="{{ route('wizard.bookings.sample') }}" class="btn btn-info">
        📄 Download Sample CSV
    </a>
</div>
    @if($step == 1)
        <form method="POST" action="{{ route('wizard.bookings.step1.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Upload CSV File</label>
                <input type="file" name="csv_file" class="form-control" required>
            </div>
            <button class="btn btn-primary">Next →</button>
        </form>
    @endif

    {{-- STEP 2: Column Mapping --}}
    @if($step == 2)
        <form method="POST" action="{{ route('wizard.bookings.step2.validate') }}">
            @csrf
            <h5 class="mb-3">🧩 Step 2: Map Your CSV Columns</h5>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Target Field (DB Column)</th>
                            <th>Field Type</th>
                            <th>Select Source Column</th>
                            <th>Row 1 Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targetColumns as $field => $type)
                            <tr>
                                <td><code>{{ strtoupper($field) }}</code></td>
                                <td>{{ $type }}</td>
                                <td>
                                    <select name="mapping[{{ $field }}]" class="form-select" required>
                                        <option value="">-- Select Column --</option>
                                        @foreach($columns as $source)
                                            <option value="{{ $source }}">{{ $source }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    {{ $data[0][$field] ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Next →</button>
        </form>
    @endif

    {{-- STEP 3: Preview + Submit --}}
    @if($step == 3)
        <form method="POST" action="{{ route('wizard.bookings.final') }}">
            @csrf

            <div class="mb-3">
                <label>Select Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                    @endforeach
                </select>
            </div>

            <h5 class="mt-4">📄 Preview Data</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
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

            <button type="submit" class="btn btn-success mt-3">Upload Bookings</button>
        </form>
    @endif
</div>
@endsection
