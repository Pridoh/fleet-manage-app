@extends('layouts.app')

@section('title', 'Detail Driver')

@section('page-title', 'Detail Driver')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Driver</h5>
<div>
                        <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <h3>{{ $driver->name }}</h3>
                            
                            <div class="mt-3">
                                @php
                                    $statusClass = [
                                        'active' => 'bg-success',
                                        'on_leave' => 'bg-warning',
                                        'inactive' => 'bg-secondary'
                                    ][$driver->status] ?? 'bg-secondary';
                                    
                                    $statusLabel = [
                                        'active' => 'Aktif',
                                        'on_leave' => 'Cuti',
                                        'inactive' => 'Tidak Aktif'
                                    ][$driver->status] ?? $driver->status;
                                @endphp
                                <span class="badge {{ $statusClass }} py-2 px-3">{{ $statusLabel }}</span>
                                
                                <!-- SIM Badge -->
                                <span class="badge bg-primary py-2 px-3 ms-2">
                                    <i class="fas fa-id-card me-1"></i> SIM {{ $driver->license_type }}
                                </span>
                                
                                <!-- Location Badge -->
                                @if($driver->location)
                                <span class="badge bg-info py-2 px-3 ms-2">
                                    <i class="fas fa-map-marker-alt me-1"></i> {{ $driver->location->name }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informasi Pribadi</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%">Nama Lengkap</td>
                                            <td><strong>{{ $driver->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Telepon</td>
                                            <td>
                                                <strong>
                                                    <a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a>
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal Lahir</td>
                                            <td>
                                                <strong>
                                                    {{ $driver->date_of_birth ? \Carbon\Carbon::parse($driver->date_of_birth)->format('d M Y') : '-' }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Alamat</td>
                                            <td><strong>{{ $driver->address ?? '-' }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informasi SIM & Pekerjaan</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%">Nomor SIM</td>
                                            <td><strong>{{ $driver->license_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Jenis SIM</td>
                                            <td><strong>SIM {{ $driver->license_type }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Berlaku Hingga</td>
                                            <td>
                                                <strong>
                                                    {{ \Carbon\Carbon::parse($driver->license_expiry)->format('d M Y') }}
                                                    @if(\Carbon\Carbon::parse($driver->license_expiry)->isPast())
                                                        <span class="badge bg-danger">Expired</span>
                                                    @elseif(\Carbon\Carbon::parse($driver->license_expiry)->diffInDays(now()) <= 30)
                                                        <span class="badge bg-warning">Hampir Expired</span>
                                                    @endif
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal Bergabung</td>
                                            <td>
                                                <strong>
                                                    {{ $driver->join_date ? \Carbon\Carbon::parse($driver->join_date)->format('d M Y') : '-' }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Lokasi Penempatan</td>
                                            <td><strong>{{ $driver->location->name ?? '-' }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Riwayat Penugasan -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Riwayat Penugasan</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Kendaraan</th>
                                                    <th>Tujuan</th>
                                                    <th>Pemohon</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($assignments as $assignment)
                                                <tr>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($assignment->actual_start_datetime)->format('d M Y') }}
                                                        @if($assignment->actual_end_datetime)
                                                         - {{ \Carbon\Carbon::parse($assignment->actual_end_datetime)->format('d M Y') }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $assignment->vehicle->brand ?? '' }} 
                                                        {{ $assignment->vehicle->model ?? '' }}
                                                        <small class="d-block text-muted">{{ $assignment->vehicle->registration_number ?? '' }}</small>
                                                    </td>
                                                    <td>{{ $assignment->request->destinationLocation->location_name ?? '-' }}</td>
                                                    <td>{{ $assignment->request->requester->name ?? '-' }}</td>
                                                    <td>
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
                                                    <td colspan="5" class="text-center py-3">Belum ada riwayat penugasan</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="#" class="btn btn-sm btn-primary">Lihat Semua Penugasan</a>
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
