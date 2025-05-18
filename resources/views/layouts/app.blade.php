@php
    use Illuminate\Support\Facades\DB;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Sistem Manajemen Armada')</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --dark-color: #212529;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .sidebar {
            min-height: 100vh;
            background-color: var(--dark-color);
            transition: all 0.3s ease;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s;
            border-radius: 4px;
            margin: 5px 0;
            white-space: nowrap;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        /* Style untuk menu dropdown sidebar */
        .sidebar ul.collapse {
            padding-left: 0;
        }
        .sidebar .nav-link[data-bs-toggle="collapse"] {
            position: relative;
        }
        .sidebar .nav-link[data-bs-toggle="collapse"] .fa-chevron-down {
            position: absolute;
            right: 10px;
            transition: transform 0.3s;
        }
        .sidebar .nav-link[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
        .sidebar ul.collapse .nav-link {
            padding-left: 35px;
        }
        .content-wrapper {
            min-height: 100vh;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            font-weight: 600;
        }
        .btn-primary {
            background-color: var(--primary-color);
        }
        .dashboard-card {
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .table-responsive {
            overflow-x: auto;
        }
        /* Tambahan style untuk notifikasi */
        .dropdown-item.notification-item {
            white-space: normal;
            border-bottom: 1px solid #f0f0f0;
        }
        .dropdown-item.notification-item:last-child {
            border-bottom: none;
        }
        .notification-icon {
            min-width: 20px;
        }
        .notification-content {
            word-break: break-word;
            width: calc(100% - 25px);
            padding-right: 5px;
        }
        
        /* Responsive styles */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 80%;
                z-index: 1040;
                height: 100vh;
                overflow-y: auto;
            }
            .sidebar.show {
                left: 0;
            }
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1030;
                display: none;
            }
            .sidebar-backdrop.show {
                display: block;
            }
            .content-wrapper {
                padding: 15px 10px;
            }
            .table-responsive {
                width: 100%;
            }
            .card {
                margin-bottom: 15px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar Backdrop -->
            <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
            
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar" id="sidebar">
                <div class="d-flex flex-column flex-shrink-0 p-3 text-white">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-white text-decoration-none">
                            <i class="fas fa-truck-moving me-2"></i>
                            <span class="fs-4">Fleet Manager</span>
                        </a>
                        <button class="btn btn-sm text-white d-md-none" id="closeSidebar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        @if(auth()->user()->role !== 'approver')
                        <li>
                            <a href="{{ route('vehicle-requests.index') }}" class="nav-link {{ request()->routeIs('vehicle-requests.*') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i> Pemesanan Kendaraan
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('approver'))
                        <li>
                            <a href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                                <i class="fas fa-check-double"></i> Persetujuan
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="#vehicleSubmenu" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('vehicles.*') || request()->routeIs('maintenance.*') || request()->routeIs('assignments.*') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('vehicles.*') || request()->routeIs('maintenance.*') || request()->routeIs('assignments.*') ? 'true' : 'false' }}">
                                <i class="fas fa-car"></i> Kendaraan <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <ul class="nav collapse {{ request()->routeIs('vehicles.*') || request()->routeIs('maintenance.*') || request()->routeIs('assignments.*') ? 'show' : '' }}" id="vehicleSubmenu">
                                <li class="ms-3">
                                    <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.index') ? 'active' : '' }}">
                                        <i class="fas fa-list"></i> Daftar Kendaraan
                                    </a>
                                </li>
                                <li class="ms-3">
                                    <a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
                                        <i class="fas fa-tools"></i> Maintenance
                                    </a>
                                </li>
                                @if(auth()->user()->isAdmin())
                                <li class="ms-3">
                                    <a href="{{ route('vehicle-usage.index') }}" class="nav-link {{ request()->routeIs('vehicle-usage.index') ? 'active' : '' }}">
                                        <i class="fas fa-location-arrow"></i> Penggunaan Kendaraan
                                    </a>
                                </li>
                                <li class="ms-3">
                                    <a href="{{ route('vehicle-usage.history') }}" class="nav-link {{ request()->routeIs('vehicle-usage.history') ? 'active' : '' }}">
                                        <i class="fas fa-history"></i> Riwayat Penggunaan
                                    </a>
                                </li>
                                <li class="ms-3">
                                    <a href="{{ route('vehicle-usage.all-logs') }}" class="nav-link {{ request()->routeIs('vehicle-usage.all-logs') || request()->routeIs('vehicle-usage.logs.*') ? 'active' : '' }}">
                                        <i class="fas fa-clipboard-list"></i> Log Kendaraan
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        <li>
                            <a href="{{ route('drivers.index') }}" class="nav-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                                <i class="fas fa-id-card"></i> Driver
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i> Laporan
                            </a>
                        </li>
                        @if(auth()->user()->isAdmin())
                        <li>
                            <a href="{{ route('system-logs.index') }}" class="nav-link {{ request()->routeIs('system-logs.*') ? 'active' : '' }}">
                                <i class="fas fa-history"></i> Log Sistem
                            </a>
                        </li>
                        @endif
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'User' }}&background=random" alt="User" width="32" height="32" class="rounded-circle me-2">
                            <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content-wrapper" id="content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 rounded">
                    <div class="container-fluid">
                        <button class="navbar-toggler border-0" type="button" id="sidebarToggle">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 ms-2 d-none d-sm-block">@yield('page-title', 'Dashboard')</h5>
                            <h6 class="mb-0 ms-2 d-block d-sm-none">@yield('page-title', 'Dashboard')</h6>
                        </div>
                        <div class="d-flex">
                            <div class="dropdown">
                                <a href="#" class="position-relative text-dark me-3 dropdown-toggle" id="dropdownNotification" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell fa-lg"></i>
                                    @php
                                        $notificationCount = DB::table('notifications')
                                            ->where('user_id', auth()->id())
                                            ->where('is_read', false)
                                            ->count();
                                    @endphp
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $notificationCount ?? 0 }}
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 300px; max-height: 400px; overflow-y: auto;" aria-labelledby="dropdownNotification">
                                    @php
                                        $notifications = DB::table('notifications')
                                            ->where('user_id', auth()->id())
                                            ->where('is_read', false)
                                            ->orderBy('created_at', 'desc')
                                            ->limit(5)
                                            ->get();
                                    @endphp
                                    @if(count($notifications) > 0)
                                        <li><h6 class="dropdown-header bg-light text-center py-2">Notifikasi Terbaru</h6></li>
                                        @foreach($notifications as $notification)
                                            <li>
                                                <a class="dropdown-item py-2 notification-item" href="{{ $notification->related_to == 'vehicle_request' ? route('approvals.index') : '#' }}">
                                                    <div class="d-flex align-items-start">
                                                        <div class="notification-icon {{ $notification->type == 'success' ? 'text-success' : ($notification->type == 'warning' ? 'text-warning' : ($notification->type == 'error' ? 'text-danger' : 'text-primary')) }} me-2">
                                                            <i class="fas {{ $notification->type == 'success' ? 'fa-check-circle' : ($notification->type == 'warning' ? 'fa-exclamation-triangle' : ($notification->type == 'error' ? 'fa-times-circle' : 'fa-info-circle')) }}"></i>
                                                        </div>
                                                        <div class="notification-content">
                                                            <div class="fw-bold small">{{ $notification->title }}</div>
                                                            <div class="small text-muted">{{ Str::limit($notification->message, 100) }}</div>
                                                            <div class="small text-muted">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                        <li><hr class="dropdown-divider m-0"></li>
                                        <li><a class="dropdown-item text-center py-2 bg-light fw-bold small text-primary" href="#">Lihat Semua Notifikasi</a></li>
                                    @else
                                        <li><h6 class="dropdown-header bg-light text-center py-2">Notifikasi</h6></li>
                                        <li class="px-3 py-3 text-center">
                                            <div class="text-muted mb-2"><i class="fas fa-bell-slash fa-2x"></i></div>
                                            <div class="text-muted small">Tidak ada notifikasi baru</div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Main Content -->
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const closeSidebar = document.getElementById('closeSidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarBackdrop.classList.toggle('show');
                });
            }
            
            if (closeSidebar) {
                closeSidebar.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                });
            }
            
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                });
            }
            
            // Close sidebar when clicking on a nav link (mobile)
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        sidebar.classList.remove('show');
                        sidebarBackdrop.classList.remove('show');
                    }
                });
            });
            
            // Handle resize events
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html> 