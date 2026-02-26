@extends('layouts.master')

@section('title', isset($booking) ? 'Edit Domestic Booking' : 'Domestic Booking')

@section('content')
<div class="app-container">

    <!-- Header -->
    <div class="app-hero-header d-flex align-items-center">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-house-door fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">{{ isset($booking) ? 'Edit Domestic Booking' : 'Domestic Booking' }}</h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        <div class="row gx-4">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">{{ isset($booking) ? 'Edit Domestic Booking' : 'Domestic Booking Form' }}</h5>
                    </div>
                    <div class="card-body">

                        {{-- Success --}}
                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

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

                        <form method="POST" action="{{ isset($booking) ? route('booking.update', $booking->id) : route('booking.store') }}">
                            @csrf
                            @if(isset($booking))
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $booking->id }}">
                            @endif

                            <input type="hidden" name="bookingType" value="domestic">

                            {{-- Book Info --}}
                            <div class="row gx-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Book No</label>
                                    <input type="text" class="form-control" value="{{ old('bookNo', $booking->bookNo ?? '(Auto Generated)') }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Book Date</label>
                                    <input type="date" class="form-control" value="{{ old('bookDate', $booking->bookDate ?? now()->toDateString()) }}" readonly>
                                </div>
                            </div>

                            {{-- Customer & Booking Info --}}
                            <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customerSelect" class="form-select" required>
                                        <option disabled selected>Choose...</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $booking->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->customer_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Service</label>
                                    <select name="service" class="form-select">
                                        <option disabled selected>Choose...</option>
                                        <option value="overnight " {{ old('service', $booking->service ?? '') == 'overnight ' ? 'selected' : '' }}>Overnight </option>
                                        <option value="overland" {{ old('service', $booking->service ?? '') == 'overland' ? 'selected' : '' }}>Overland </option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Book Channel</label>
                                    <select name="bookChannel" class="form-select">
                                        <option disabled selected>Choose...</option>
                                        @foreach(['facebook', 'whatsapp', 'instagram', 'others'] as $ch)
                                        <option value="{{ $ch }}" {{ old('bookChannel', $booking->bookChannel ?? '') == $ch ? 'selected' : '' }}>
                                            {{ ucfirst($ch) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Payment & Location --}}
                            <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Payment Mode</label>
                                    <select name="paymentMode" class="form-select">
                                        <option disabled selected>Choose...</option>
                                        <option value="cod" {{ old('paymentMode', $booking->paymentMode ?? '') == 'cod' ? 'selected' : '' }}>COD</option>
                                        <option value="non_cod" {{ old('paymentMode', $booking->paymentMode ?? '') == 'non_cod' ? 'selected' : '' }}>Non-COD</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Origin</label>
                                    <select name="origin" class="form-select">
                                        <option disabled selected>Choose...</option>
                                        <option disabled selected>Pakistan</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Destination</label>
                                    <div class="position-relative">
                                        <input type="text" id="citySearch" class="form-control" placeholder="Search city..." autocomplete="off">
                                        <ul id="cityDropdown" style="
            display:none;
            position:absolute;
            top:100%;
            left:0;
            right:0;
            background:#fff;
            border:1px solid #ccc;
            border-top:none;
            max-height:200px;
            overflow-y:auto;
            z-index:9999;
            list-style:none;
            margin:0;
            padding:0;
        ">
                                            @foreach($tranzoCities as $city)
                                            @if(strtolower($city['city_name']) !== 'karachi')
                                            <li data-value="{{ $city['city_name'] }}" data-source="tranzo" data-id="{{ $city['id'] ?? '' }}" style="padding:8px 12px; cursor:pointer;">
                                                {{ $city['city_name'] }}
                                            </li>
                                            @endif
                                            @endforeach
                                            @php $tranzoCityNames = array_map('strtolower', array_column($tranzoCities, 'city_name')); @endphp
                                            @foreach($sonicCities as $city)
                                            @if(!in_array(strtolower($city['city_name']), $tranzoCityNames) && strtolower($city['city_name']) !== 'karachi')
                                            <li data-value="{{ $city['city_name'] }}" data-source="sonic" data-id="{{ $city['id'] ?? '' }}" style="padding:8px 12px; cursor:pointer;">
                                                {{ $city['city_name'] }}
                                            </li>
                                            @endif
                                            @endforeach
                                        </ul>
                                    </div>

                                    <input type="hidden" name="destination" id="destination" value="">
                                    <input type="hidden" name="consignee_city_id" id="consignee_city_id" value="">
                                    <input type="hidden" name="destination_source" id="destination_source" value="">
                                </div>



                            </div>

                            {{-- Dimensions & Shipment --}}
                            <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" name="postalCode" class="form-control" value="{{ old('postalCode', $booking->postalCode ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Invoice Value</label>
                                    <input type="number" name="invoiceValue" class="form-control" value="{{ old('invoiceValue', $booking->invoiceValue ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Weight (KG)</label>
                                    <input type="number" step="any" name="weight" class="form-control" value="{{ old('weight', $booking->weight ?? '') }}">
                                </div>
                            </div>

                            <div class="row gx-4 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Pieces</label>
                                    <input type="number" step="any" name="pieces" class="form-control" value="{{ old('pieces', $booking->pieces ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">COD Amount</label>
                                    <input type="number" name="codAmount" class="form-control" value="{{ old('codAmount', $booking->codAmount ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Item Detail</label>
                                    <textarea name="itemDetail" class="form-control" rows="3">{{ old('itemDetail', $booking->itemDetail ?? '') }}</textarea>
                                </div>
                                <!-- <div class="col-md-4">
                                    <label class="form-label">Length</label>
                                    <input type="number" step="any" name="length" class="form-control" value="{{ old('length', $booking->length ?? '') }}">

                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Width</label>
                                    <input type="number" step="any" name="width" class="form-control" value="{{ old('width', $booking->width ?? '') }}">

                                </div> -->
                            </div>

                            <!-- <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Height</label>
                                    <input type="number" step="any" name="height" class="form-control" value="{{ old('height', $booking->height ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Dimensional Weight</label>
                                    <input type="number" step="any" name="dimensionalWeight" class="form-control" value="{{ old('dimensionalWeight', $booking->dimensionalWeight ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Order No</label>
                                    <input type="text" name="orderNo" class="form-control" value="{{ old('orderNo', $booking->orderNo ?? '') }}">
                                </div>
                            </div> -->

                            <!-- <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Arrival Clearance</label>
                                    <select name="arrivalClearance" class="form-select">
                                        <option disabled selected>Choose...</option>
                                        @foreach(['DR', 'Console', 'Actual Clearance'] as $clearance)
                                        <option value="{{ $clearance }}" {{ old('arrivalClearance', $booking->arrivalClearance ?? '') == $clearance ? 'selected' : '' }}>
                                            {{ $clearance }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Item Content</label>
                                    <input type="text" name="itemContent" class="form-control" value="{{ old('itemContent', $booking->itemContent ?? '') }}">
                                </div>
                              
                            </div> -->



                            {{-- Shipper & Consignee --}}
                            <!-- @foreach(['shipper', 'consignee'] as $type)
                            <div class="row gx-4 mb-4">
                                @foreach(['Company', 'Name', 'Number', 'Email'] as $field)
                                <div class="col-md-6">
                                    <label class="form-label">{{ ucfirst($type) }} {{ $field }}</label>
                                    <input type="{{ $field === 'Email' ? 'email' : 'text' }}" name="{{ $type . $field }}" class="form-control"
                                        value="{{ old($type . $field, $booking->{$type . $field} ?? '') }}">
                                </div>
                                @endforeach
                            </div>
                            <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">{{ ucfirst($type) }} Address</label>
                                    <textarea name="{{ $type }}Address" class="form-control" rows="3">{{ old($type . 'Address', $booking->{$type . 'Address'} ?? '') }}</textarea>
                                </div>
                            </div>
                            @endforeach -->

                            {{-- Remarks / Instructions --}}
                            <!-- <div class="row gx-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control">{{ old('remarks', $booking->remarks ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pickup Instructions</label>
                                    <textarea name="pickupInstructions" class="form-control">{{ old('pickupInstructions', $booking->pickupInstructions ?? '') }}</textarea>
                                </div>
                            </div> -->

                            <!-- <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Delivery Instructions</label>
                                    <textarea name="deliveryInstructions" class="form-control" rows="3">{{ old('deliveryInstructions', $booking->deliveryInstructions ?? '') }}</textarea>
                                </div>
                            </div> -->

                            <!-- <div class="row gx-4 mb-4"> -->

                            <!-- Territory Select -->
                            <!-- <div class="col-md-4">
                                    <label class="form-label">Territory</label>
                                    <select name="territory" id="territory" class="form-select">
                                        <option selected disabled>Choose...</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-sales="{{ $user->name }}"
                                            {{ old('territory', isset($booking) ? $booking->territory : '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div> -->

                            <!-- Sales Person Display -->
                            <!-- <div class="col-md-4">
                                    <label class="form-label">Sales Person</label>
                                    <input type="text" id="salesPersonName" class="form-control"
                                        value="{{ old('salesPersonName', isset($booking) && $booking->salesPerson ? ($users->find($booking->salesPerson)->name ?? '') : '') }}"
                                        readonly>
                                    <input type="hidden" name="salesPerson" id="salesPerson"
                                        value="{{ old('salesPerson', isset($booking) ? $booking->salesPerson : '') }}">
                                </div> -->

                            <!-- <div class="col-md-4">
                                    <label class="form-label">Rate Type</label>
                                    <input type="text" name="rateType" class="form-control" value="{{ old('rateType', $booking->rateType ?? '') }}">
                                </div>
                            </div> -->

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ isset($booking) ? 'Update' : 'Submit' }}</button>
                                <a href="{{ route('booking.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')

<script>
    const citySearch = document.getElementById('citySearch');
    const cityDropdown = document.getElementById('cityDropdown');
    const allItems = cityDropdown.querySelectorAll('li');

    citySearch.addEventListener('input', function() {
        const search = this.value.toLowerCase().trim();
        let hasVisible = false;

        allItems.forEach(item => {
            if (item.textContent.toLowerCase().includes(search)) {
                item.style.display = '';
                hasVisible = true;
            } else {
                item.style.display = 'none';
            }
        });

        cityDropdown.style.display = (search.length > 0 && hasVisible) ? 'block' : 'none';

        // Agar search clear ho toh hidden fields bhi clear karo
        if (search.length === 0) {
            document.getElementById('destination').value = '';
            document.getElementById('consignee_city_id').value = '';
            document.getElementById('destination_source').value = '';
        }
    });

    allItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.background = '#f0f0f0';
        });
        item.addEventListener('mouseleave', function() {
            this.style.background = '#fff';
        });
        item.addEventListener('click', function() {
            const cityName = this.getAttribute('data-value');
            const source = this.getAttribute('data-source');
            const cityId = this.getAttribute('data-id');

            citySearch.value = cityName;
            document.getElementById('destination').value = cityName;
            document.getElementById('consignee_city_id').value = cityId;
            document.getElementById('destination_source').value = source;

            cityDropdown.style.display = 'none';
        });
    });

    // Bahar click karo toh dropdown band ho
    document.addEventListener('click', function(e) {
        if (!citySearch.contains(e.target) && !cityDropdown.contains(e.target)) {
            cityDropdown.style.display = 'none';
        }
    });
</script>
<script>
    document.getElementById('destinationSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('consignee_city_id').value = selectedOption.getAttribute('data-city-id') || '';
        document.getElementById('destination_source').value = selectedOption.getAttribute('data-source') || '';
    });
</script>
@endpush
<!-- @push('scripts')
<script>
    window.addEventListener('DOMContentLoaded', function() {
        const customersData = {
            @foreach($customers as $customer)
            "{{ $customer->id }}": {
                shipperCompany: "{{ $customer->customer_name }}",
                shipperName: "{{ $customer->contact_person_1 }}",
                shipperNumber: "{{ $customer->contact_no_1 }}",
                shipperEmail: "{{ $customer->email_1 }}",
                shipperAddress: `{{ $customer->address_1 }}`,
                consigneeCompany: "{{ $customer->customer_name }}",
                consigneeName: "{{ $customer->contact_person_2 }}",
                consigneeNumber: "{{ $customer->contact_no_2 }}",
                consigneeEmail: "{{ $customer->email_2 }}",
                consigneeAddress: `{{ $customer->address_2 }}`
            },
            @endforeach
        };

        const customerSelect = document.getElementById('customerSelect');

        const shipperFields = {
            shipperCompany: document.querySelector('input[name="shipperCompany"]'),
            shipperName: document.querySelector('input[name="shipperName"]'),
            shipperNumber: document.querySelector('input[name="shipperNumber"]'),
            shipperEmail: document.querySelector('input[name="shipperEmail"]'),
            shipperAddress: document.querySelector('textarea[name="shipperAddress"]')
        };

        const isEdit = {
            {
                isset($booking) ? 'true' : 'false'
            }
        };

        customerSelect?.addEventListener('change', function() {
            const customerId = this.value;
            if (customersData[customerId]) {
                const data = customersData[customerId];

                // Auto-fill shipper only if creating new booking OR field empty
                for (const key in shipperFields) {
                    if (!isEdit || !shipperFields[key].value) {
                        shipperFields[key].value = data[key] || '';
                    }
                }

                // Optional: Consignee auto-fill (same logic)
                // const consigneeFields = {
                //     consigneeCompany: document.querySelector('input[name="consigneeCompany"]'),
                //     consigneeName: document.querySelector('input[name="consigneeName"]'),
                //     consigneeNumber: document.querySelector('input[name="consigneeNumber"]'),
                //     consigneeEmail: document.querySelector('input[name="consigneeEmail"]'),
                //     consigneeAddress: document.querySelector('textarea[name="consigneeAddress"]')
                // };
                // for (const key in consigneeFields) {
                //     if(!isEdit || !consigneeFields[key].value){
                //         consigneeFields[key].value = data[key] || '';
                //     }
                // }
            }
        });

        // Trigger change on page load only if creating new booking
        if (customerSelect && customerSelect.value && !isEdit) {
            customerSelect.dispatchEvent(new Event('change'));
        }

        // ----------------------------
        // Territory -> Sales Person logic
        // ----------------------------
        const territorySelect = document.getElementById('territory');
        territorySelect?.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            document.getElementById('salesPersonName').value = selected.getAttribute('data-sales') || '';
            document.getElementById('salesPerson').value = selected.value || '';
        });

        // Pre-fill Sales Person on edit
        if (territorySelect && territorySelect.value) {
            const selected = territorySelect.options[territorySelect.selectedIndex];
            document.getElementById('salesPersonName').value = selected.getAttribute('data-sales') || '';
        }
    });
</script>
@endpush -->