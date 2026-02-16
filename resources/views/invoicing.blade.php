@extends('layouts.master')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Invoicing</h4>

    <div class="row">
        <!-- Import Invoicing -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('invoice.import') }}" class="text-decoration-none">
                <div class="card text-center shadow-sm h-100 border-0" style="background-color: #f9fdff;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-file-alt fs-4"></i>
                        </div>
                        <h6 class="mb-0">Import Invoicing</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="{{ route('invoice.uninvoiced.import') }}" class="text-decoration-none">
                <div class="card text-center shadow-sm h-100 border-0" style="background-color: #f9fdff;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-file-alt fs-4"></i>
                        </div>
                        <h6 class="mb-0">Un-Invoice Booking - Import</h6>
                    </div>
                </div>
            </a>
        </div>
        <!-- Export Invoicing -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('invoice.export') }}" class="text-decoration-none">
                <div class="card text-center shadow-sm h-100 border-0" style="background-color: #f7fcfd;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-file-export fs-4"></i>
                        </div>
                        <h6 class="mb-0">Export Invoice</h6>
                    </div>
                </div>
            </a>
        </div>
        <!-- Export Invoicing -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('invoice.uninvoiced.export') }}" class="text-decoration-none">
                <div class="card text-center shadow-sm h-100 border-0" style="background-color: #f7fcfd;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-file-export fs-4"></i>
                        </div>
                        <h6 class="mb-0">Univoice Booking Export </h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="{{ route('invoice.recovery') }}" class="text-decoration-none">
                <div class="card text-center shadow-sm h-100 border-0" style="background-color: #f7fcfd;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-file-export fs-4"></i>
                        </div>
                        <h6 class="mb-0">Invoice Recovery</h6>
                    </div>
                </div>
            </a>
        </div>

        <!-- Invoice Detail Report -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('invoice.report') }}" class="text-decoration-none">
                <div class="card text-center shadow-sm h-100 border-0" style="background-color: #f6fdfc;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-clipboard-check fs-4"></i>
                        </div>
                        <h6 class="mb-0">Invoice Detail Report</h6>
                    </div>
                </div>
            </a>
        </div>

        {{-- Recovered Invoices --}}
        <div class="col-md-4 mb-4">
            <a href="{{ route('recovered.invoices') }}" class="text-decoration-none">
                <div class="card text-center shadow-sm h-100 border-0" style="background-color: #f6fdfc;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-clipboard-check fs-4"></i>
                        </div>
                        <h6 class="mb-0">Recovered Invoices</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
{{-- @include('layouts.footer') --}}
@endsection
