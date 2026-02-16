@extends('layouts.master')
@section('title', '3PL Booking')

@section('content')
<div class="app-container">
    <!-- 🔹 Header -->
    <div class="app-hero-header d-flex align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 icon-box md border bg-white rounded-5">
                <i class="bi bi-ui-checks-grid fs-5 text-primary"></i>
            </div>
            <div>
                <h2 class="mb-1">3PL Booking</h2>
            </div>
        </div>
    </div>

    <div class="app-body">
        {{-- =================== FORM =================== --}}
        <div class="row gx-4 mb-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Add 3PL Booking</h5>
                    </div>
                    <div class="card-body">

                        {{-- ✅ Alerts --}}
                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        {{-- ✅ Form --}}
                        <form method="POST" action="{{ route('thirdparty.store') }}">
                            @csrf
                            <div class="row gx-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Book No *</label>
                                    <select name="bookNo" class="form-select" required>
                                        <option value="">Select Book No * </option>
                                        @foreach($bookingList as $b)
                                        <option value="{{ $b->bookNo }}"
                                            {{ old('bookNo') == $b->bookNo ? 'selected' : '' }}>
                                            {{ $b->bookNo }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Book Date</label>
                                    <input type="date" name="book_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">3PL Company *</label>
                                    <select name="3plCompany" class="form-select" required>

                                        <option value="Tranzo">Tranzo</option>

                                    </select>
                                </div>
                                <!-- <div class="col-md-6">
                                    <label class="form-label">3PL Ref No *</label>
                                    <input type="text" name="3plRef" class="form-control" placeholder="Enter 3PL Ref No" required>
                                </div> -->
                                <div class="col-md-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== DATA TABLE =================== --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">3PL Booking Dataa</h5>
            </div>
            <div class="card-body">

                <!-- 🔹 Table Controls -->
                <div class="table-controls modern-controls d-flex justify-content-between mb-2">
                    <div class="search-group-container">
                        <button class="search-options-button" id="searchOptionsButton">🔍</button>
                        <input type="text" id="searchInput" placeholder="Search data..." class="redesigned-search-input">

                        <!-- 🔽 Search Column Dropdown -->
                        <div class="search-options-menu" id="searchOptionsMenu">
                            <label><input type="radio" name="searchColumn" value="all" checked> All Columns</label>
                            <label><input type="radio" name="searchColumn" value="book_no"> Book No</label>
                            <label><input type="radio" name="searchColumn" value="book_date"> Book Date</label>
                            <label><input type="radio" name="searchColumn" value="company_name"> 3PL Company</label>
                            <label><input type="radio" name="searchColumn" value="ref_no"> 3PL Ref No</label>
                            <label><input type="radio" name="searchColumn" value="remarks"> Remarks</label>
                        </div>

                        <button class="go-button" onclick="filterTable()">Go</button>
                    </div>

                    <div class="right-controls">
                        @include('layouts.actions-dropdown', ['downloadRoute' => route('thirdparty.download')])
                        <button class="reset-button btn btn-outline-secondary ms-2" onclick="clearSearchAndReset()">Reset</button>
                    </div>
                </div>

                <!-- 🔹 Data Table -->
                <div class="table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr class="header-row">
                                <th class="header-cell sortable" data-column="book_no">Book No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="book_date">Book Date <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="company_name">3PL Company <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="ref_no">3PL Ref No <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="remarks">Remarks <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="customer_name">Customer Name <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="shipper_name">Shipper Name <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="consignee_name">Consignee Name <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="updated_by">Updated By <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="updated_at">Updated Date/Time <span class="sort-icon"></span></th>
                                <th class="header-cell sortable" data-column="updated_at">Action <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody id="bookingsTableBody">
                            @forelse($bookings as $booking)
                            <tr class="data-row">
                                <td class="data-cell" data-column="book_no">{{ $booking->book_no }}</td>
                                <td class="data-cell" data-column="book_date">{{ \Carbon\Carbon::parse($booking->book_date)->format('d-m-Y') }}</td>
                                <td class="data-cell" data-column="company_name">{{ $booking->company_name ?? '-' }}</td>
                                <td class="data-cell" data-column="ref_no">{{ $booking->ref_no ?? '-' }}</td>
                                <td class="data-cell" data-column="remarks">{{ $booking->remarks ?? '-' }}</td>
                                <td class="data-cell">{{ $booking->booking->customer->customer_name ?? '-' }}</td>
                                <td class="data-cell">{{ $booking->booking->shipperName ?? '-' }}</td>
                                <td class="data-cell">{{ $booking->booking->consigneeName ?? '-' }}</td>
                                <td class="data-cell">{{ $booking->updater->name ?? '-' }}</td>
                                <td class="data-cell">{{ $booking->updated_at ? $booking->updated_at->format('d-M-Y H:i:s') : '-' }}</td>
                                <td class="data-cell text-center">
                                    <!-- Edit Button -->
                                    <button
                                        class="btn btn-sm btn-primary editBtn"
                                        data-id="{{ $booking->id }}"
                                        data-tracking="{{ $booking->ref_no }}"
                                        data-remarks='@json($booking->remarks)'
                                        data-status="{{ $booking->order_status ?? '' }}">
                                        Shipper Advice
                                    </button>

                                    <!-- Payment Button -->
                                    <button
                                        class="btn btn-sm btn-success paymentBtn"
                                        data-tracking="{{ $booking->ref_no }}">
                                        Payment
                                    </button>
                                </td>


                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">No bookings found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Edit Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="#">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" id="booking_id">

                <div class="modal-header">
                    <h5 class="modal-title">Shipper Advice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- 3PL Tracking Number -->
                    <div class="mb-3">
                        <label class="form-label">3PL Tracking Number</label>
                        <input type="text" class="form-control" name="tracking_number" id="tracking_number" readonly>
                    </div>

                    <!-- Order Status (Static) -->
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select class="form-control" name="order_status" id="order_status">
                            <option value="">Select Status</option>
                            <option value="A">A</option>
                            <option value="R">R</option>
                        </select>
                    </div>

                    <!-- Last Remarks -->
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" id="remarks"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </form>

        </div>
    </div>
</div>


<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> <!-- Close button -->
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="paymentDataBody">
                        <!-- Dynamic rows will be injected here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
            </div>

        </div>
    </div>
</div>


<!-- 
//modal script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modalEl = document.getElementById('editBookingModal');
        const modal = new bootstrap.Modal(modalEl); // create instance once
        const form = modalEl.querySelector('form');
        const cancelBtn = modalEl.querySelector('.btn-secondary');

        // OPEN MODAL
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('booking_id').value = this.dataset.id;
                document.getElementById('tracking_number').value = this.dataset.tracking ?? '';
                document.getElementById('remarks').value = this.dataset.remarks ?? '';

                // Preselect current status if data-status exists
                const select = document.getElementById('order_status');
                select.value = btn.dataset.status ?? '';

                modal.show();
            });
        });

        // CANCEL BUTTON CLICK -> dismiss modal instantly
        cancelBtn.addEventListener('click', function() {
            modal.hide();
            form.reset();
        });

        // SUBMIT FORM
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // prevent default form submit

            // Hide modal immediately on submit
            modal.hide();

            const orderStatus = document.getElementById('order_status').value;
            const payload = {
                tracking_number: document.getElementById('tracking_number').value,
                order_status: orderStatus,
                remarks: document.getElementById('remarks').value
            };

            fetch(`{{ route('3pl.merchant.advice') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async res => {
                    const data = await res.json();
                    return {
                        status: res.status,
                        data
                    };
                })
                .then(({
                    status,
                    data
                }) => {

                    if (data.non_field_errors && data.non_field_errors.length) {
                        alert(data.non_field_errors[0]);
                        return;
                    }

                    if (status !== 200) {
                        alert(data.message ?? 'API error');
                        return;
                    }

                    // Success alert
                    alert('Order updated successfully');

                    // Reset form after success
                    form.reset();

                })
                .catch(err => {
                    console.error(err);
                    alert('Network / JS error');
                    form.reset();
                });
        });

    });
</script>
<!-- 
//3Pl payment api script  -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const paymentModalEl = document.getElementById('paymentModal');
        const paymentModal = new bootstrap.Modal(paymentModalEl);
        const paymentBody = document.getElementById('paymentDataBody');
        const cancelBtn = document.getElementById('cancelBtn'); // Cancel button
        const form = document.getElementById('paymentForm'); // Form inside modal (optional)

        // 🔹 CANCEL BUTTON CLICK -> dismiss modal and reset form
        cancelBtn.addEventListener('click', () => {
            paymentModal.hide();
        });


        // 🔹 Payment button click -> show modal and fetch data
        document.querySelectorAll('.paymentBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const trackingNumber = this.dataset.tracking;

                paymentBody.innerHTML = `<tr><td colspan="2" class="text-center">Loading...</td></tr>`;
                paymentModal.show();

                fetch(`/get-payment-status?tracking_number=${trackingNumber}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data || data.error) {
                            paymentBody.innerHTML = `<tr><td colspan="2" class="text-center text-danger">${data.error ?? 'No data found'}</td></tr>`;
                            return;
                        }

                        let html = '';
                        data.forEach(d => {
                            html += `
                            <tr><th>Reference Number</th><td>${d.reference_number}</td></tr>
                            <tr><th>Tracking Number</th><td>${d.tracking_number}</td></tr>
                            <tr><th>Reserve Invoice</th><td>${d.reserve_invoice_number} (${d.reserve_invoice_amount})</td></tr>
                            <tr><th>Reserve Settlement Date</th><td>${d.reserve_settlement_date}</td></tr>
                            <tr><th>Advance Invoice</th><td>${d.advance_invoice_number ?? '-'}</td></tr>
                            <tr><th>Advance Amount</th><td>${d.advance_invoice_amount ?? '-'}</td></tr>
                            <tr><th>Balance Amount</th><td>${d.balance_amount ?? '-'}</td></tr>
                            <tr><th>Net Amount</th><td>${d.net_amount ?? '-'}</td></tr>
                            <tr><th>Order Status</th><td>${d.ds_order_status}</td></tr>
                            <tr><th>Shipment Type</th><td>${d.ds_shipment_type}</td></tr>
                            <tr><td colspan="2"><hr></td></tr>
                        `;
                        });

                        paymentBody.innerHTML = html;
                    })
                    .catch(err => {
                        console.error(err);
                        paymentBody.innerHTML = `<tr><td colspan="2" class="text-center text-danger">Failed to load data</td></tr>`;
                    });
            });
        });

    });
</script>



{{-- ✅ Dashboard Modals Include --}}
@include('layouts.dashboard-modals', [
'columns' => ['book_no','book_date','company_name','ref_no','remarks','customer','shipper','consignee','updated_by','updated_at'],
'chartModel' => 'ThirdPartyBooking',
'chartAction' => route('thirdparty.chart'),
'filterAction' => route('thirdparty.index'),
'rowFilterAction' => route('thirdparty.rowFilter'),
'sortAction' => route('thirdparty.index'),
'aggregateAction' => route('thirdparty.aggregate'),
'computeAction' => route('thirdparty.compute'),
'downloadAction' => route('thirdparty.download')
])

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
<script>
    // Toggle search options menu
    document.getElementById('searchOptionsButton').addEventListener('click', function() {
        const menu = document.getElementById('searchOptionsMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });

    // Search / Filter Table
    function filterTable() {
        const filter = document.getElementById('searchInput').value.toUpperCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toUpperCase().includes(filter) ? '' : 'none';
        });
    }

    function clearSearchAndReset() {
        document.getElementById('searchInput').value = '';
        filterTable();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">


</script>
@endpush
@endsection