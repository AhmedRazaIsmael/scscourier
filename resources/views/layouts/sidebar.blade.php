@php
    // $permissions is injected from AppServiceProvider
    $permissions = $permissions ?? [];
@endphp

<style>
.sidebar-wrapper { position: fixed; top: 0; left: 0; height: 100vh; width: 250px; background: #f8f9fa; display: flex; flex-direction: column; overflow: hidden; z-index: 1000; }
.sidebarMenuScroll { flex: 1; overflow-y: auto; padding-right: 8px; scrollbar-width: thin; }
.sidebarMenuScroll::-webkit-scrollbar { width: 6px; }
.sidebarMenuScroll::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.2); border-radius: 10px; }
.treeview.open > .treeview-menu { display: block; }
.treeview-menu { display: none; padding-left: 20px; }
@media (max-width: 576px) { .sidebar-wrapper { width: 220px; } .sidebarMenuScroll { overflow-y: auto; } }
</style>

<nav id="sidebar" class="sidebar-wrapper">
      <div class="app-brand text-center py-3">
    <a href="{{ url('/') }}">
        <img src="{{ asset('dashboard-assets/images/logo3.png') }}" 
             class="logo" 
             alt="Logo" 
             style="width: 250px; height: 80px;"> <!-- Bigger size -->
    </a>
</div>
    <div class="sidebarMenuScroll">
        <ul class="sidebar-menu">
            <li class="sidebar-title">
                <h6 class="m-0 text-truncate fw-bold">ABC Express</h6>
            </li>

            {{-- Dashboard --}}
            @if(in_array('dashboard', $permissions))
                <li class="current-page">
                    <a href="{{ route('dashboard') }}"><i class="bi bi-box"></i><span class="menu-text">Dashboard</span></a>
                </li>
            @endif

            {{-- Book Tracking --}}
            @if(in_array('book-tracking', $permissions))
                <li><a href="{{ url('/book-tracking') }}"><i class="bi bi-pie-chart"></i><span class="menu-text">Book Tracking</span></a></li>
            @endif

            {{-- Booking --}}
            @if(in_array('booking', $permissions) || collect($permissions)->contains(function($p){ return str_starts_with($p,'booking.'); }))
                <li class="treeview">
                    <a href="#!"><i class="bi bi-window-sidebar"></i><span class="menu-text">Booking</span></a>
                    <ul class="treeview-menu">
                        @if(in_array('booking.domestic', $permissions))
                            <li><a href="{{ url('/domestic-booking') }}">Singel Booking</a></li>
                        @endif
                        <!-- @if(in_array('booking.export', $permissions))
                            <li><a href="{{ url('/export-booking') }}">Export</a></li>
                        @endif
                        @if(in_array('booking.import', $permissions))
                            <li><a href="{{ url('/import-booking') }}">Import</a></li>
                        @endif
                        @if(in_array('booking.cross-border', $permissions))
                            <li><a href="{{ url('/cross-border') }}">Cross Border</a></li>
                        @endif -->
                        @if(in_array('booking.bulk-attachments', $permissions))
                            <li><a href="{{ route('wizard.bookings.step1') }}">Bulk Booking </a></li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Label Print --}}
            @if(in_array('label-print', $permissions) || collect($permissions)->contains(fn($p) => str_starts_with($p,'label-print.')))
                <li class="treeview">
                    <a href="#!"><i class="bi bi-patch-check"></i><span class="menu-text">Label Print</span></a>
                    <ul class="treeview-menu">
                        @if(in_array('label-print.single', $permissions))
                            <li><a href="{{ url('/single-label') }}">Single Label</a></li>
                        @endif
                        @if(in_array('label-print.bulk', $permissions))
                            <li><a href="{{ url('/bulk-label') }}">Bulk Label</a></li>
                        @endif
                        <!-- @if(in_array('label-print.pdo-single', $permissions))
                            <li><a href="{{ url('/pdo-single-label') }}">POD Single Label</a></li>
                        @endif -->
                        <!-- @if(in_array('label-print.pdo-bulk', $permissions))
                            <li><a href="{{ url('/pdo-bulk-label') }}">POD Bulk Label</a></li>
                        @endif -->
                        <!-- @if(in_array('label-print.sticker', $permissions))
                            <li><a href="{{ url('/sticker-label') }}">Sticker Label</a></li>
                        @endif -->
                        <!-- @if(in_array('label-print.undertaking', $permissions))
                            <li><a href="{{ url('/undertaking-print') }}">Undertaking Label</a></li>
                        @endif -->
                    </ul>
                </li>
            @endif

            @if(in_array('label-print', $permissions) || collect($permissions)->contains(fn($p) => str_starts_with($p,'label-print.')))
                <li class="treeview">
                    <a href="#!"><i class="bi bi-patch-check"></i><span class="menu-text"> Shipments</span></a>
                    <ul class="treeview-menu">
                       @if(in_array('reports.booking-edit', $permissions))
                            <li><a href="{{ route('booking.index') }}">All Shipments </a></li>
                        @endif
                       
                    </ul>
                </li>
            @endif

            {{-- Operation --}}
            @if(in_array('operation', $permissions) || collect($permissions)->contains(fn($p) => str_starts_with($p,'operation.')))
                <li class="treeview">
                    <a href="#!"><i class="bi bi-shield-lock"></i><span class="menu-text">Operation</span></a>
                    <ul class="treeview-menu">
                        @if(in_array('operation.3pl-booking', $permissions))
                            <li><a href="{{ url('/3pl-booking') }}">3PL Booking</a></li>
                        @endif
                        <!-- @if(in_array('operation.3pl-upload', $permissions))
                            <li><a href="{{ route('3pl.upload.step1') }}">3PL Bulk Upload</a></li>
                        @endif -->

                        @if(in_array('operation.scanning.arrival', $permissions) || in_array('operation.scanning.delivery', $permissions))
                        <li class="treeview">
                            <a href="#!">Scanning <i class="bi bi-chevron-right"></i></a>
                            <ul class="treeview-menu">
                                @if(in_array('operation.scanning.arrival', $permissions))
                                    <li><a href="{{ route('scan.form', ['type' => 'arrival']) }}">Arrival Scan</a></li>
                                @endif
                                @if(in_array('operation.scanning.delivery', $permissions))
                                    <li><a href="{{ route('scan.form', ['type' => 'delivery']) }}">Out For Delivery</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif
