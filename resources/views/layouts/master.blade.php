<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | Airborn Courier Express</title>

    <!-- Meta -->
    <meta name="description" content="Airborn Courier Express Dashboard">
    <meta name="author" content="Airborn Courier Express">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('dashboard-assets/images/favicon.svg') }}">

    <!-- ************* CSS Files ************* -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.1/css/OverlayScrollbars.min.css">

    <!-- Main dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard-assets/css/main.min.css') }}">

    <!-- ✅ Add this line for all table pages -->
    <link rel="stylesheet" href="{{ asset('dashboard-assets/css/tables.css') }}">

    <!-- Page-specific CSS -->
    @stack('styles')
</head>

<body>

    <!-- Page Wrapper -->
    <div class="page-wrapper">

        {{-- Header --}}
        @include('layouts.header')

        <!-- Main Container -->
        <div class="main-container">

            {{-- Sidebar --}}
            @include('layouts.sidebar')

            <!-- Content Wrapper -->
            <div class="content-wrapper p-3">
                @yield('content')
            </div>

        </div>
        <!-- /Main Container -->

        {{-- Footer --}}
        {{-- @include('layouts.footer') --}}

    </div>
    <!-- /Page Wrapper -->

    <!-- ************* JavaScript Files ************* -->

    <!-- jQuery (must come first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <!-- Overlay Scrollbars -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.1/js/jquery.overlayScrollbars.min.js"></script>

    <!-- Local custom scripts if they exist -->
    @if (file_exists(public_path('dashboard-assets/js/main.min.js')))
        <script src="{{ asset('dashboard-assets/js/main.min.js') }}"></script>
    @endif

    @if (file_exists(public_path('dashboard-assets/js/custom.js')))
        <script src="{{ asset('dashboard-assets/js/custom.js') }}"></script>
    @endif

    <!-- ✅ Add this line for table sorting/search -->
    @if (file_exists(public_path('dashboard-assets/js/tables.js')))
        <script src="{{ asset('dashboard-assets/js/tables.js') }}"></script>
    @endif

    {{-- Global Dashboard Modals --}}
    @include('layouts.dashboard-modals', [
        'columns' => ['code','name','state','country','province'],
        'filterAction' => url()->current(),
        'rowFilterAction' => url()->current(),
        'computeAction' => url()->current(),
        'aggregateAction' => url()->current(),
        'downloadAction' => route('city.download'),
        'chartAction' => route('city.chart'),
        'chartModel' => 'generic'
    ])

    <!-- Expression helper -->
    <script>
        function insertToExpression(val) {
            let ta = document.querySelector('[name="compute_expression"]');
            if(ta) {
                ta.value += val;
                ta.focus();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.modal').forEach(modalEl => {
                new bootstrap.Modal(modalEl, {
                    backdrop: false // disables dark overlay
                });
            });
        });
    </script>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
