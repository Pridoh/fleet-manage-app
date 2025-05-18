@extends('layouts.app')

@section('title', 'Detail Pemesanan Kendaraan - Sistem Manajemen Armada')

@section('page-title', 'Detail Pemesanan Kendaraan')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pemesanan Kendaraan</h5>
                <span class="badge bg-{{ 
                    $vehicleRequest->status === 'approved' ? 'success' : 
                    ($vehicleRequest->status === 'pending' ? 'warning' : 
                    ($vehicleRequest->status === 'rejected' ? 'danger' : 
                    ($vehicleRequest->status === 'completed' ? 'info' : 'secondary')))
                }}">
                    {{ 
                        $vehicleRequest->status === 'approved' ? 'Disetujui' : 
                        ($vehicleRequest->status === 'pending' ? 'Menunggu' : 
                        ($vehicleRequest->status === 'rejected' ? 'Ditolak' : 
                        ($vehicleRequest->status === 'completed' ? 'Selesai' : ucfirst($vehicleRequest->status))))
                    }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3 border-bottom pb-2">Informasi Pemohon</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="ps-0 text-muted" style="width: 40%;">Nomor Permintaan</th>
                                <td>REQ-{{ str_pad($vehicleRequest->request_id, 5, '0', STR_PAD_LEFT) }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Nama Pemohon</th>
                                <td>{{ $vehicleRequest->requester->name ?? 'Tidak ada data' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Departemen</th>
                                <td>{{ $vehicleRequest->requester->department->department_name ?? 'Tidak ada departemen' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Tanggal Pemesanan</th>
                                <td>{{ $vehicleRequest->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-3 border-bottom pb-2">Detail Kendaraan</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="ps-0 text-muted" style="width: 40%;">Jenis Kendaraan</th>
                                <td>{{ $vehicleRequest->vehicleType->type_name ?? 'Tidak ada data' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Jumlah Penumpang</th>
                                <td>{{ $vehicleRequest->passenger_count }} orang</td>
                            </tr>
                            @if($vehicleRequest->assignment && $vehicleRequest->assignment->vehicle)
                                <tr>
                                    <th class="ps-0 text-muted">Kendaraan</th>
                                    <td>{{ $vehicleRequest->assignment->vehicle->registration_number ?? 'Belum ditetapkan' }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">Pengemudi</th>
                                    <td>{{ $vehicleRequest->assignment->driver->name ?? 'Belum ditetapkan' }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>
                
                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-3 border-bottom pb-2">Detail Perjalanan</h6>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1 small text-muted">Lokasi Awal</p>
                                        <h6 class="mb-3">{{ $vehicleRequest->pickupLocation->location_name ?? 'Tidak ada data' }}</h6>
                                        
                                        <p class="mb-1 small text-muted">Tanggal & Jam Mulai</p>
                                        <h6>{{ \Carbon\Carbon::parse($vehicleRequest->pickup_datetime)->format('d/m/Y H:i') }}</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1 small text-muted">Lokasi Tujuan</p>
                                        <h6 class="mb-3">{{ $vehicleRequest->destinationLocation->location_name ?? 'Tidak ada data' }}</h6>
                                        
                                        <p class="mb-1 small text-muted">Tanggal & Jam Selesai</p>
                                        <h6>{{ \Carbon\Carbon::parse($vehicleRequest->return_datetime)->format('d/m/Y H:i') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="mb-3 border-bottom pb-2">Tujuan Penggunaan</h6>
                        <p>{{ $vehicleRequest->purpose }}</p>
                        
                        @if($vehicleRequest->notes)
                            <h6 class="mt-4 mb-2">Catatan Tambahan</h6>
                            <p>{{ $vehicleRequest->notes }}</p>
                        @endif
                    </div>
                </div>

                @if($vehicleRequest->status === 'pending' && Auth::id() === $vehicleRequest->requester_id)
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('vehicle-requests.edit', $vehicleRequest->request_id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Edit Pemesanan
                        </a>
                        <form action="{{ route('vehicle-requests.destroy', $vehicleRequest->request_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemesanan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Hapus Pemesanan
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Status Persetujuan</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if(isset($vehicleRequest->approvals) && count($vehicleRequest->approvals) > 0)
                        @foreach($vehicleRequest->approvals->sortBy('approval_level') as $approval)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0">Level {{ $approval->approval_level }}</h6>
                                    <span class="badge bg-{{ 
                                        $approval->status === 'approved' ? 'success' : 
                                        ($approval->status === 'pending' ? 'warning' : 
                                        ($approval->status === 'rejected' ? 'danger' : 'secondary')) 
                                    }}">
                                        {{ 
                                            $approval->status === 'approved' ? 'Disetujui' : 
                                            ($approval->status === 'pending' ? 'Menunggu' : 
                                            ($approval->status === 'rejected' ? 'Ditolak' : $approval->status)) 
                                        }}
                                    </span>
                                </div>
                                <p class="mb-1 small">{{ $approval->approver->name ?? 'Approver' }}</p>
                                @if($approval->status !== 'pending')
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        {{ isset($approval->approval_datetime) ? \Carbon\Carbon::parse($approval->approval_datetime)->format('d/m/Y H:i') : \Carbon\Carbon::parse($approval->updated_at)->format('d/m/Y H:i') }}
                                    </small>
                                    
                                    @if($approval->status === 'rejected' && $approval->comments)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small class="text-danger">
                                                <i class="fas fa-comment-alt me-1"></i> 
                                                {{ $approval->comments }}
                                            </small>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="list-group-item text-center py-3">
                            <p class="mb-0 text-muted">Tidak ada data persetujuan</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        @if($vehicleRequest->assignment)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Kendaraan yang Ditugaskan</h5>
                </div>
                <div class="card-body">
                    @if($vehicleRequest->assignment->vehicle)
                        <h6>{{ $vehicleRequest->assignment->vehicle->registration_number }}</h6>
                        <p class="text-muted mb-0">{{ $vehicleRequest->assignment->vehicle->vehicleType->type_name ?? 'Tipe Kendaraan' }}</p>
                        <p class="text-muted mb-3">{{ $vehicleRequest->assignment->vehicle->model ?? '' }}</p>
                        
                        <h6 class="mt-3">Pengemudi</h6>
                        <p class="mb-0">{{ $vehicleRequest->assignment->driver->name ?? 'Belum ditentukan' }}</p>
                        @if($vehicleRequest->assignment->driver && $vehicleRequest->assignment->driver->phone)
                            <p class="mb-0">
                                <i class="fas fa-phone me-1 small"></i>
                                <a href="tel:{{ $vehicleRequest->assignment->driver->phone }}">{{ $vehicleRequest->assignment->driver->phone }}</a>
                            </p>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <p class="mb-0 text-muted">Kendaraan belum ditugaskan</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('vehicle-requests.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection 