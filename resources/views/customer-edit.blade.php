@extends('layouts.master')

@section('title', 'Edit Customer')

@section('content')
<div class="page-content">
    <div class="page-container mt-5"> 
        <form action="{{ route('customer.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header text-black">
                    <h5 class="mb-0">Edit Customer</h5>
                </div>

                <div class="card-body row g-3">

                    <!-- Basic fields -->
                    <div class="col-md-4">
                        <label>Customer Code</label>
                        <input type="text" class="form-control" value="{{ $customer->code }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> Customer Name
                        <input type="text" name="customer_name" class="form-control" required value="{{ old('customer_name', $customer->customer_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label>Open Date</label>
                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($customer->open_date)->format('d-M-Y') }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> Contact Person 1
                        <input type="text" name="contact_person_1" class="form-control" required value="{{ old('contact_person_1', $customer->contact_person_1) }}">
                    </div>

                    <div class="col-md-4">
                        <label>Contact Person 2</label>
                        <input type="text" name="contact_person_2" class="form-control" value="{{ old('contact_person_2', $customer->contact_person_2) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> Product
                        <select name="product" class="form-control" required>
                            <option value="">--SELECT--</option>
                            <option value="Import" {{ $customer->product=='Import' ? 'selected' : '' }}>Import</option>
                            <option value="Export" {{ $customer->product=='Export' ? 'selected' : '' }}>Export</option>
                            <option value="Domestic" {{ $customer->product=='Domestic' ? 'selected' : '' }}>Domestic</option>
                            <option value="COD" {{ $customer->product=='COD' ? 'selected' : '' }}>COD</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> Contact No 1
                        <input type="text" name="contact_no_1" class="form-control" required value="{{ old('contact_no_1', $customer->contact_no_1) }}">
                    </div>

                    <div class="col-md-4">
                        <label>Contact No 2</label>
                        <input type="text" name="contact_no_2" class="form-control" value="{{ old('contact_no_2', $customer->contact_no_2) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> Email Address 1
                        <input type="email" name="email_1" class="form-control" required value="{{ old('email_1', $customer->email_1) }}">
                    </div>

                    <div class="col-md-4">
                        <label>Email Address 2</label>
                        <input type="email" name="email_2" class="form-control" value="{{ old('email_2', $customer->email_2) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> Address 1
                        <textarea name="address_1" class="form-control" required>{{ old('address_1', $customer->address_1) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label>Address 2</label>
                        <textarea name="address_2" class="form-control">{{ old('address_2', $customer->address_2) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label>NTN#</label>
                        <input type="text" name="ntn" class="form-control" value="{{ old('ntn', $customer->ntn) }}">
                    </div>

                    <div class="col-md-4">
                        <label>NIC</label>
                        <input type="text" name="nic" class="form-control" value="{{ old('nic', $customer->nic) }}">
                    </div>

                    <div class="col-md-4">
                        <label>Website</label>
                        <input type="text" name="website" class="form-control" value="{{ old('website', $customer->website) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> Country
                        <select name="country_id" id="countrySelect" class="form-control" required>
                            <option value="">--SELECT--</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ $country->id == $customer->country_id ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="text-danger">*</label> City
                        <select name="city_id" id="citySelect" class="form-control" required>
                            <option value="">--SELECT--</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ $city->id == $customer->city_id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Parent Customer Code</label>
                        <input type="text" name="parent_customer_code" class="form-control" value="{{ old('parent_customer_code', $customer->parent_customer_code) }}">
                    </div>

                    <!-- Dynamic Territory -->
                    <div class="col-md-4">
                        <label>Territory</label>
                        <select name="territory" id="territorySelect" class="form-control" required>
                            <option value="">--SELECT--</option>
                            @foreach($users as $user)
                                <option value="Territory {{ strtoupper(substr($user->name,0,1)) }} - {{ $user->name }}"
                                        data-salesperson="{{ $user->name }}"
                                        {{ $user->name == $customer->sales_person ? 'selected' : '' }}>
                                    Territory {{ strtoupper(substr($user->name,0,1)) }} - {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Sales Person</label>
                        <input type="text" name="sales_person" id="salesPerson" class="form-control" readonly value="{{ $customer->sales_person }}">
                    </div>

                    <div class="col-md-4">
                        <label>Tariff Code</label>
                        <select name="tariff_code" class="form-control">
                            <option value="">--SELECT--</option>
                            <option value="T1" {{ $customer->tariff_code=='T1' ? 'selected' : '' }}>T1</option>
                            <option value="T2" {{ $customer->tariff_code=='T2' ? 'selected' : '' }}>T2</option>
                            <option value="T3" {{ $customer->tariff_code=='T3' ? 'selected' : '' }}>T3</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Status</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="1" {{ $customer->status==1 ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="0" {{ $customer->status==0 ? 'checked' : '' }}>
                            <label class="form-check-label">In-Active</label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label>Business Type</label>
                        <select name="business_type" id="businessTypeSelect" class="form-control">
                            <option value="">--SELECT--</option>
                            <option value="Logistics" {{ $customer->business_type=='Logistics' ? 'selected' : '' }}>Logistics</option>
                            <option value="Textile Manufacturer" {{ $customer->business_type=='Textile Manufacturer' ? 'selected' : '' }}>Textile Manufacturer</option>
                            <option value="Importer" {{ $customer->business_type=='Importer' ? 'selected' : '' }}>Importer</option>
                            <option value="Other" {{ !in_array($customer->business_type, ['Logistics','Textile Manufacturer','Importer']) && $customer->business_type ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="otherBusinessTypeField" style="display: {{ !in_array($customer->business_type, ['Logistics','Textile Manufacturer','Importer']) && $customer->business_type ? 'block' : 'none' }};">
                        <label>Specify Other Business Type</label>
                        <input type="text" name="other_business_type" id="otherBusinessTypeInput" class="form-control" value="{{ !in_array($customer->business_type, ['Logistics','Textile Manufacturer','Importer']) ? $customer->business_type : '' }}">
                    </div>

                </div>

                <!-- Footer -->
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                    <a href="{{ route('customer.index') }}" class="btn btn-secondary">Cancel</a>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <input type="file" name="attachment" class="form-control" id="attachmentInput" style="max-width: 200px;">
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#attachmentModal">
                            Preview Attachment
                        </button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @include('layouts.footer')
</div>

<!-- Attachment Preview Modal -->
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Attachment Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <iframe id="attachmentPreview" style="width:100%; height:500px;" frameborder="0"></iframe>
        <p class="text-muted mt-2">Note: Only PDFs, images, and previewable files will show here.</p>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function(){

    // -----------------------------
    // Country → City dependent dropdown
    // -----------------------------
    $('#countrySelect').on('change', function(){
        let country_id = $(this).val();
        $('#citySelect').html('<option>Loading...</option>');

        if(country_id){
            $.get(`/get-cities-by-country/${country_id}`, function(cities){
                let options = '<option value="">--SELECT--</option>';
                cities.forEach(c => {
                    options += `<option value="${c.id}">${c.name}</option>`;
                });
                $('#citySelect').html(options);
            });
        } else {
            $('#citySelect').html('<option value="">--SELECT--</option>');
        }
    });

    // -----------------------------
    // Auto-fill Sales Person from Territory
    // -----------------------------
    $('#territorySelect').on('change', function() {
        const selectedSalesPerson = $(this).find(':selected').data('salesperson') || '';
        $('#salesPerson').val(selectedSalesPerson);
    });

    // -----------------------------
    // Business Type → Other logic
    // -----------------------------
    $('#businessTypeSelect').on('change', function() {
        if($(this).val() === 'Other'){
            $('#otherBusinessTypeField').show();
            $('#otherBusinessTypeInput').attr('required', true);
        } else {
            $('#otherBusinessTypeField').hide();
            $('#otherBusinessTypeInput').val('');
            $('#otherBusinessTypeInput').removeAttr('required');
        }
    });

    // -----------------------------
    // Attachment preview
    // -----------------------------
    $('#attachmentInput').on('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('attachmentPreview');
        if (file) {
            preview.src = URL.createObjectURL(file);
        } else {
            preview.src = '';
        }
    });

});
</script>
@endpush

