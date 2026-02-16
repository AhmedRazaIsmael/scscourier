@extends('layouts.master')
@section('title', 'Edit City')

@section('content')
<div class="page-content">
    <div class="page-container mt-5"> 
        <div class="card">
            <div class="card-header">
                <h5>Edit City</h5>
            </div>
            <div class="card-body">

                {{-- ✅ Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ✅ Error Message --}}
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

                <form action="{{ route('city.update', $city->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>City Code</label>
                            <input type="text" name="code" id="cityCode" class="form-control" required value="{{ old('code', $city->code) }}">
                        </div>
                        <div class="col-md-6">
                            <label>City Name</label>
                            <select name="name" id="cityName" class="form-control" required>
                                <option value="">--SELECT--</option>
                                {{-- Will load dynamically --}}
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Country</label>
                            <select name="country_id" id="country" class="form-control" required>
                                <option value="">--SELECT--</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" 
                                        {{ $country->id == old('country_id', $city->country_id) ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
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

    // ✅ Load cities for selected country
    function loadCities(countryId, selectedCityName = null) {
        if (!countryId) return;

        $.get(`/get-cities-by-country/${countryId}`, function (cities) {
            let cityOptions = '<option value="">--SELECT--</option>';
            cities.forEach(c => {
                const selected = (selectedCityName && selectedCityName === c.name) ? 'selected' : '';
                cityOptions += `<option value="${c.name}" data-code="${c.code}" ${selected}>${c.name}</option>`;
            });
            $('#cityName').html(cityOptions);

            // Auto-fill code if city already selected
            const selected = $('#cityName').find(':selected');
            $('#cityCode').val(selected.data('code') || '');
        }).fail(function () {
            console.error('Failed to load cities.');
        });
    }

    // ✅ On country change
    $('#country').on('change', function () {
        const countryId = $(this).val();
        $('#cityName').html('<option value="">Loading...</option>');
        $('#cityCode').val('');
        loadCities(countryId);
    });

    // ✅ On city change → auto-fill code
    $('#cityName').on('change', function () {
        const selected = $(this).find(':selected');
        $('#cityCode').val(selected.data('code') || '');
    });

    // ✅ Load cities when editing (initial load)
    const selectedCountry = $('#country').val();
    const selectedCity = "{{ old('name', $city->name) }}";
    if (selectedCountry) {
        loadCities(selectedCountry, selectedCity);
    }
});
</script>
@endpush
