<style>
/* ================= HEADER START ================= */

/* --- Header container --- */
.app-header {
    display: grid;
    grid-template-columns: auto 1fr auto; /* left | center | right */
    align-items: center;
    background: #ffffff;
    border-bottom: 1px solid #eaeaea;
    padding: 0.25rem 1rem;
    position: sticky;
    top: -1px;
    height: 10px !important;
}

/* --- Sidebar buttons --- */
.pin-sidebar button,
.toggle-sidebar button {
    background-color: #0d6efd;
    border: none;
    height: 38px;
    width: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

/* --- Center Logo --- */
.app-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.app-brand img.logo {
    display: block;
    max-height: 80px;
    width: 80px;
    object-fit: contain;
    vertical-align: middle;
}

@media (max-width: 576px) {
    .app-brand img.logo {
        max-height: 38px;
    }
}

/* --- Right side (search + user) --- */
.header-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}

/* --- Search box --- */
.search-container {
    position: relative;
}

.search-container input {
    padding-right: 2rem;
    height: 36px;
    font-size: 0.9rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

/* Hover effect */
.search-container input:hover {
    border-color: #0d6efd;
    box-shadow: 0 0 5px rgba(13, 110, 253, 0.3);
}

/* Focus effect (on click) */
.search-container input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.5);
    outline: none;
}

/* Search icon animation */
.search-container i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    transition: color 0.2s ease, transform 0.2s ease;
}

.search-container input:focus + i,
.search-container input:hover + i {
    color: #0d6efd;
    transform: translateY(-50%) scale(1.1);
}

/* --- Dropdown menu --- */
.dropdown-menu {
    position: absolute !important;
    z-index: 9999 !important;
}
.user-header {
    border-bottom: 1px solid #eaeaea;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}
.dropdown-item {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    transition: background-color 0.2s ease;
}
.dropdown-item:hover {
    background-color: #f5f5f5;
}
/* ================= HEADER END ================= */
</style>

<div class="app-header">
    <!-- LEFT: Sidebar Buttons -->
    <div class="d-flex align-items-center gap-2">
        <div class="pin-sidebar">
            <button type="button" class="btn btn-primary rounded-2">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <div class="toggle-sidebar d-lg-none">
            <button type="button" class="btn btn-primary rounded-2">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>

   

    <!-- RIGHT: Search + User -->
    <div class="header-actions">
        <div class="search-container d-none d-lg-block">
            <input type="text" class="form-control" id="searchAny" placeholder="Search">
            <i class="bi bi-search"></i>
        </div>

        <div class="dropdown">
            <a id="userSettings" class="dropdown-toggle d-flex align-items-center py-1 avatar-box" href="#"
               data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ asset('dashboard-assets/images/user3.png') }}" class="rounded-circle img-3x" alt="User">
                <span class="status online"></span>
            </a>

            <div class="dropdown-menu dropdown-menu-end shadow-lg p-3">
                @php
                    $user = Auth::user();
                @endphp

                <div class="user-header d-flex align-items-center mb-3">
                    <img src="{{ asset('dashboard-assets/images/user3.png') }}" class="rounded-circle img-3x me-2" alt="User">
                    <div>
                        <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                        <small class="text-muted">{{ $user->role->name ?? 'User' }}</small>
                    </div>
                </div>

                <a class="dropdown-item d-flex align-items-center py-2 border mb-1" href="{{ route('users.index') }}">
                    <i class="bi bi-person-circle me-2 text-primary"></i>
                    <span>My Profile</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center py-2 border w-100 text-start">
                        <i class="bi bi-box-arrow-right me-2 text-primary"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
