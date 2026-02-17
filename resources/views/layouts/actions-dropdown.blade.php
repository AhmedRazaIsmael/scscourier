<div class="dropdown" style="position: relative; z-index: 1055;">
    <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu shadow">
        <!-- Filter -->
        <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#columnFilterModal">
                <i class="ti ti-filter"></i> Filter
            </a>
        </li>

        <!-- Row Filter -->
        <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#rowFilterModal">
                <i class="ti ti-filter"></i> Row Filter
            </a>
        </li>

        <!-- Data submenu -->
        <li class="dropdown dropend">
            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                <i class="ti ti-database"></i> Data
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sortModal">
                        <i class="ti ti-arrows-sort"></i> Sort
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#aggregateModal">
                        <i class="ti ti-sigma"></i> Aggregate
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#computeModal">
                        <i class="ti ti-calculator"></i> Compute
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="ti ti-clock-backward"></i> Flashback
                    </a>
                </li>
            </ul>
        </li>

        <!-- Format submenu -->
        <li class="dropdown dropend">
            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                <i class="ti ti-format"></i> Format
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#controlBreakModal">
                        Control Break
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#highlightModal">
                        Highlight
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#rowsPerPageModal">
                        Rows Per Page
                    </a>
                </li>
            </ul>
        </li>

        <!-- Group By -->
        {{-- <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#groupByModal"><i class="ti ti-table"></i> Group By</a></li> --}}

        <!-- Chart -->
        {{-- <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#chartModal"><i class="ti ti-chart-bar"></i> Chart</a></li> --}}

        <!-- Download -->
        <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#downloadModal">
                <i class="ti ti-download"></i> Download
            </a>
        </li>

        <!-- Settings -->
        <li>
            <a class="dropdown-item" href="#">
                <i class="ti ti-settings"></i> Settings
            </a>
        </li>
    </ul>
</div>