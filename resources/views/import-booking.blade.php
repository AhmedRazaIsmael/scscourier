@extends('layouts.master')

@section('title', isset($booking) ? 'Edit Import Booking' : 'Import Booking')

@section('content')
<div class="app-container">

    <!-- Header -->
    <div class="app-hero-header d-flex align-items-center">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-box-seam fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">{{ isset($booking) ? 'Edit Import Booking' : 'Import Booking' }}</h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        <div class="row gx-4">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">{{ isset($booking) ? 'Edit Import Booking' : 'Import Booking Form' }}</h5>
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

                            <input type="hidden" name="bookingType" value="import">

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

                            {{-- Customer / Service / Channel --}}
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
                                        <option value="document" {{ old('service', $booking->service ?? '') == 'document' ? 'selected' : '' }}>Document</option>
                                        <option value="express" {{ old('service', $booking->service ?? '') == 'express' ? 'selected' : '' }}>Express</option>
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

                            {{-- Payment / Location --}}
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
                                    <label class="form-label">Origin<span class="text-danger">*</span></label>
                                    <select name="origin" class="form-select" required>
                                        <option value="" disabled {{ old('origin', $booking->origin ?? '') ? '' : 'selected' }}>Select Your Origin</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ old('origin', $booking->origin ?? '') == $country->name ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Destination<span class="text-danger">*</span></label>
                                    <select name="destination" class="form-select" required>
                                        <option value="" disabled {{ old('destination', $booking->destination ?? '') ? '' : 'selected' }}>Select Your Destination</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ old('destination', $booking->destination ?? '') == $country->name ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Shipment --}}
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
                                    <input type="number" step="any"  name="weight" class="form-control" value="{{ old('weight', $booking->weight ?? '') }}">
                                </div>
                            </div>

                            <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Pieces</label>
                                    <input type="number"  step="any" name="pieces" class="form-control" value="{{ old('pieces', $booking->pieces ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Length</label>
                                    <input type="number" step="any"  name="length" class="form-control" value="{{ old('length', $booking->length ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Width</label>
                                    <input type="number" step="any"  name="width" class="form-control" value="{{ old('width', $booking->width ?? '') }}">
                                </div>
                            </div>

                            <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Height</label>
                                    <input type="number" step="any"  name="height" class="form-control" value="{{ old('height', $booking->height ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Dimensional Weight</label>
                                    <input type="number" step="any"  name="dimensionalWeight" class="form-control" value="{{ old('dimensionalWeight', $booking->dimensionalWeight ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Order No</label>
                                    <input type="text" name="orderNo" class="form-control" value="{{ old('orderNo', $booking->orderNo ?? '') }}">
                                </div>
                            </div>

                            <div class="row gx-4 mb-4">
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
                               <div class="col-md-4">
                                    <label class="form-label">COD Amount</label>
                                    <input type="number" name="codAmount" class="form-control" value="{{ old('codAmount', $booking->codAmount ?? '') }}">
                               </div>
                            </div>

                            <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Item Detail</label>
                                    <textarea name="itemDetail" class="form-control" rows="3">{{ old('itemDetail', $booking->itemDetail ?? '') }}</textarea>
                                </div>
                            </div>

                            {{-- Shipper & Consignee --}}
                            @foreach(['shipper', 'consignee'] as $type)
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
                            @endforeach

                            {{-- Remarks & Instructions --}}
                            <div class="row gx-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control">{{ old('remarks', $booking->remarks ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pickup Instructions</label>
                                    <textarea name="pickupInstructions" class="form-control">{{ old('pickupInstructions', $booking->pickupInstructions ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="row gx-4 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Delivery Instructions</label>
                                    <textarea name="deliveryInstructions" class="form-control" rows="3">{{ old('deliveryInstructions', $booking->deliveryInstructions ?? '') }}</textarea>
                                </div>
                            </div>

                            {{-- Territory / Sales / Rate --}}
                            <div class="row gx-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Territory</label>
                                    <select name="territory" id="territory" class="form-select">
                                        <option selected disabled>Choose...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" data-sales="{{ $user->name }}" {{ old('territory', isset($booking) ? $booking->territory : '') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Sales Person</label>
                                    <input type="text" id="salesPersonName" class="form-control" value="{{ old('salesPersonName', isset($booking) && $booking->salesPerson ? ($users->find($booking->salesPerson)->name ?? '') : '') }}" readonly>
                                    <input type="hidden" name="salesPerson" id="salesPerson" value="{{ old('salesPerson', isset($booking) ? $booking->salesPerson : '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Rate Type</label>
                                    <input type="text" name="rateType" class="form-control" value="{{ old('rateType', $booking->rateType ?? '') }}">
                                </div>
                            </div>

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
window.addEventListener('DOMContentLoaded', function() {
    const customersData = {
        @foreach($customers as $customer)
            "{{ $customer->id }}": {
                consigneeCompany: "{{ $customer->customer_name }}",
                consigneeName: "{{ $customer->contact_person_1 }}",
                consigneeNumber: "{{ $customer->contact_no_1 }}",
                consigneeEmail: "{{ $customer->email_1 }}",
                consigneeAddress: `{{ $customer->address_1 }}`
            },
        @endforeach
    };

    const customerSelect = document.getElementById('customerSelect');

    const fields = {
        consigneeCompany: document.querySelector('input[name="consigneeCompany"]'),
        consigneeName: document.querySelector('input[name="consigneeName"]'),
        consigneeNumber: document.querySelector('input[name="consigneeNumber"]'),
        consigneeEmail: document.querySelector('input[name="consigneeEmail"]'),
        consigneeAddress: document.querySelector('textarea[name="consigneeAddress"]')
    };

    const isEdit = {{ isset($booking) ? 'true' : 'false' }};

    customerSelect?.addEventListener('change', function () {
        const customerId = this.value;

        if(customersData[customerId]){
            const data = customersData[customerId];

            // ✅ Auto-fill only if field is empty OR creating new booking
            for (const key in fields) {
                if(!isEdit || !fields[key].value) {
                    fields[key].value = data[key] || '';
                }
            }
        }
    });

    // Trigger change on page load only for new booking
    if(customerSelect && customerSelect.value && !isEdit){
        customerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