<!-- 
                        @if(in_array('operation.assigning', $permissions))
                            <li><a href="{{ route('assigning.counter.partner') }}">Assigning Counter Partner</a></li>
                        @endif -->
                        @if(in_array('operation.edit-weight', $permissions))
                            <li><a href="{{ url('/edit-dimensional-weight') }}">Edit Dimensional Weight</a></li>
                        @endif
                        @if(in_array('operation.shipment-status', $permissions))
                            <li><a href="{{ route('booking.status') }}">Shipment Status</a></li>
                        @endif
                        <!-- @if(in_array('operation.bulk-status', $permissions))
                            <li><a href="{{ route('booking.status.editBookingStatusView') }}">Bulk Booking Status</a></li>
                        @endif -->
                    </ul>
                </li>
            @endif

            {{-- Reports --}}
            @if(in_array('reports', $permissions) || collect($permissions)->contains(fn($p) => str_starts_with($p,'reports.')))
                <li class="treeview">
                    <a href="#!"><i class="bi bi-upc-scan"></i><span class="menu-text">Reports</span></a>
                    <ul class="treeview-menu">
                        @if(in_array('reports.pending', $permissions))
                            <li><a href="{{ route('pending.shipments') }}">Pending Shipments</a></li>
                        @endif
                        @if(in_array('reports.pending', $permissions))
                            <li><a href="{{ route('pending.shipments') }}">All Order</a></li>
                        @endif
                        @if(in_array('reports.pending', $permissions))
                            <li><a href="{{ route('pending.shipments') }}">Delivered Order</a></li>
                        @endif
                        @if(in_array('reports.pending', $permissions))
                            <li><a href="{{ route('pending.shipments') }}">Return Order</a></li>
                        @endif
                     
                        <!-- @if(in_array('reports.search-data', $permissions))
                            <li><a href="{{ route('search.data') }}">Search Data</a></li>
                        @endif
                        @if(in_array('reports.booking-void', $permissions))
                            <li><a href="{{ route('booking.void.list') }}">Booking Void</a></li>
                        @endif
                        @if(in_array('reports.analysis', $permissions))
                            <li><a href="{{ route('booking.analysis') }}">Booking Analysis</a></li>
                        @endif
                        @if(in_array('reports.sales-funnel', $permissions))
                            <li><a href="{{ route('sales.funnel') }}">Sales Funnel</a></li>
                        @endif -->
                        <!-- @if(in_array('reports.manifest', $permissions))
                            <li><a href="{{ route('manifest.pl') }}">Manifest P/L</a></li>
                        @endif -->
                        <!-- @if(in_array('reports.void-booking', $permissions))
                            <li><a href="{{ route('void.bookings') }}">Void Booking</a></li>
                        @endif -->
                        <!-- @if(in_array('reports.attachments', $permissions))
                            <li><a href="{{ route('booking.attachments') }}">Booking Attachments</a></li>
                        @endif -->
                    </ul>
                </li>
            @endif

            {{-- Financials --}}
            @if(in_array('financials', $permissions) || collect($permissions)->contains(fn($p) => str_starts_with($p,'financials.')))
                <li class="treeview">
                    <a href="#!"><i class="bi bi-window-sidebar"></i><span class="menu-text">Financials</span></a>
                    <ul class="treeview-menu">
                        @if(in_array('financials.shipment-cost', $permissions))
                            <li><a href="{{ route('shipment.cost') }}">Shipment Costing</a></li>
                        @endif
                        @if(in_array('financials.invoicing', $permissions))
                            <li><a href="{{ route('invoicing.index') }}">Invoicing</a></li>
                        @endif
                        @if(in_array('financials.dashboard', $permissions))
                            <li><a href="{{ route('financial.dashboard') }}">Financial Dashboard</a></li>
                        @endif
                        @if(in_array('financials.shipment-sale', $permissions))
                            <li><a href="{{ route('shipment.sale') }}">Shipment Sale</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Master Setup --}}
            @if(in_array('master-setup', $permissions) || collect($permissions)->contains(fn($p) => str_starts_with($p,'master-setup.')))
                <li class="treeview">
                    <a href="#!"><i class="bi bi-gear-fill"></i><span class="menu-text">Master Setup</span></a>
                    <ul class="treeview-menu">
                        <!-- @if(in_array('master-setup.city', $permissions))
                            <li><a href="{{ route('city.index') }}">Add City</a></li>
                        @endif -->
                        @if(in_array('master-setup.customer', $permissions))
                            <li><a href="{{ route('customer.index') }}">Add Customer</a></li>
                        @endif
                        @if(in_array('master-setup.user', $permissions))
                            <li><a href="{{ route('users.index') }}">Add User</a></li>
                        @endif
                    </ul>
                </li>
            @endif

        </ul>
    </div>
</nav>
