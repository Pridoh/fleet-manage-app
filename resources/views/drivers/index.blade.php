@extends('layouts.app')

@section('title', 'Manajemen Driver')

@section('page-title', 'Manajemen Driver')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Driver</h1>
        <a href="{{ route('drivers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Tambah Driver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>No. SIM</th>
                            <th>Jenis SIM</th>
                            <th>Telepon</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                        <tr>
                            <td>{{ $driver->name }}</td>
                            <td>{{ $driver->license_number }}</td>
                            <td>{{ $driver->license_type }}</td>
                            <td>{{ $driver->phone }}</td>
                            <td>{{ $driver->location->name ?? '-' }}</td>
                            <td>
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
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <div class="btn-group gap-2">
                                    <a href="{{ route('drivers.show', $driver) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $driver->driver_id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Modal Hapus -->
                                <div class="modal fade" id="deleteModal{{ $driver->driver_id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $driver->driver_id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $driver->driver_id }}">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus driver <strong>{{ $driver->name }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('drivers.destroy', $driver) }}" method="POST">
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
                            <td colspan="7" class="text-center py-4">Tidak ada data driver</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $drivers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 