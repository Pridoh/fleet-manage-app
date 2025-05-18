@extends('layouts.app')

@section('title', 'Detail Kendaraan')

@section('page-title', 'Detail Kendaraan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 font-weight-bold text-primary">Informasi Kendaraan</h5>
                    <div>
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-lg-8">
                            <h3>{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})</h3>
                            <h5 class="text-primary">{{ $vehicle->registration_number }}</h5>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            @php
                                $statusClass = [
                                    'available' => 'bg-success',
                                    'in_use' => 'bg-primary',
                                    'maintenance' => 'bg-warning',
                                    'inactive' => 'bg-secondary'
                                ][$vehicle->status] ?? 'bg-secondary';
                                
                                $statusLabel = [
                                    'available' => 'Tersedia',
                                    'in_use' => 'Digunakan',
                                    'maintenance' => 'Maintenance',
                                    'inactive' => 'Tidak Aktif'
                                ][$vehicle->status] ?? $vehicle->status;
                            @endphp
                            <span class="badge {{ $statusClass }} py-2 px-3 fs-6">{{ $statusLabel }}</span>
                            
                            @if($vehicle->ownership_type === 'leased')
                                <div class="mt-2">
                                    <span class="badge bg-info py-1 px-2">Kendaraan Sewa</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 h-100">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 font-weight-bold">Informasi Dasar</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-striped">
                                        <tr>
                                            <td width="40%" class="fw-bold">Jenis Kendaraan</td>
                                            <td>{{ $vehicle->vehicleType->type_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Merek</td>
                                            <td>{{ $vehicle->brand }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Model</td>
                                            <td>{{ $vehicle->model }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Tahun</td>
                                            <td>{{ $vehicle->year }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Kapasitas</td>
                                            <td>{{ $vehicle->capacity ?? '-' }} Orang</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Lokasi</td>
                                            <td>{{ $vehicle->location->location_name ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4 h-100">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 font-weight-bold">Informasi Kepemilikan</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-striped">
                                        <tr>
                                            <td width="40%" class="fw-bold">Jenis Kepemilikan</td>
                                            <td>
                                                {{ $vehicle->ownership_type == 'owned' ? 'Milik Sendiri' : 'Sewa' }}
                                            </td>
                                        </tr>
                                        @if($vehicle->ownership_type == 'leased')
                                        <tr>
                                            <td class="fw-bold">Perusahaan Leasing</td>
                                            <td>{{ $vehicle->lease_company }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Periode Sewa</td>
                                            <td>
                                                @if($vehicle->lease_start_date && $vehicle->lease_end_date)
                                                    {{ \Carbon\Carbon::parse($vehicle->lease_start_date)->format('d M Y') }} - 
                                                    {{ \Carbon\Carbon::parse($vehicle->lease_end_date)->format('d M Y') }}
                                                    
                                                    @if(\Carbon\Carbon::parse($vehicle->lease_end_date)->isPast())
                                                        <span class="badge bg-danger ms-1">Kadaluarsa</span>
                                                    @elseif(\Carbon\Carbon::parse($vehicle->lease_end_date)->diffInDays(now()) <= 30)
                                                        <span class="badge bg-warning ms-1">Segera berakhir</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold">Service Terakhir</td>
                                            <td>
                                                {{ $vehicle->last_service_date ? \Carbon\Carbon::parse($vehicle->last_service_date)->format('d M Y') : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Service Berikutnya</td>
                                            <td>
                                                @if($vehicle->next_service_date)
                                                    {{ \Carbon\Carbon::parse($vehicle->next_service_date)->format('d M Y') }}
                                                    @if(\Carbon\Carbon::parse($vehicle->next_service_date)->isPast())
                                                        <span class="badge bg-danger ms-1">Terlambat</span>
                                                    @elseif(\Carbon\Carbon::parse($vehicle->next_service_date)->diffInDays(now()) <= 7)
                                                        <span class="badge bg-warning ms-1">Segera</span>
                                                    @endif
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
                    
                    <!-- Riwayat Maintenance -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0 font-weight-bold">Riwayat Maintenance</h6>
                                    <a href="{{ route('maintenance.create') }}?vehicle_id={{ $vehicle->vehicle_id }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i> Tambah Maintenance
                                    </a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 15%">Tanggal</th>
                                                    <th class="text-center" style="width: 15%">Jenis</th>
                                                    <th class="text-center" style="width: 12%">Odometer</th>
                                                    <th class="text-center" style="width: 15%">Biaya</th>
                                                    <th class="text-center" style="width: 15%">Lokasi</th>
                                                    <th class="text-center" style="width: 28%">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($vehicle->maintenanceRecords as $record)
                                                <tr>
                                                    <td class="text-center">{{ \Carbon\Carbon::parse($record->start_date)->format('d M Y') }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $typeLabel = [
                                                                'routine' => 'Rutin',
                                                                'repair' => 'Perbaikan',
                                                                'inspection' => 'Inspeksi',
                                                                'emergency' => 'Darurat'
                                                            ][$record->maintenance_type] ?? $record->maintenance_type;
                                                            
                                                            $typeClass = [
                                                                'routine' => 'bg-info',
                                                                'repair' => 'bg-warning',
                                                                'inspection' => 'bg-primary',
                                                                'emergency' => 'bg-danger'
                                                            ][$record->maintenance_type] ?? 'bg-secondary';
                                                        @endphp
                                                        <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                                    </td>
                                                    <td class="text-center">{{ number_format($record->odometer_reading, 0, ',', '.') }} km</td>
                                                    <td class="text-center">Rp {{ number_format($record->cost, 0, ',', '.') }}</td>
                                                    <td class="text-center">{{ $record->performed_by }}</td>
                                                    <td class="text-center">{{ $record->description }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-3">Belum ada data maintenance</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Riwayat Penggunaan -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 font-weight-bold">Riwayat Penggunaan</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 20%">Tanggal</th>
                                                    <th class="text-center" style="width: 20%">Pemohon</th>
                                                    <th class="text-center" style="width: 20%">Driver</th>
                                                    <th class="text-center" style="width: 25%">Tujuan</th>
                                                    <th class="text-center" style="width: 15%">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($vehicle->assignments as $assignment)
                                                <tr>
                                                    <td class="text-center">
                                                        @if($assignment->actual_start_datetime)
                                                            {{ \Carbon\Carbon::parse($assignment->actual_start_datetime)->format('d M Y') }}
                                                            @if($assignment->actual_end_datetime)
                                                            - {{ \Carbon\Carbon::parse($assignment->actual_end_datetime)->format('d M Y') }}
                                                            @endif
                                                        @else
                                                            {{ \Carbon\Carbon::parse($assignment->request->pickup_datetime)->format('d M Y') }}
                                                            - {{ \Carbon\Carbon::parse($assignment->request->return_datetime)->format('d M Y') }}
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $assignment->request->requester->name ?? '-' }}</td>
                                                    <td class="text-center">{{ $assignment->driver->name ?? '-' }}</td>
                                                    <td class="text-center">{{ $assignment->request->destinationLocation->location_name ?? 'Tidak ada data' }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $assignmentStatusClass = [
                                                                'assigned' => 'bg-info',
                                                                'in_progress' => 'bg-primary',
                                                                'completed' => 'bg-success',
                                                                'cancelled' => 'bg-danger'
                                                            ][$assignment->status] ?? 'bg-secondary';
                                                            
                                                            $assignmentStatusLabel = [
                                                                'assigned' => 'Ditugaskan',
                                                                'in_progress' => 'Dalam Perjalanan',
                                                                'completed' => 'Selesai',
                                                                'cancelled' => 'Dibatalkan'
                                                            ][$assignment->status] ?? $assignment->status;
                                                        @endphp
                                                        <span class="badge {{ $assignmentStatusClass }}">{{ $assignmentStatusLabel }}</span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3">Belum ada riwayat penggunaan</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
