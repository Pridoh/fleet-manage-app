@extends('layouts.app')

@section('title', 'Laporan - Sistem Manajemen Armada')

@section('page-title', 'Laporan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link {{ request('report_type') != 'fuel' && request('report_type') != 'maintenance' ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    Pemesanan Kendaraan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('report_type') == 'fuel' ? 'active' : '' }}" href="{{ route('reports.index', ['report_type' => 'fuel']) }}">
                    Konsumsi BBM
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('report_type') == 'maintenance' ? 'active' : '' }}" href="{{ route('reports.index', ['report_type' => 'maintenance']) }}">
                    Maintenance
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <form action="{{ route('reports.index') }}" method="GET" class="row g-3">
                            <input type="hidden" name="report_type" value="{{ request('report_type', 'requests') }}">
                            
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                            </div>
                            
                            @if(request('report_type') != 'fuel' && request('report_type') != 'maintenance')
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="department_id" class="form-label">Departemen</label>
                                    <select name="department_id" id="department_id" class="form-select">
                                        <option value="">Semua Departemen</option>
                                        @foreach($departments ?? [] as $department)
                                            <option value="{{ $department->department_id }}" {{ request('department_id') == $department->department_id ? 'selected' : '' }}>
                                                {{ $department->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif(request('report_type') == 'fuel')
                                <div class="col-md-3">
                                    <label for="vehicle_id" class="form-label">Kendaraan</label>
                                    <select name="vehicle_id" id="vehicle_id" class="form-select">
                                        <option value="">Semua Kendaraan</option>
                                        @foreach($vehicles ?? [] as $vehicle)
                                            <option value="{{ $vehicle->vehicle_id }}" {{ request('vehicle_id') == $vehicle->vehicle_id ? 'selected' : '' }}>
                                                {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->registration_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="department_id" class="form-label">Departemen</label>
                                    <select name="department_id" id="department_id" class="form-select">
                                        <option value="">Semua Departemen</option>
                                        @foreach($departments ?? [] as $department)
                                            <option value="{{ $department->department_id }}" {{ request('department_id') == $department->department_id ? 'selected' : '' }}>
                                                {{ $department->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif(request('report_type') == 'maintenance')
                                <div class="col-md-3">
                                    <label for="vehicle_id" class="form-label">Kendaraan</label>
                                    <select name="vehicle_id" id="vehicle_id" class="form-select">
                                        <option value="">Semua Kendaraan</option>
                                        @foreach($vehicles ?? [] as $vehicle)
                                            <option value="{{ $vehicle->vehicle_id }}" {{ request('vehicle_id') == $vehicle->vehicle_id ? 'selected' : '' }}>
                                                {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->registration_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="maintenance_type" class="form-label">Jenis Maintenance</label>
                                    <select name="maintenance_type" id="maintenance_type" class="form-select">
                                        <option value="">Semua Jenis</option>
                                        <option value="routine" {{ request('maintenance_type') == 'routine' ? 'selected' : '' }}>Service Rutin</option>
                                        <option value="repair" {{ request('maintenance_type') == 'repair' ? 'selected' : '' }}>Perbaikan</option>
                                        <option value="inspection" {{ request('maintenance_type') == 'inspection' ? 'selected' : '' }}>Inspeksi</option>
                                        <option value="emergency" {{ request('maintenance_type') == 'emergency' ? 'selected' : '' }}>Darurat</option>
                                    </select>
                                </div>
                            @endif
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('reports.index', ['report_type' => request('report_type')]) }}" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                                <button type="button" class="btn btn-success float-end" id="exportExcel">
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grafik -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            @if(request('report_type') == 'fuel')
                                Grafik Konsumsi BBM
                            @elseif(request('report_type') == 'maintenance')
                                Grafik Maintenance
                            @else
                                Grafik Pemesanan Kendaraan
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="mainChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabel -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            @if(request('report_type') == 'fuel')
                                Data Konsumsi BBM
                            @elseif(request('report_type') == 'maintenance')
                                Data Maintenance
                            @else
                                Data Pemesanan Kendaraan
                            @endif
                        </h5>
                        <span class="badge bg-primary">Total: {{ $reportData->total() ?? 0 }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            @if(request('report_type') == 'fuel')
                                <!-- Tabel Konsumsi BBM -->
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kendaraan</th>
                                            <th>Driver</th>
                                            <th>Tanggal</th>
                                            <th>BBM (Liter)</th>
                                            <th>Odometer Awal</th>
                                            <th>Odometer Akhir</th>
                                            <th>Jarak (km)</th>
                                            <th>Efisiensi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($reportData) && count($reportData) > 0)
                                            @foreach($reportData as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->registration_number }}</strong><br>
                                                        <small class="text-muted">{{ $item->brand }} {{ $item->model }}</small>
                                                    </td>
                                                    <td>{{ $item->driver_name ?? '-' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                                                    <td>{{ number_format($item->fuel_used, 1) }}</td>
                                                    <td>{{ number_format($item->start_odometer) }}</td>
                                                    <td>{{ number_format($item->end_odometer) }}</td>
                                                    <td>{{ number_format($item->end_odometer - $item->start_odometer) }}</td>
                                                    <td>
                                                        @if($item->fuel_used > 0)
                                                            {{ number_format(($item->end_odometer - $item->start_odometer) / $item->fuel_used, 1) }} km/L
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @elseif(request('report_type') == 'maintenance')
                                <!-- Tabel Maintenance -->
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kendaraan</th>
                                            <th>Jenis</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                            <th>Odometer</th>
                                            <th>Deskripsi</th>
                                            <th>Biaya (Rp)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($reportData) && count($reportData) > 0)
                                            @foreach($reportData as $item)
                                                @php
                                                    $statusClass = [
                                                        'scheduled' => 'warning',
                                                        'in_progress' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ][$item->status] ?? 'secondary';
                                                    
                                                    $statusLabel = [
                                                        'scheduled' => 'Dijadwalkan',
                                                        'in_progress' => 'Dalam Proses',
                                                        'completed' => 'Selesai',
                                                        'cancelled' => 'Dibatalkan'
                                                    ][$item->status] ?? ucfirst($item->status);
                                                    
                                                    $typeLabel = [
                                                        'routine' => 'Service Rutin',
                                                        'repair' => 'Perbaikan',
                                                        'inspection' => 'Inspeksi',
                                                        'emergency' => 'Darurat'
                                                    ][$item->maintenance_type] ?? ucfirst($item->maintenance_type);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->registration_number }}</strong><br>
                                                        <small class="text-muted">{{ $item->brand }} {{ $item->model }}</small>
                                                    </td>
                                                    <td>{{ $typeLabel }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($item->start_date)->format('d/m/Y') }}</td>
                                                    <td>{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d/m/Y') : '-' }}</td>
                                                    <td>{{ $item->odometer_reading ? number_format($item->odometer_reading) : '-' }}</td>
                                                    <td>{{ Str::limit($item->description, 30) }}</td>
                                                    <td>{{ $item->cost ? number_format($item->cost) : '-' }}</td>
                                                    <td><span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @else
                                <!-- Tabel Pemesanan Kendaraan -->
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Pemohon</th>
                                            <th>Departemen</th>
                                            <th>Dari</th>
                                            <th>Ke</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                            <th>Status</th>
                                            <th>Kendaraan</th>
                                            <th>Pengemudi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($reportData) && $reportData->count() > 0)
                                            @foreach($reportData as $item)
                                                <tr>
                                                    <td>REQ-{{ str_pad($item->request_id, 5, '0', STR_PAD_LEFT) }}</td>
                                                    <td>{{ $item->requester->name ?? 'User' }}</td>
                                                    <td>{{ $item->requester->department->department_name ?? '-' }}</td>
                                                    <td>{{ $item->pickupLocation->location_name ?? 'Asal' }}</td>
                                                    <td>{{ $item->destinationLocation->location_name ?? 'Tujuan' }}</td>
                                                    <td>{{ isset($item->pickup_datetime) ? \Carbon\Carbon::parse($item->pickup_datetime)->format('d/m/Y H:i') : '-' }}</td>
                                                    <td>{{ isset($item->return_datetime) ? \Carbon\Carbon::parse($item->return_datetime)->format('d/m/Y H:i') : '-' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $item->status === 'completed' ? 'success' : 'danger' }}">
                                                            {{ $item->status === 'completed' ? 'Disetujui' : 'Ditolak' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $item->assignment->vehicle->registration_number ?? '-' }}</td>
                                                    <td>{{ $item->assignment->driver->name ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="10" class="text-center py-3">Tidak ada data pemesanan kendaraan yang selesai diproses</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Pagination -->
                        @if(isset($reportData))
                            <div class="d-flex justify-content-center">
                                {{ $reportData->appends(request()->except('page'))->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Grafik utama
        const ctx = document.getElementById('mainChart').getContext('2d');
        
        @if(request('report_type') == 'fuel')
            const fuelChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Konsumsi BBM (Liter)',
                        data: {!! json_encode($chartData['data'] ?? []) !!},
                        backgroundColor: '#0d6efd',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 4,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah BBM (Liter)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Kendaraan'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toFixed(1) + ' Liter';
                                }
                            }
                        }
                    }
                }
            });
        @elseif(request('report_type') == 'maintenance')
            const maintenanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Biaya Maintenance (Rp)',
                        data: {!! json_encode($chartData['data'] ?? []) !!},
                        backgroundColor: '#6f42c1',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 4,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Biaya (Rp)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('id-ID');
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Kendaraan'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        @else
            // Grafik permintaan kendaraan
            const requestsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartData['dates'] ?? []) !!},
                    datasets: [{
                        label: 'Permintaan Disetujui',
                        data: {!! json_encode($chartData['approved'] ?? []) !!},
                        backgroundColor: 'rgba(25, 135, 84, 0.2)',
                        borderColor: '#198754',
                        pointBackgroundColor: '#198754',
                        tension: 0.3
                    }, {
                        label: 'Permintaan Ditolak',
                        data: {!! json_encode($chartData['rejected'] ?? []) !!},
                        backgroundColor: 'rgba(220, 53, 69, 0.2)',
                        borderColor: '#dc3545',
                        pointBackgroundColor: '#dc3545',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 4,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Permintaan'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tanggal'
                            }
                        }
                    }
                }
            });
        @endif
        
        // Export Excel
        document.getElementById('exportExcel').addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'excel');
            window.location.href = "{{ route('reports.export') }}?" + params.toString();
        });
    });
</script>
@endpush 