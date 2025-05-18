@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Log Penggunaan Kendaraan</h1>
        <div>
            <a href="{{ route('vehicle-usage.logs.index', $assignment) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('vehicle-usage.logs.edit', [$assignment, $log]) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri - Informasi Kendaraan -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kendaraan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Kendaraan:</strong>
                        <p>{{ $assignment->vehicle->registration_number }} - {{ $assignment->vehicle->brand }} {{ $assignment->vehicle->model }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Driver:</strong>
                        <p>{{ $assignment->driver->name ?? 'Tanpa driver' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Status Penggunaan:</strong>
                        <p>
                            @if($assignment->status == 'assigned')
                                <span class="badge bg-warning text-dark">Ditugaskan</span>
                            @elseif($assignment->status == 'in_progress')
                                <span class="badge bg-info text-white">Sedang Digunakan</span>
                            @elseif($assignment->status == 'completed')
                                <span class="badge bg-success">Selesai</span>
                            @elseif($assignment->status == 'cancelled')
                                <span class="badge bg-danger">Dibatalkan</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Kartu Statistik -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Penggunaan</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <div class="icon-circle bg-primary text-white">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div>Jarak Tempuh</div>
                            <div class="h5 mb-0 font-weight-bold">
                                {{ ($assignment->end_odometer && $assignment->start_odometer) ? number_format($assignment->end_odometer - $assignment->start_odometer) . ' km' : 'Belum tercatat' }}
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <div class="icon-circle bg-success text-white">
                                <i class="fas fa-gas-pump"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div>Total BBM</div>
                            <div class="h5 mb-0 font-weight-bold">
                                {{ $assignment->fuel_used ? number_format($assignment->fuel_used, 1) . ' liter' : 'Belum tercatat' }}
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-info text-white">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div>Efisiensi</div>
                            <div class="h5 mb-0 font-weight-bold">
                                @if($assignment->fuel_used && $assignment->end_odometer && $assignment->start_odometer)
                                    {{ number_format(($assignment->end_odometer - $assignment->start_odometer) / $assignment->fuel_used, 1) }} km/liter
                                @else
                                    Belum tercatat
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kolom Kanan - Detail Log -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Log</h6>
                </div>
                <div class="card-body">
                    <!-- Tampilkan tipe log dengan badge -->
                    <div class="mb-4 d-flex align-items-center">
                        @php
                            $logTypeLabels = [
                                'departure' => ['Keberangkatan', 'primary'],
                                'arrival' => ['Kedatangan', 'success'],
                                'refuel' => ['Pengisian BBM', 'warning'],
                                'checkpoint' => ['Titik Pemeriksaan', 'info'],
                                'issue' => ['Masalah/Insiden', 'danger']
                            ];
                            $typeInfo = $logTypeLabels[$log->log_type] ?? ['Lainnya', 'secondary'];
                        @endphp
                        <span class="badge bg-{{ $typeInfo[1] }} p-2 fs-6 me-2">{{ $typeInfo[0] }}</span>
                        <span>Dicatat oleh: <strong>{{ $log->logger->name ?? 'System' }}</strong></span>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Waktu Log</h6>
                            <p><i class="fas fa-clock text-primary me-2"></i>{{ \Carbon\Carbon::parse($log->log_datetime)->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Lokasi</h6>
                            <p><i class="fas fa-map-marker-alt text-danger me-2"></i>{{ $log->location->location_name ?? 'Tidak tercatat' }}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Odometer</h6>
                            <p><i class="fas fa-tachometer-alt text-primary me-2"></i>{{ $log->odometer_reading ? number_format($log->odometer_reading) . ' km' : 'Tidak tercatat' }}</p>
                        </div>
                        <div class="col-md-6">
                            @if($log->log_type == 'refuel')
                                <h6 class="font-weight-bold">BBM Ditambahkan</h6>
                                <p>
                                    <i class="fas fa-gas-pump text-warning me-2"></i>{{ $log->fuel_added ? number_format($log->fuel_added, 1) . ' liter' : 'Tidak tercatat' }}
                                    @if($log->fuel_cost)
                                        <br><i class="fas fa-money-bill text-success me-2"></i>Rp {{ number_format($log->fuel_cost) }}
                                    @endif
                                </p>
                            @else
                                <h6 class="font-weight-bold">Level BBM</h6>
                                <p><i class="fas fa-gas-pump text-warning me-2"></i>{{ $log->fuel_level ? number_format($log->fuel_level, 1) . ' liter' : 'Tidak tercatat' }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="font-weight-bold">Catatan</h6>
                            <div class="p-3 bg-light rounded">
                                {{ $log->notes ?? 'Tidak ada catatan' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <small class="text-muted">Log ID: {{ $log->log_id }}</small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">Dibuat: {{ $log->created_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Hapus Log
                        </button>
                        <a href="{{ route('vehicle-usage.logs.edit', [$assignment, $log]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit Log
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus log penggunaan kendaraan ini?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i> 
                    Perhatian: Menghapus log ini mungkin akan mempengaruhi data penggunaan kendaraan secara keseluruhan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('vehicle-usage.logs.destroy', [$assignment, $log]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus Log</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling untuk icon circle di card */
    .icon-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 3rem;
        width: 3rem;
        border-radius: 50%;
    }
    .icon-circle i {
        font-size: 1.25rem;
    }
</style>
@endsection 