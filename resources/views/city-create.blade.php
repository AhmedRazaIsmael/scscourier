@extends('layouts.master')
@section('title', 'Create City')

@section('content')
<div class="page-content">
    <div class="page-container mt-5">
        <div class="card">
            <div class="card-header">
                <h5>Create City</h5>
            </div>
            <div class="card-body">

                {{-- ✅ Show Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ✅ Show Error Message --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ✅ Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('city.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>City Code</label>
                            <input type="text" name="code" id="cityCode" class="form-control" placeholder="Enter City Code">
                        </div>

                        <div class="col-md-6">
                            <label>City Name</label>
                            <select name="name" id="cityName" class="form-control">
                                <option value="">--SELECT--</option>
                                {{-- Options will be loaded dynamically --}}
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Country</label>
                            <select name="country_id" id="country" class="form-control" required>
                                <option value="">--SELECT--</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{ route('city.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // ✅ Load cities by Country
    function loadCities(countryId) {
        if (!countryId) return;

        $.get(`/get-cities-by-country/${countryId}`, function(cities) {
            let cityOptions = '<option value="">--SELECT--</option>';
            cities.forEach(c => {
                cityOptions += `<option value="${c.name}" data-code="${c.code}">${c.name}</option>`;
            });
            $('#cityName').html(cityOptions);
        }).fail(function() {
            alert('Failed to load cities. Please try again.');
        });
    }

    // ✅ When country changes → Load related cities
    $('#country').on('change', function () {
        const countryId = $(this).val();
        $('#cityName').html('<option value="">Loading...</option>');
        $('#cityCode').val('');
        loadCities(countryId);
    });

    // ✅ When city changes → Auto-fill city code
    $('#cityName').on('change', function () {
        const selected = $(this).find(':selected');
        $('#cityCode').val(selected.data('code') || '');
    });
});
</script>
@endpush
