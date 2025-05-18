@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Penggunaan Kendaraan #{{ $assignment->assignment_id }}</h1>
        <div>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri - Informasi Penggunaan -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Penggunaan</h6>
                    @if($assignment->status == 'assigned')
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#startModal">
                            <i class="fas fa-play fa-sm"></i> Mulai Penggunaan
                        </button>
                    @elseif($assignment->status == 'in_progress')
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#completeModal">
                            <i class="fas fa-check fa-sm"></i> Selesaikan Penggunaan
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Status</h5>
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
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">ID Permintaan</h5>
                            <p>{{ $assignment->request->request_id }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Kendaraan</h5>
                            <p>
                                <i class="fas fa-car text-primary mr-1"></i> {{ $assignment->vehicle->registration_number }}<br>
                                <small class="text-muted">{{ $assignment->vehicle->brand }} {{ $assignment->vehicle->model }} ({{ $assignment->vehicle->vehicleType->type_name }})</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Pengemudi</h5>
                            <p>
                                @if($assignment->driver)
                                    <i class="fas fa-user text-primary mr-1"></i> {{ $assignment->driver->name }}<br>
                                    <small class="text-muted"><i class="fas fa-phone"></i> {{ $assignment->driver->phone_number }}</small>
                                @else
                                    <span class="text-muted">Tanpa pengemudi</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Pemohon</h5>
                            <p><i class="fas fa-user-tie text-primary mr-1"></i> {{ $assignment->request->requester->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Departemen</h5>
                            <p><i class="fas fa-building text-primary mr-1"></i> {{ $assignment->request->requester->department->department_name ?? 'Tidak diketahui' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Waktu Mulai</h5>
                            <p><i class="fas fa-clock text-success mr-1"></i> {{ $assignment->actual_start_datetime ? \Carbon\Carbon::parse($assignment->actual_start_datetime)->format('d M Y H:i') : 'Belum dimulai' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Waktu Selesai</h5>
                            <p><i class="fas fa-clock text-danger mr-1"></i> {{ $assignment->actual_end_datetime ? \Carbon\Carbon::parse($assignment->actual_end_datetime)->format('d M Y H:i') : 'Belum selesai' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Odometer Awal</h5>
                            <p><i class="fas fa-tachometer-alt text-primary mr-1"></i> {{ $assignment->start_odometer ? number_format($assignment->start_odometer) . ' km' : '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Odometer Akhir</h5>
                            <p><i class="fas fa-tachometer-alt text-primary mr-1"></i> {{ $assignment->end_odometer ? number_format($assignment->end_odometer) . ' km' : '-' }}</p>
                        </div>
                    </div>
                    @if($assignment->end_odometer && $assignment->start_odometer)
                    <div class="alert alert-info">
                        <i class="fas fa-road mr-1"></i> Total jarak tempuh: <strong>{{ number_format($assignment->end_odometer - $assignment->start_odometer) }} km</strong>
                    </div>
                    @endif
                    
                    <!-- Informasi BBM -->
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-dark">Penggunaan BBM</h5>
                            <div class="d-flex align-items-center">
                                <div class="me-4">
                                    <i class="fas fa-gas-pump fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    @if($assignment->fuel_used)
                                        <div class="progress mb-1" style="height: 10px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="small">{{ number_format($assignment->fuel_used, 1) }} liter</span>
                                            @if($assignment->end_odometer && $assignment->start_odometer && $assignment->fuel_used > 0)
                                                <span class="small text-muted">
                                                    {{ number_format(($assignment->end_odometer - $assignment->start_odometer) / $assignment->fuel_used, 1) }} km/liter
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">Belum ada data penggunaan BBM</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($assignment->status == 'completed')
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-dark">Catatan</h5>
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-clipboard-list text-primary mr-1"></i> 
                                {{ $assignment->notes ?? 'Tidak ada catatan' }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan - Detail Perjalanan -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Perjalanan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h5 class="font-weight-bold text-dark">Tujuan Perjalanan</h5>
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-map-marked-alt text-primary mr-1"></i> 
                                {{ $assignment->request->purpose }}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Lokasi Penjemputan</h5>
                            <p>
                                <i class="fas fa-map-marker-alt text-success mr-1"></i>
                                {{ $assignment->request->pickupLocation->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Waktu Penjemputan</h5>
                            <p>
                                <i class="fas fa-calendar-alt text-success mr-1"></i>
                                {{ $assignment->request->pickup_datetime ? \Carbon\Carbon::parse($assignment->request->pickup_datetime)->format('d M Y H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Lokasi Tujuan</h5>
                            <p>
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                {{ $assignment->request->destinationLocation->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark">Waktu Kembali (Estimasi)</h5>
                            <p>
                                <i class="fas fa-calendar-alt text-danger mr-1"></i>
                                {{ $assignment->request->return_datetime ? \Carbon\Carbon::parse($assignment->request->return_datetime)->format('d M Y H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                    <hr>
                    <!-- Status Perjalanan -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="font-weight-bold text-dark mb-3">Status Perjalanan</h5>
                            <div class="timeline-steps">
                                <div class="timeline-step mb-3">
                                    <div class="timeline-content">
                                        <div class="inner-circle {{ $assignment->status == 'assigned' || $assignment->status == 'in_progress' || $assignment->status == 'completed' ? 'bg-success' : 'bg-secondary' }}">
                                            <i class="fas fa-clipboard-check text-white"></i>
                                        </div>
                                        <p class="h6 mt-3 mb-1">Ditugaskan</p>
                                        <p class="text-muted small">
                                            {{ \Carbon\Carbon::parse($assignment->created_at)->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="timeline-step mb-3">
                                    <div class="timeline-content">
                                        <div class="inner-circle {{ $assignment->status == 'in_progress' || $assignment->status == 'completed' ? 'bg-success' : 'bg-secondary' }}">
                                            <i class="fas fa-car text-white"></i>
                                        </div>
                                        <p class="h6 mt-3 mb-1">Dalam Perjalanan</p>
                                        <p class="text-muted small">
                                            {{ $assignment->actual_start_datetime ? \Carbon\Carbon::parse($assignment->actual_start_datetime)->format('d M Y H:i') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="timeline-step mb-3">
                                    <div class="timeline-content">
                                        <div class="inner-circle {{ $assignment->status == 'completed' ? 'bg-success' : 'bg-secondary' }}">
                                            <i class="fas fa-flag-checkered text-white"></i>
                                        </div>
                                        <p class="h6 mt-3 mb-1">Selesai</p>
                                        <p class="text-muted small">
                                            {{ $assignment->actual_end_datetime ? \Carbon\Carbon::parse($assignment->actual_end_datetime)->format('d M Y H:i') : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Informasi Log Penggunaan -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-weight-bold text-dark mb-0">Log Penggunaan Kendaraan</h5>
                                <a href="{{ route('vehicle-usage.logs.index', $assignment) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-clipboard-list me-1"></i> Lihat Semua Log
                                </a>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i> Log penggunaan mencatat detail perjalanan, termasuk keberangkatan, kedatangan, pengisian BBM dan checkpoint.
                            </div>
                        </div>
                    </div>
                    <!-- Informasi Tambahan -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-dark">Informasi Tambahan</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-light">Jumlah Penumpang</th>
                                        <td>{{ $assignment->request->passenger_count ?? '1' }} orang</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Jenis Keperluan</th>
                                        <td>{{ $assignment->request->request_type ?? 'Dinas' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Durasi Penggunaan</th>
                                        <td>
                                            @if($assignment->actual_start_datetime && $assignment->actual_end_datetime)
                                                {{ \Carbon\Carbon::parse($assignment->actual_start_datetime)->diffForHumans(\Carbon\Carbon::parse($assignment->actual_end_datetime), true) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
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
    .modal-title {
        font-size: 1.1rem;
    }
    .form-label {
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

<!-- Modal Mulai Penggunaan -->
<div class="modal fade" id="startModal" tabindex="-1" aria-labelledby="startModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vehicle-usage.update-status', $assignment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="startModalLabel">Mulai Penggunaan Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="in_progress">
                    <div class="mb-3">
                        <label for="start_odometer" class="form-label">Odometer Awal (km)</label>
                        <input type="number" class="form-control" id="start_odometer" name="start_odometer" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Mulai Penggunaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Selesai Penggunaan -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vehicle-usage.update-status', $assignment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalLabel">Selesaikan Penggunaan Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="completed">
                    <div class="mb-3">
                        <label for="end_odometer" class="form-label">Odometer Akhir (km)</label>
                        <input type="number" class="form-control" id="end_odometer" name="end_odometer" required min="{{ $assignment->start_odometer ?? 0 }}">
                    </div>
                    <div class="mb-3">
                        <label for="fuel_used" class="form-label">Penggunaan BBM (liter)</label>
                        <input type="number" class="form-control" id="fuel_used" name="fuel_used" step="0.1" min="0" placeholder="Masukkan jumlah BBM yang digunakan">
                        <small class="form-text text-muted">Isi untuk mencatat konsumsi BBM selama penggunaan kendaraan</small>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Selesaikan Penggunaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 