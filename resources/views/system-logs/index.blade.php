@extends('layouts.app')

@section('title', 'Log Sistem')

@section('page-title', 'Log Sistem')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Filter Log</h5>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="collapse" id="filterCollapse">
                    <div class="card-body">
                        <form action="{{ route('system-logs.index') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Pencarian</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, aksi...">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="entity_type" class="form-label">Tipe Entitas</label>
                                <select class="form-select" id="entity_type" name="entity_type">
                                    <option value="">Semua Tipe</option>
                                    @foreach($entityTypes as $type)
                                        <option value="{{ $type }}" {{ request('entity_type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="action" class="form-label">Aksi</label>
                                <select class="form-select" id="action" name="action">
                                    <option value="">Semua Aksi</option>
                                    @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                            {{ ucfirst($action) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                            </div>
                            
                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('system-logs.index') }}" class="btn btn-secondary me-2">Reset</a>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Log Sistem</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Pengguna</th>
                                    <th>Aksi</th>
                                    <th>Tipe Entitas</th>
                                    <th>ID Entitas</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->log_id }}</td>
                                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $log->user ? $log->user->name : 'Sistem' }}</td>
                                        <td>
                                            <span class="badge {{ $log->action == 'create' ? 'bg-success' : 
                                                ($log->action == 'update' ? 'bg-primary' : 
                                                ($log->action == 'delete' ? 'bg-danger' : 
                                                ($log->action == 'approve' ? 'bg-info' : 
                                                ($log->action == 'reject' ? 'bg-warning' : 'bg-secondary')))) }}">
                                                {{ ucfirst($log->action) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $log->entity_type)) }}</td>
                                        <td>{{ $log->entity_id }}</td>
                                        <td>{{ $log->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data log sistem</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            Menampilkan {{ $logs->firstItem() ?? 0 }} sampai {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log
                        </div>
                        <div>
                            {{ $logs->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 