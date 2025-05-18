@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Maintenance Kendaraan</h1>
        <div>
            <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Kembali
            </a>
            <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit fa-sm"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri - Informasi Maintenance -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Maintenance</h6>
                    @if($maintenance->status == 'scheduled')
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#startModal">
                            <i class="fas fa-play fa-sm"></i> Mulai Maintenance
                        </button>
                    @elseif($maintenance->status == 'in_progress')
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#completeModal">
                            <i class="fas fa-check fa-sm"></i> Selesaikan Maintenance
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Status</h5>
                            <p>
                                @php
                                    $statusLabel = [
                                        'scheduled' => 'Dijadwalkan',
                                        'in_progress' => 'Dalam Proses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan'
                                    ][$maintenance->status] ?? ucfirst($maintenance->status);
                                    
                                    $statusClass = [
                                        'scheduled' => 'bg-warning text-dark',
                                        'in_progress' => 'bg-info text-white',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger'
                                    ][$maintenance->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Jenis Maintenance</h5>
                            <p>
                                @php
                                    $type = $maintenance->maintenance_type;
                                    $typeLabel = [
                                        'routine' => 'Service Rutin',
                                        'repair' => 'Perbaikan',
                                        'inspection' => 'Inspeksi',
                                        'emergency' => 'Darurat'
                                    ][$type] ?? ucfirst($type);
                                    
                                    $typeClass = [
                                        'routine' => 'bg-info',
                                        'repair' => 'bg-warning',
                                        'inspection' => 'bg-primary',
                                        'emergency' => 'bg-danger'
                                    ][$type] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Kendaraan</h5>
                            <p>
                                <i class="fas fa-car text-primary mr-1"></i> {{ $maintenance->vehicle->registration_number }}<br>
                                <small class="text-muted">{{ $maintenance->vehicle->brand }} {{ $maintenance->vehicle->model }} ({{ $maintenance->vehicle->vehicleType->type_name }})</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Odometer</h5>
                            <p>
                                <i class="fas fa-tachometer-alt text-primary mr-1"></i> 
                                {{ $maintenance->odometer_reading ? number_format($maintenance->odometer_reading) . ' km' : 'Tidak tercatat' }}
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Tanggal Mulai</h5>
                            <p><i class="fas fa-calendar-alt text-success mr-1"></i> {{ $maintenance->start_date ? $maintenance->start_date->format('d M Y') : '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Tanggal Selesai</h5>
                            <p><i class="fas fa-calendar-alt text-danger mr-1"></i> {{ $maintenance->end_date ? $maintenance->end_date->format('d M Y') : 'Belum selesai' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Dilakukan Oleh</h5>
                            <p><i class="fas fa-user-cog text-primary mr-1"></i> {{ $maintenance->performed_by ?: 'Belum ditentukan' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Biaya</h5>
                            <p><i class="fas fa-money-bill-wave text-success mr-1"></i> {{ $maintenance->cost ? 'Rp ' . number_format($maintenance->cost, 0, ',', '.') : 'Belum tercatat' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-dark">Deskripsi</h5>
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-clipboard-list text-primary mr-1"></i> 
                                {{ $maintenance->description }}
                            </div>
                        </div>
                    </div>
                    @if($maintenance->notes)
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-dark">Catatan</h5>
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-sticky-note text-primary mr-1"></i> 
                                {{ $maintenance->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan - Timeline & Informasi Tambahan -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Maintenance</h6>
                </div>
                <div class="card-body">
                    <!-- Timeline Status Maintenance -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="timeline-steps">
                                <div class="timeline-step mb-3">
                                    <div class="timeline-content">
                                        <div class="inner-circle {{ in_array($maintenance->status, ['scheduled', 'in_progress', 'completed']) ? 'bg-success' : 'bg-secondary' }}">
                                            <i class="fas fa-clipboard-check text-white"></i>
                                        </div>
                                        <p class="h6 mt-3 mb-1">Dijadwalkan</p>
                                        <p class="text-muted small">
                                            {{ \Carbon\Carbon::parse($maintenance->created_at)->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="timeline-step mb-3">
                                    <div class="timeline-content">
                                        <div class="inner-circle {{ in_array($maintenance->status, ['in_progress', 'completed']) ? 'bg-success' : 'bg-secondary' }}">
                                            <i class="fas fa-tools text-white"></i>
                                        </div>
                                        <p class="h6 mt-3 mb-1">Dalam Proses</p>
                                        <p class="text-muted small">
                                            {{ $maintenance->status == 'in_progress' || $maintenance->status == 'completed' ? $maintenance->updated_at->format('d M Y H:i') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="timeline-step mb-3">
                                    <div class="timeline-content">
                                        <div class="inner-circle {{ $maintenance->status == 'completed' ? 'bg-success' : 'bg-secondary' }}">
                                            <i class="fas fa-check-circle text-white"></i>
                                        </div>
                                        <p class="h6 mt-3 mb-1">Selesai</p>
                                        <p class="text-muted small">
                                            {{ $maintenance->end_date ? $maintenance->end_date->format('d M Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Riwayat Maintenance Kendaraan ini -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-dark mb-3">Riwayat Maintenance Kendaraan</h5>
                            <div class="table-responsive">
                                @php
                                    $pastMaintenances = $maintenance->vehicle->maintenanceRecords()
                                        ->where('maintenance_id', '!=', $maintenance->maintenance_id)
                                        ->orderBy('start_date', 'desc')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                
                                @if($pastMaintenances->count() > 0)
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jenis</th>
                                            <th>Status</th>
                                            <th>Biaya</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pastMaintenances as $past)
                                        <tr>
                                            <td>{{ $past->start_date->format('d M Y') }}</td>
                                            <td>
                                                @php
                                                    $typeLabel = [
                                                        'routine' => 'Service Rutin',
                                                        'repair' => 'Perbaikan',
                                                        'inspection' => 'Inspeksi',
                                                        'emergency' => 'Darurat'
                                                    ][$past->maintenance_type] ?? ucfirst($past->maintenance_type);
                                                @endphp
                                                {{ $typeLabel }}
                                            </td>
                                            <td>
                                                @php
                                                    $statusLabel = [
                                                        'scheduled' => 'Dijadwalkan',
                                                        'in_progress' => 'Dalam Proses',
                                                        'completed' => 'Selesai',
                                                        'cancelled' => 'Dibatalkan'
                                                    ][$past->status] ?? ucfirst($past->status);
                                                @endphp
                                                {{ $statusLabel }}
                                            </td>
                                            <td>{{ $past->cost ? 'Rp ' . number_format($past->cost, 0, ',', '.') : '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="alert alert-info">
                                    Belum ada riwayat maintenance sebelumnya untuk kendaraan ini.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Informasi Tambahan -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-dark">Informasi Tambahan</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>
                                @if($maintenance->status == 'completed')
                                    Kendaraan dalam kondisi baik dan siap digunakan kembali.
                                @elseif($maintenance->status == 'in_progress')
                                    Kendaraan sedang dalam proses maintenance dan tidak dapat digunakan.
                                @elseif($maintenance->status == 'scheduled')
                                    Kendaraan dijadwalkan untuk maintenance pada {{ $maintenance->start_date->format('d M Y') }}.
                                @else
                                    Jadwal maintenance kendaraan dibatalkan.
                                @endif
                            </div>
                            
                            @if($maintenance->status == 'completed')
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-1"></i>
                                Maintenance telah selesai dilakukan selama {{ $maintenance->start_date->diffInDays($maintenance->end_date) + 1 }} hari.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS untuk timeline -->
<style>
    .timeline-steps {
        display: flex;
        flex-direction: column;
    }
    .timeline-step {
        position: relative;
    }
    .timeline-content {
        position: relative;
    }
    .inner-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin: 0 auto;
    }
    
    /* Font size adjustments */
    .h3 {
        font-size: 1.5rem !important;
    }
    .h5, .h6, h5, h6 {
        font-size: 0.95rem !important;
    }
    .card-body p {
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    .badge {
        font-size: 0.75rem;
    }
    .text-muted {
        font-size: 0.8rem;
    }
    .alert {
        font-size: 0.85rem;
    }
    .table {
        font-size: 0.85rem;
    }
    small {
        font-size: 0.75rem !important;
    }
    .btn {
        font-size: 0.85rem;
    }
    .timeline-step .h6 {
        font-size: 0.85rem !important;
    }
    .timeline-step .small {
        font-size: 0.75rem !important;
    }
</style>

<!-- Modal Mulai Maintenance -->
<div class="modal fade" id="startModal" tabindex="-1" aria-labelledby="startModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('maintenance.update-status', $maintenance) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="startModalLabel">Mulai Maintenance Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="in_progress">
                    <div class="mb-3">
                        <label for="performed_by" class="form-label">Dilakukan Oleh</label>
                        <input type="text" class="form-control" id="performed_by" name="performed_by" value="{{ $maintenance->performed_by }}">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ $maintenance->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Mulai Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Selesai Maintenance -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('maintenance.update-status', $maintenance) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalLabel">Selesaikan Maintenance Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="completed">
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label">Biaya (Rp)</label>
                        <input type="number" class="form-control" id="cost" name="cost" value="{{ $maintenance->cost }}">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ $maintenance->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Selesaikan Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 