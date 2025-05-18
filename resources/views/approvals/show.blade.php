@extends('layouts.app')

@section('title', 'Detail Persetujuan - Sistem Manajemen Armada')

@section('page-title', 'Detail Persetujuan')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Permintaan Kendaraan</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0 text-muted">ID Permintaan</th>
                                <td>{{ $approval->request->request_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Pemohon</th>
                                <td>{{ $approval->request->requester->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Departemen</th>
                                <td>{{ $approval->request->requester->department->department_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Tanggal Permintaan</th>
                                <td>{{ isset($approval->request->created_at) ? \Carbon\Carbon::parse($approval->request->created_at)->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0 text-muted">Status</th>
                                <td>
                                    <span class="badge bg-{{ 
                                        $approval->request->status === 'approved' ? 'success' : 
                                        ($approval->request->status === 'pending' ? 'warning' : 
                                        ($approval->request->status === 'rejected' ? 'danger' : 'secondary')) 
                                    }}">
                                        {{ 
                                            $approval->request->status === 'approved' ? 'Disetujui' : 
                                            ($approval->request->status === 'pending' ? 'Menunggu' : 
                                            ($approval->request->status === 'rejected' ? 'Ditolak' : $approval->request->status)) 
                                        }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Level Persetujuan</th>
                                <td>
                                    <span class="badge bg-info">Level {{ $approval->approval_level }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Jenis Kendaraan</th>
                                <td>{{ $approval->request->vehicleType->type_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Jumlah Penumpang</th>
                                <td>{{ $approval->request->passenger_count ?? '-' }} orang</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6>Rincian Perjalanan</h6>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1 text-muted small">Dari</p>
                                        <p class="mb-3 fw-bold">{{ $approval->request->pickupLocation->location_name ?? '-' }}</p>
                                        
                                        <p class="mb-1 text-muted small">Mulai</p>
                                        <p class="mb-0 fw-bold">{{ isset($approval->request->pickup_datetime) ? \Carbon\Carbon::parse($approval->request->pickup_datetime)->format('d/m/Y H:i') : '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1 text-muted small">Ke</p>
                                        <p class="mb-3 fw-bold">{{ $approval->request->destinationLocation->location_name ?? '-' }}</p>
                                        
                                        <p class="mb-1 text-muted small">Selesai</p>
                                        <p class="mb-0 fw-bold">{{ isset($approval->request->return_datetime) ? \Carbon\Carbon::parse($approval->request->return_datetime)->format('d/m/Y H:i') : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6>Tujuan Penggunaan</h6>
                        <p>{{ $approval->request->purpose ?? '-' }}</p>
                        
                        @if($approval->request->notes)
                            <h6>Catatan Tambahan</h6>
                            <p>{{ $approval->request->notes }}</p>
                        @endif
                    </div>
                </div>
                
                @if($approval->status === 'pending')
                    <div class="card bg-light border-primary mb-4">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i> Petunjuk Persetujuan</h6>
                            <p class="card-text">Anda diminta untuk menyetujui atau menolak permintaan kendaraan ini. Jika Anda menolak, harap berikan alasan yang jelas.</p>
                        </div>
                    </div>
                    
                    @if(Auth::user()->hasRole('approver'))
                        <form action="{{ route('approvals.approve', $approval->approval_id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success me-2">
                                <i class="fas fa-check me-1"></i> Setujui Permintaan
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-1"></i> Tolak Permintaan
                        </button>
                        
                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('approvals.reject', $approval->approval_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="rejectModalLabel">Tolak Permintaan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="rejection_reason" class="form-label">Alasan Penolakan</label>
                                                <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Hanya approver yang dapat melakukan persetujuan atau penolakan permintaan.
                        </div>
                    @endif
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
                    @if(isset($approvalChain) && count($approvalChain) > 0)
                        @foreach($approvalChain as $chainApproval)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0">Level {{ $chainApproval->approval_level }}</h6>
                                    <span class="badge bg-{{ 
                                        $chainApproval->status === 'approved' ? 'success' : 
                                        ($chainApproval->status === 'pending' ? 'warning' : 
                                        ($chainApproval->status === 'rejected' ? 'danger' : 'secondary')) 
                                    }}">
                                        {{ 
                                            $chainApproval->status === 'approved' ? 'Disetujui' : 
                                            ($chainApproval->status === 'pending' ? 'Menunggu' : 
                                            ($chainApproval->status === 'rejected' ? 'Ditolak' : $chainApproval->status)) 
                                        }}
                                    </span>
                                </div>
                                <p class="mb-1 small">{{ $chainApproval->approver->name ?? 'Approver' }}</p>
                                @if($chainApproval->status !== 'pending')
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        {{ isset($chainApproval->updated_at) ? \Carbon\Carbon::parse($chainApproval->updated_at)->format('d/m/Y H:i') : '-' }}
                                    </small>
                                    
                                    @if($chainApproval->status === 'rejected' && $chainApproval->rejection_reason)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small class="text-danger">
                                                <i class="fas fa-comment-alt me-1"></i> 
                                                {{ $chainApproval->rejection_reason }}
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
        
        @if($approval->request->assignment)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Kendaraan yang Dipilih</h5>
                </div>
                <div class="card-body">
                    @if($approval->request->assignment->vehicle)
                        <h6>{{ $approval->request->assignment->vehicle->registration_number }}</h6>
                        <p class="text-muted mb-0">{{ $approval->request->assignment->vehicle->brand ?? '' }} {{ $approval->request->assignment->vehicle->model ?? '' }}</p>
                        <p class="text-muted mb-3">{{ $approval->request->assignment->vehicle->vehicleType->type_name ?? 'Tipe Kendaraan' }}</p>
                        
                        <h6 class="mt-3">Pengemudi</h6>
                        <p class="mb-0">{{ $approval->request->assignment->driver->name ?? 'Belum ditentukan' }}</p>
                        @if($approval->request->assignment->driver && $approval->request->assignment->driver->phone)
                            <p class="mb-0">
                                <i class="fas fa-phone me-1 small"></i>
                                <a href="tel:{{ $approval->request->assignment->driver->phone }}">{{ $approval->request->assignment->driver->phone }}</a>
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
    <a href="{{ route('approvals.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection 