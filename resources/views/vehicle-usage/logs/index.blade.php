@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Log Penggunaan Kendaraan</h1>
        <div>
            <a href="{{ route('vehicle-usage.show', $assignment) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('vehicle-usage.logs.create', $assignment) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle"></i> Tambah Log
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Penggunaan Kendaraan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6>Kendaraan</h6>
                    <p>{{ $assignment->vehicle->registration_number }} - {{ $assignment->vehicle->brand }} {{ $assignment->vehicle->model }}</p>
                </div>
                <div class="col-md-4">
                    <h6>Driver</h6>
                    <p>{{ $assignment->driver->name ?? 'Tanpa driver' }}</p>
                </div>
                <div class="col-md-4">
                    <h6>Status</h6>
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
            <div class="row">
                <div class="col-md-4">
                    <h6>Jarak Tempuh</h6>
                    <p>{{ ($assignment->end_odometer && $assignment->start_odometer) ? number_format($assignment->end_odometer - $assignment->start_odometer) . ' km' : 'Belum tercatat' }}</p>
                </div>
                <div class="col-md-4">
                    <h6>Total BBM</h6>
                    <p>{{ $assignment->fuel_used ? number_format($assignment->fuel_used, 1) . ' liter' : 'Belum tercatat' }}</p>
                </div>
                <div class="col-md-4">
                    <h6>Efisiensi</h6>
                    <p>
                        @if($assignment->fuel_used && $assignment->end_odometer && $assignment->start_odometer)
                            {{ number_format(($assignment->end_odometer - $assignment->start_odometer) / $assignment->fuel_used, 1) }} km/liter
                        @else
                            Belum tercatat
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

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

    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Log Penggunaan Kendaraan</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th>Odometer</th>
                            <th>BBM</th>
                            <th>Catatan</th>
                            <th>Pencatat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->log_datetime)->format('d/m/Y H:i') }}</td>
                            <td>
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
                                <span class="badge bg-{{ $typeInfo[1] }}">{{ $typeInfo[0] }}</span>
                            </td>
                            <td>{{ $log->location->location_name ?? '-' }}</td>
                            <td>{{ $log->odometer_reading ? number_format($log->odometer_reading) . ' km' : '-' }}</td>
                            <td>
                                @if($log->log_type == 'refuel')
                                    {{ $log->fuel_added ? number_format($log->fuel_added, 1) . ' L' : '-' }}
                                    @if($log->fuel_cost)
                                        <br><small>Rp {{ number_format($log->fuel_cost) }}</small>
                                    @endif
                                @else
                                    {{ $log->fuel_level ? number_format($log->fuel_level, 1) . ' L' : '-' }}
                                @endif
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($log->notes, 30) ?: '-' }}</td>
                            <td>{{ $log->logger->name ?? 'System' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('vehicle-usage.logs.show', [$assignment, $log]) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('vehicle-usage.logs.edit', [$assignment, $log]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $log->log_id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Modal Hapus -->
                                <div class="modal fade" id="deleteModal{{ $log->log_id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $log->log_id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $log->log_id }}">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus log penggunaan kendaraan ini?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('vehicle-usage.logs.destroy', [$assignment, $log]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Belum ada log penggunaan kendaraan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h5>Panduan Log</h5>
        <div class="mb-2">
            <span class="badge bg-primary me-2">Keberangkatan</span> Log pada saat kendaraan berangkat dari lokasi asal
        </div>
        <div class="mb-2">
            <span class="badge bg-success me-2">Kedatangan</span> Log pada saat kendaraan tiba di lokasi tujuan atau kembali ke pool
        </div>
        <div class="mb-2">
            <span class="badge bg-warning me-2">Pengisian BBM</span> Log untuk mencatat pengisian bahan bakar
        </div>
        <div class="mb-2">
            <span class="badge bg-info me-2">Titik Pemeriksaan</span> Log untuk mencatat checkpoint atau titik pemeriksaan selama perjalanan
        </div>
        <div class="mb-2">
            <span class="badge bg-danger me-2">Masalah/Insiden</span> Log untuk mencatat masalah atau insiden selama perjalanan
        </div>
    </div>
</div>
@endsection 