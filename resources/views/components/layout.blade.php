<!DOCTYPE html>
<html lang="en" data-sidenav-size="sm-hover">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Booking Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    
    <!-- gridjs css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/gridjs/theme/mermaid.min.css') }}">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">

    <!-- One of the following themes -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/@simonwep/pickr/themes/classic.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/@simonwep/pickr/themes/monolith.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/@simonwep/pickr/themes/nano.min.css') }}">

    <!-- Theme Config Js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- Vendor css -->
    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- Sidenav Menu Start -->
        <div class="sidenav-menu">

            <!-- Brand Logo -->
            <a href="/" class="logo">
                <span class="logo-light">
                    <span class="logo-lg">Logo Here</span>
                    <span class="logo-sm text-center">Logo</span>
                </span>

                <span class="logo-dark">
                    <span class="logo-lg">Logo Here</span>
                    <span class="logo-sm text-center">Logo</span>
                </span>
            </a>

            <!-- Sidebar Hover Menu Toggle Button -->
            <button class="button-sm-hover">
                <i class="ti ti-circle align-middle"></i>
            </button>

            <!-- Full Sidebar Menu Close Button -->
            <button class="button-close-fullsidebar">
                <i class="ti ti-x align-middle"></i>
            </button>

            <div data-simplebar>

                <!--- Sidenav Menu -->
                <ul class="side-nav">

                    <li class="side-nav-item">
                        <a href="/" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                            <span class="menu-text"> Dashboard </span>
                        </a>
                    </li>

                    {{-- <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarMaster" aria-expanded="false" aria-controls="sidebarMaster" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-settings"></i></span>
                            <span class="menu-text"> Master Setup </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarMaster">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="/master/city" class="side-nav-link">
                                        <span class="menu-text">City</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/master/customer" class="side-nav-link">
                                        <span class="menu-text">Customer</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li> --}}
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarMaster" aria-expanded="false" aria-controls="sidebarMaster" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-settings"></i></span>
                            <span class="menu-text"> Master Setup </span>
                            <span class="menu-arrow"></span> <!-- ✅ This enables arrow -->
                        </a>
                        <div class="collapse" id="sidebarMaster">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="/master/city" class="side-nav-link">
                                        <span class="menu-text">City</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/master/customer" class="side-nav-link">
                                        <span class="menu-text">Customer</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a href="/book-tracking" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-message-filled"></i></span>
                            <span class="menu-text"> Book Tracking </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarBooking" aria-expanded="false" aria-controls="sidebarBooking" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-basket-filled"></i></span>
                            <span class="menu-text"> Booking </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarBooking">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="/domestic-booking" class="side-nav-link">
                                        <span class="menu-text">Domestic</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/export-booking" class="side-nav-link">
                                        <span class="menu-text">Export</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/import-booking" class="side-nav-link">
                                        <span class="menu-text">Import</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/cross-border" class="side-nav-link">
                                        <span class="menu-text">Cross Border</span>
                                    </a>
                                </li>
                                  <li class="side-nav-item">
                                    <a href="{{ route('wizard.bookings.step1') }}" class="side-nav-link">
                                        <span class="menu-text">Bulk Booking Attachments</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarInvoice" aria-expanded="false" aria-controls="sidebarInvoice" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
                            <span class="menu-text"> Label Print</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarInvoice">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="/single-label" class="side-nav-link">
                                        <span class="menu-text">Single Label</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/bulk-label" class="side-nav-link">
                                        <span class="menu-text">Bulk Label</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/pdo-single-label" class="side-nav-link">
                                        <span class="menu-text">PDO Single Label</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/pdo-bulk-label" class="side-nav-link">
                                        <span class="menu-text">PDO Bulk Label</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/sticker-label" class="side-nav-link">
                                        <span class="menu-text">Sticker Label</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/undertaking-print" class="side-nav-link">
                                        <span class="menu-text">Undertaking Print</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPages" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-files"></i></span>
                            <span class="menu-text"> Operation </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPages">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="/3pl-booking" class="side-nav-link">
                                        <span class="menu-text">3PL Booking</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('3pl.upload.step1') }}" class="side-nav-link">
                                        <span class="menu-text">3PL Bulk Upload</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('booking.index') }}" class="side-nav-link">
                                        <span class="menu-text">Search Data</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('scan.form', ['type' => 'arrival']) }}" class="side-nav-link">
                                        <span class="menu-text">Arrival Scan</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('scan.form', ['type' => 'delivery']) }}" class="side-nav-link">
                                        <span class="menu-text">Out of Delivery</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('assigning.counter.partner') }}" class="side-nav-link">
                                        <span class="menu-text">Assigning Counter Partner</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/edit-dimensional-weight" class="side-nav-link">
                                        <span class="menu-text">Edit Dimensional Weight</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/booking-status" class="side-nav-link">
                                        <span class="menu-text">Shipment Status</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="/bulk-booking-status" class="side-nav-link">
                                        <span class="menu-text">Bulk Booking Status</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPagesAuth" aria-expanded="false" aria-controls="sidebarPagesAuth" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-lock-filled"></i></span>
                            <span class="menu-text"> Reports</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPagesAuth">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="{{ route('pending.shipments') }}" class="side-nav-link">
                                        <span class="menu-text">Pending Shipments</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('booking.index') }}" class="side-nav-link {{ request()->routeIs('booking.index') ? 'active' : '' }}">
                                        <span class="menu-text">Booking Edit</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('booking.void.list') }}" class="side-nav-link">
                                        <span class="menu-text">Booking Void</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('booking.analysis') }}" class="side-nav-link">
                                        <span class="menu-text">Booking Analysis</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('sales.funnel') }}" class="side-nav-link">
                                        <span class="menu-text">Sales Funnel</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('manifest.pl') }}" class="side-nav-link">
                                        <span class="menu-icon"><i class="ti ti-report-money"></i></span>
                                        <span class="menu-text">Manifest P/L</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPagesError" aria-expanded="false" aria-controls="sidebarPagesError" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-server-2"></i></span>
                            <span class="menu-text"> Financials </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPagesError">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                   <a href="{{ route('shipment.cost') }}" class="side-nav-link">
                                        <span class="menu-text">Shipment Costing</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="{{ route('invoicing.index') }}" class="side-nav-link">
                                        <span class="menu-text">Invoicing</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarBaseUI" aria-expanded="false" aria-controls="sidebarBaseUI" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-brightness-filled"></i></span>
                            <span class="menu-text"> Inquiry </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarBaseUI">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="ui-accordions.html" class="side-nav-link">
                                        <span class="menu-text">Inquiry Dashboard</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="ui-alerts.html" class="side-nav-link">
                                        <span class="menu-text">Customer Inquiry</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <div class="clearfix"></div>
            </div>
        </div>
        <!-- Sidenav Menu End -->

        <!-- Topbar Start -->
        <header class="app-topbar">
            <div class="page-container topbar-menu">
                <div class="d-flex align-items-center gap-2">

                    <!-- Brand Logo -->
                    <a href="/" class="logo">
                        <span class="logo-light">
                            <span class="logo-lg"><img src="{{ asset('assets/images/logo.png') }}" alt="logo"></span>
                            <span class="logo-sm"><img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo"></span>
                        </span>

                        <span class="logo-dark">
                            <span class="logo-lg"><img src="{{ asset('assets/images/logo-dark.png') }}" alt="dark logo"></span>
                            <span class="logo-sm"><img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo"></span>
                        </span>
                    </a>

                    <!-- Sidebar Menu Toggle Button -->
                    <button class="sidenav-toggle-button btn btn-secondary btn-icon">
                        <i class="ti ti-menu-deep fs-24"></i>
                    </button>

                    <!-- Horizontal Menu Toggle Button -->
                    <button class="topnav-toggle-button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                        <i class="ti ti-menu-deep fs-22"></i>
                    </button>

                    <!-- Button Trigger Search Modal -->
                    <div class="topbar-search text-muted d-none d-xl-flex gap-2 align-items-center" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                        <i class="ti ti-search fs-18"></i>
                        <span class="me-2">Search something..</span>
                        <button type="submit" class="ms-auto btn btn-sm btn-primary shadow-none">⌘K</button>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <!-- Search for small devices -->
                    <div class="topbar-item d-flex d-xl-none">
                        <button class="topbar-link btn btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                            <i class="ti ti-search fs-22"></i>
                        </button>
                    </div>

                    <!-- Button Trigger Customizer Offcanvas -->
                    <div class="topbar-item d-none d-sm-flex">
                        <button class="topbar-link btn btn-outline-primary btn-icon" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button">
                            <i class="ti ti-settings fs-22"></i>
                        </button>
                    </div>

                    <!-- Light/Dark Mode Button -->
                    <div class="topbar-item d-none d-sm-flex">
                        <button class="topbar-link btn btn-outline-primary btn-icon" id="light-dark-mode" type="button">
                            <i class="ti ti-moon fs-22"></i>
                        </button>
                    </div>

                    <!-- User Dropdown -->
                    <div class="topbar-item">
                        <div class="dropdown">
                            <a class="topbar-link btn btn-outline-primary dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" aria-haspopup="false" aria-expanded="false">
                                <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" width="24" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                                <span class="d-lg-flex flex-column gap-1 d-none">
                                    Isaac G.
                                </span>
                                <i class="ti ti-chevron-down d-none d-lg-block align-middle ms-2"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="dropdown-header noti-title">
                                    <h6 class="text-overflow m-0">Welcome !</h6>
                                </div>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="ti ti-user-hexagon me-1 fs-17 align-middle"></i>
                                    <span class="align-middle">My Account</span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="ti ti-settings me-1 fs-17 align-middle"></i>
                                    <span class="align-middle">Settings</span>
                                </a>
                                <div class="dropdown-divider"></div>
                               <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                    
                                    <a href="#" class="dropdown-item active fw-semibold text-danger"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                        <span class="align-middle">Sign Out</span>
                                    </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Topbar End -->

        <!-- Search Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-transparent">
                    <div class="card mb-0 shadow-none">
                        <div class="px-3 py-2 d-flex flex-row align-items-center" id="top-search">
                            <i class="ti ti-search fs-22"></i>
                            <input type="search" class="form-control border-0" id="search-modal-input" placeholder="Search for actions, people,">
                            <button type="button" class="btn p-0" data-bs-dismiss="modal" aria-label="Close">[esc]</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @yield('content')

    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <!-- gridjs js -->
    <script src="{{ asset('assets/vendor/gridjs/gridjs.umd.js') }}"></script>

    <script>

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];
    document.getElementById("bookDate").value = today;
    </script>

    @yield('script')

</body>

</html>

