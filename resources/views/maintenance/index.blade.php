@extends('layouts.app')

@section('title', 'Jadwal Maintenance Kendaraan')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Jadwal Maintenance Kendaraan</h1>
        <a href="{{ route('maintenance.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Tambah Jadwal
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Kendaraan</th>
                            <th>Jenis Maintenance</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                        <tr>
                            <td>
                                {{ $maintenance->vehicle->brand ?? 'Tidak ada' }} {{ $maintenance->vehicle->model ?? '' }}
                                <div class="small text-muted">{{ $maintenance->vehicle->registration_number ?? '' }}</div>
                            </td>
                            <td>
                                @php
                                    $type = $maintenance->maintenance_type;
                                    $typeLabel = [
                                        'routine' => 'Service Rutin',
                                        'repair' => 'Perbaikan',
                                        'inspection' => 'Inspeksi',
                                        'emergency' => 'Darurat'
                                    ][$type] ?? ucfirst($type);
                                    
                                    $typeClass = [
                                        'routine' => 'bg-info',
                                        'repair' => 'bg-warning',
                                        'inspection' => 'bg-primary',
                                        'emergency' => 'bg-danger'
                                    ][$type] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                            </td>
                            <td>{{ $maintenance->start_date ? $maintenance->start_date->format('d M Y') : '-' }}</td>
                            <td>{{ $maintenance->end_date ? $maintenance->end_date->format('d M Y') : '-' }}</td>
                            <td>
                                @php
                                    $status = $maintenance->status;
                                    $statusLabel = [
                                        'scheduled' => 'Dijadwalkan',
                                        'in_progress' => 'Dalam Proses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan'
                                    ][$status] ?? ucfirst($status);
                                    
                                    $statusClass = [
                                        'scheduled' => 'bg-warning',
                                        'in_progress' => 'bg-info',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger'
                                    ][$status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @if($maintenance->cost)
                                    Rp {{ number_format($maintenance->cost, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('maintenance.show', $maintenance) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $maintenance->maintenance_id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Modal Hapus -->
                                <div class="modal fade" id="deleteModal{{ $maintenance->maintenance_id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $maintenance->maintenance_id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $maintenance->maintenance_id }}">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus jadwal maintenance ini?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('maintenance.destroy', $maintenance) }}" method="POST">
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
                            <td colspan="7" class="text-center py-4">Tidak ada data jadwal maintenance</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $maintenances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 