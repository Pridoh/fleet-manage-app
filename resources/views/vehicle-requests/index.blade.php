@extends('layouts.app')

@section('title', 'Pemesanan Kendaraan - Sistem Manajemen Armada')

@section('page-title', 'Pemesanan Kendaraan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pemesanan Kendaraan</h5>
        <div>
            <a href="{{ route('vehicle-requests.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Buat Pemesanan
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <div class="row mb-3">
            <div class="col-md-8">
                <form action="{{ route('vehicle-requests.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="Cari..." name="search" value="{{ request('search') }}">
                        <button class="btn btn-sm btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="partially_approved" {{ request('status') == 'partially_approved' ? 'selected' : '' }}>Disetujui Sebagian</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" placeholder="Tanggal Mulai">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" placeholder="Tanggal Akhir">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('vehicle-requests.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-chart-bar me-1"></i> Lihat Laporan
                </a>
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
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Persetujuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($vehicleRequests) && $vehicleRequests->count() > 0)
                        @foreach($vehicleRequests as $request)
                            <tr>
                                <td>REQ-{{ str_pad($request->request_id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $request->requester->name ?? 'User' }}</td>
                                <td>{{ $request->destinationLocation->location_name ?? 'Tidak ada data' }}</td>
                                <td>{{ isset($request->pickup_datetime) ? \Carbon\Carbon::parse($request->pickup_datetime)->format('d/m/Y H:i') : '-' }}</td>
                                <td>{{ isset($request->return_datetime) ? \Carbon\Carbon::parse($request->return_datetime)->format('d/m/Y H:i') : '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $request->status === 'completed' ? 'success' : 
                                        ($request->status === 'pending' ? 'warning' : 
                                        ($request->status === 'rejected' ? 'danger' : 
                                        ($request->status === 'partially_approved' ? 'info' : 'secondary'))) 
                                    }}">
                                        {{ 
                                            $request->status === 'completed' ? 'Selesai' : 
                                            ($request->status === 'pending' ? 'Menunggu' : 
                                            ($request->status === 'rejected' ? 'Ditolak' : 
                                            ($request->status === 'partially_approved' ? 'Disetujui Sebagian' : $request->status))) 
                                        }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($request->approvals) && $request->approvals->count() > 0)
                                        <div class="d-flex gap-1 align-items-center">
                                            @php 
                                                $approvedCount = $request->approvals->where('status', 'approved')->count();
                                                $totalApprovals = $request->approvals->count();
                                                $progressPercent = $totalApprovals > 0 ? ($approvedCount / $totalApprovals) * 100 : 0;
                                            @endphp
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="badge bg-secondary">{{ $approvedCount }}/{{ $totalApprovals }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('vehicle-requests.show', $request->request_id) }}" class="btn btn-sm btn-primary" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Selalu tampilkan semua tombol -->
                                            <a href="{{ route('vehicle-requests.edit', $request->request_id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('vehicle-requests.destroy', $request->request_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemesanan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <!-- Akhir tombol -->
                                    </div>
                                    <!-- Debug Info: Status={{ $request->status }}, User={{ auth()->user()->id }}, Requester={{ $request->requester_id }}, IsAdmin={{ auth()->user()->isAdmin() ? 'Ya' : 'Tidak' }} -->
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center py-3">Tidak ada data pemesanan kendaraan</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($vehicleRequests))
            <div class="d-flex justify-content-center mt-4">
                {{ $vehicleRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 