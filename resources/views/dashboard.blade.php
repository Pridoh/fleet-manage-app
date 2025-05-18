@extends('layouts.app')

@section('title', 'Dashboard - Sistem Manajemen Armada')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistik Utama -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card dashboard-card shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Kendaraan</h6>
                                <h4 class="mb-0">{{ $totalVehicles ?? 0 }}</h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-car fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-success">
                                <i class="fas fa-circle"></i> {{ $availableVehicles ?? 0 }} tersedia
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pemesanan Baru</h6>
                                <h4 class="mb-0">{{ $newRequests ?? 0 }}</h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt"></i> Hari ini
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Persetujuan Tertunda</h6>
                                <h4 class="mb-0">{{ $pendingApprovals ?? 0 }}</h4>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clipboard-check fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-hourglass-half"></i> Menunggu persetujuan
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total BBM (L)</h6>
                                <h4 class="mb-0">{{ $totalFuel ?? 0 }}</h4>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-gas-pump fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar-week"></i> Bulan ini
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Penggunaan Kendaraan -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Penggunaan Kendaraan</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary active" data-period="weekly">Mingguan</button>
                    <button class="btn btn-sm btn-outline-secondary" data-period="monthly">Bulanan</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="vehicleUsageChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Daftar Maintenance Kendaraan -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Jadwal Maintenance</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if(isset($maintenanceSchedules) && count($maintenanceSchedules) > 0)
                        @foreach($maintenanceSchedules as $schedule)
                            <a href="{{ route('maintenance.show', $schedule) }}" class="list-group-item list-group-item-action p-3">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <h6 class="mb-1">{{ $schedule->vehicle->registration_number ?? 'Kendaraan' }}</h6>
                                    @php
                                        $statusClass = [
                                            'scheduled' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ][$schedule->status] ?? 'secondary';
                                        
                                        $statusLabel = [
                                            'scheduled' => 'Dijadwalkan',
                                            'in_progress' => 'Dalam Proses',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ][$schedule->status] ?? ucfirst($schedule->status);
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <p class="mb-1 small">
                                    <i class="fas fa-car text-primary mr-1"></i>
                                    {{ $schedule->vehicle->brand }} {{ $schedule->vehicle->model }}
                                </p>
                                <p class="mb-1 small text-muted">
                                    @php
                                        $typeLabel = [
                                            'routine' => 'Service Rutin',
                                            'repair' => 'Perbaikan',
                                            'inspection' => 'Inspeksi',
                                            'emergency' => 'Darurat'
                                        ][$schedule->maintenance_type] ?? ucfirst($schedule->maintenance_type);
                                    @endphp
                                    <i class="fas fa-tools mr-1"></i> {{ $typeLabel }}
                                </p>
                                <small>
                                    <i class="far fa-calendar-alt"></i> 
                                    {{ isset($schedule->start_date) ? \Carbon\Carbon::parse($schedule->start_date)->format('d M Y') : 'Belum dijadwalkan' }}
                                </small>
                            </a>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <p class="mb-0 text-muted">Tidak ada jadwal maintenance</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-primary">
                    Lihat Semua
                </a>
            </div>
        </div>
    </div>

    <!-- Permintaan Kendaraan Terbaru -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Permintaan Kendaraan Terbaru</h5>
                @if(auth()->user()->role !== 'approver')
                <a href="{{ route('vehicle-requests.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Pemohon</th>
                                <th>Dari</th>
                                <th>Ke</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($latestRequests) && count($latestRequests) > 0)
                                @foreach($latestRequests as $request)
                                    <tr>
                                        <td>{{ $request->requester->name ?? 'User' }}</td>
                                        <td>{{ $request->pickupLocation->name ?? 'Lokasi Awal' }}</td>
                                        <td>{{ $request->destinationLocation->name ?? 'Tujuan' }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $request->status === 'approved' ? 'success' : 
                                                ($request->status === 'pending' ? 'warning' : 
                                                ($request->status === 'rejected' ? 'danger' : 'secondary')) 
                                            }}">
                                                {{ 
                                                    $request->status === 'approved' ? 'Disetujui' : 
                                                    ($request->status === 'pending' ? 'Menunggu' : 
                                                    ($request->status === 'rejected' ? 'Ditolak' : $request->status)) 
                                                }}
                                            </span>
                                        </td>
                                        <td>{{ isset($request->created_at) ? \Carbon\Carbon::parse($request->created_at)->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center py-3">Tidak ada permintaan kendaraan</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Konsumsi BBM -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <h5 class="mb-0">Konsumsi BBM per Kendaraan</h5>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="fuelDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="fuelDropdown">
                        <div class="dropdown-header">Opsi Grafik:</div>
                        <a class="dropdown-item" href="#">Lihat Detail</a>
                        <a class="dropdown-item" href="#">Unduh Laporan</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="fuelChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Informasi Kendaraan -->
</div>
@endsection

@push('scripts')
<script>
    // Setup untuk Grafik Penggunaan Kendaraan
    const usageCtx = document.getElementById('vehicleUsageChart').getContext('2d');
    const usageChart = new Chart(usageCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($weeklyLabels) !!}, // Mengambil label dari controller
            datasets: [{
                label: 'Total Penggunaan',
                data: {!! json_encode($weeklyData) !!}, // Mengambil data dari controller
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: '#0d6efd',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Data untuk grafik mingguan dan bulanan
    const weeklyData = {
        labels: {!! json_encode($weeklyLabels) !!},
        datasets: [{
            label: 'Total Penggunaan',
            data: {!! json_encode($weeklyData) !!},
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            borderColor: '#0d6efd',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    };

    const monthlyData = {
        labels: {!! json_encode($monthlyLabels) !!},
        datasets: [{
            label: 'Total Penggunaan',
            data: {!! json_encode($monthlyData) !!},
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            borderColor: '#0d6efd',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    };

    // Event listener untuk tombol periode
    document.querySelectorAll('[data-period]').forEach(button => {
        button.addEventListener('click', function() {
            const period = this.getAttribute('data-period');
            document.querySelectorAll('[data-period]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update chart berdasarkan periode yang dipilih
            if (period === 'weekly') {
                usageChart.data = weeklyData;
                usageChart.options.scales.x.title = {
                    display: true,
                    text: 'Hari'
                };
            } else if (period === 'monthly') {
                usageChart.data = monthlyData;
                usageChart.options.scales.x.title = {
                    display: true,
                    text: 'Bulan'
                };
            }
            
            usageChart.update();
        });
    });

    // Setup untuk Grafik Konsumsi BBM
    const fuelCtx = document.getElementById('fuelChart').getContext('2d');
    const fuelChart = new Chart(fuelCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($fuelLabels) !!}, // Mengambil label dari controller
            datasets: [{
                data: {!! json_encode($fuelData) !!}, // Mengambil data dari controller
                backgroundColor: [
                    '#0d6efd',
                    '#6f42c1',
                    '#20c997',
                    '#fd7e14',
                    '#6c757d'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} L (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush 