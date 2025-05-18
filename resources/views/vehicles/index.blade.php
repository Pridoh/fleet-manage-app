@extends('layouts.app')

@section('title', 'Manajemen Kendaraan')

@section('page-title', 'Manajemen Kendaraan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Kendaraan</h6>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('vehicles.update-status') }}" class="btn btn-sm btn-info me-2">
                            <i class="fas fa-sync-alt me-1"></i> Perbarui Status
                        </a>
                        <a href="{{ route('vehicles.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Tambah Kendaraan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 10%">No. Polisi</th>
                                    <th class="text-center" style="width: 15%">Jenis</th>
                                    <th class="text-center" style="width: 20%">Merek & Model</th>
                                    <th class="text-center" style="width: 8%">Tahun</th>
                                    <th class="text-center" style="width: 15%">Lokasi</th>
                                    <th class="text-center" style="width: 12%">Status</th>
                                    <th class="text-center" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $vehicle)
                                <tr>
                                    <td class="align-middle">{{ $vehicle->registration_number }}</td>
                                    <td class="align-middle">{{ $vehicle->vehicleType->type_name ?? '-' }}</td>
                                    <td class="align-middle">
                                        <div>{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                                        @if($vehicle->capacity)
                                            <small class="badge bg-secondary">{{ $vehicle->capacity }} Orang</small>
                                        @endif
                                        @if($vehicle->ownership_type === 'leased')
                                            <small class="badge bg-info">Sewa</small>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">{{ $vehicle->year }}</td>
                                    <td class="align-middle">{{ $vehicle->location->location_name ?? '-' }}</td>
                                    <td class="align-middle text-center">
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
                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        
                                        @if($vehicle->ownership_type === 'leased' && $vehicle->lease_end_date)
                                            <div class="mt-1">
                                                <small class="text-muted d-block">
                                                    Sewa sampai: {{ \Carbon\Carbon::parse($vehicle->lease_end_date)->format('d/m/Y') }}
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group gap-2">
                                            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $vehicle->vehicle_id }}" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Modal Hapus -->
                                        <div class="modal fade" id="deleteModal{{ $vehicle->vehicle_id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $vehicle->vehicle_id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel{{ $vehicle->vehicle_id }}">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Apakah Anda yakin ingin menghapus kendaraan dengan nomor polisi <strong>{{ $vehicle->registration_number }}</strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST">
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
                                    <td colspan="7" class="text-center py-4">Tidak ada data kendaraan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            Menampilkan {{ $vehicles->firstItem() ?? 0 }} sampai {{ $vehicles->lastItem() ?? 0 }} dari {{ $vehicles->total() }} kendaraan
                        </div>
                        <div>
                            {{ $vehicles->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 