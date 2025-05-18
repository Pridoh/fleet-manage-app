@extends('layouts.app')

@section('title', 'Persetujuan Pemesanan - Sistem Manajemen Armada')

@section('page-title', 'Persetujuan Pemesanan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link {{ request('tab') != 'history' ? 'active' : '' }}" href="{{ route('approvals.index') }}">Menunggu Persetujuan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('tab') == 'history' ? 'active' : '' }}" href="{{ route('approvals.index', ['tab' => 'history']) }}">Riwayat Persetujuan</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form action="{{ route('approvals.index', ['tab' => request('tab')]) }}" method="GET" class="d-flex gap-2">
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="Cari..." name="search" value="{{ request('search') }}">
                        <button class="btn btn-sm btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    @if(request('tab') == 'history')
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('approvals.index', ['tab' => request('tab')]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-secondary me-2">Total: {{ $pendingApprovals->total() ?? 0 }}</span>
            </div>
        </div>
        
        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Pemohon</th>
                        <th>Tujuan</th>
                        <th>Tanggal Permintaan</th>
                        <th>Tanggal Penggunaan</th>
                        @if(request('tab') == 'history')
                            <th>Status</th>
                            <th>Tanggal Persetujuan</th>
                        @else
                            <th>Level</th>
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if(isset($pendingApprovals) && $pendingApprovals->count() > 0)
                        @foreach($pendingApprovals as $approval)
                            <tr>
                                <td>{{ $approval->approval_id }}</td>
                                <td>{{ $approval->request->requester->name ?? 'User' }}</td>
                                <td>{{ $approval->request->destinationLocation->location_name ?? 'Tujuan' }}</td>
                                <td>{{ isset($approval->request->created_at) ? \Carbon\Carbon::parse($approval->request->created_at)->format('d/m/Y') : '-' }}</td>
                                <td>{{ isset($approval->request->pickup_datetime) ? \Carbon\Carbon::parse($approval->request->pickup_datetime)->format('d/m/Y H:i') : '-' }}</td>
                                
                                @if(request('tab') == 'history')
                                    <td>
                                        <span class="badge bg-{{ $approval->status === 'approved' ? 'success' : ($approval->status === 'rejected' ? 'danger' : 'secondary') }}">
                                            {{ $approval->status === 'approved' ? 'Disetujui' : ($approval->status === 'rejected' ? 'Ditolak' : $approval->status) }}
                                        </span>
                                    </td>
                                    <td>{{ isset($approval->updated_at) ? \Carbon\Carbon::parse($approval->updated_at)->format('d/m/Y H:i') : '-' }}</td>
                                @else
                                    <td>
                                        <span class="badge bg-info">Level {{ $approval->approval_level }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('approvals.show', $approval->approval_id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            @if(Auth::user()->hasRole('approver'))
                                            <form action="{{ route('approvals.approve', $approval->approval_id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('approvals.reject', $approval->approval_id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i> Tolak
                                                </button>
                                            </form>
                                            @endif
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $approval->approval_id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $approval->approval_id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('approvals.reject', $approval->approval_id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="rejectModalLabel{{ $approval->approval_id }}">Tolak Permintaan</h5>
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
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="{{ request('tab') == 'history' ? '7' : '6' }}" class="text-center py-3">
                                @if(request('tab') == 'history')
                                    Tidak ada riwayat persetujuan
                                @else
                                    Tidak ada permintaan yang menunggu persetujuan
                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($pendingApprovals))
            <div class="d-flex justify-content-center mt-4">
                {{ $pendingApprovals->appends(['tab' => request('tab'), 'search' => request('search'), 'status' => request('status')])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 