@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Riwayat Penggunaan Kendaraan</h2>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Penggunaan</h6>
            <div>
                <a href="{{ route('vehicle-usage.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <form action="{{ route('vehicle-usage.history') }}" method="GET" class="form-inline">
                        <div class="input-group mb-2 mr-sm-2">
                            <input type="text" class="form-control form-control-sm" name="search" 
                                placeholder="Cari kendaraan" value="{{ request('search') }}">
                        </div>
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Dari</div>
                            </div>
                            <input type="date" class="form-control form-control-sm" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Sampai</div>
                            </div>
                            <input type="date" class="form-control form-control-sm" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary mb-2">
                            <i class="fas fa-search fa-sm"></i> Filter
                        </button>
                        <a href="{{ route('vehicle-usage.history') }}" class="btn btn-sm btn-secondary mb-2 ml-1">
                            <i class="fas fa-sync-alt fa-sm"></i> Reset
                        </a>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Kendaraan</th>
                            <th>Pengemudi</th>
                            <th>Pemohon</th>
                            <th>Tujuan</th>
                            <th>Waktu Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->assignment_id }}</td>
                                <td>
                                    {{ $assignment->vehicle->registration_number }}<br>
                                    <small class="text-muted">{{ $assignment->vehicle->brand }} {{ $assignment->vehicle->model }}</small>
                                </td>
                                <td>
                                    @if($assignment->driver)
                                        {{ $assignment->driver->name }}
                                    @else
                                        <span class="text-muted">Tanpa pengemudi</span>
                                    @endif
                                </td>
                                <td>{{ $assignment->request->requester->name }}</td>
                                <td>{{ $assignment->request->purpose }}</td>
                                <td>{{ \Carbon\Carbon::parse($assignment->actual_end_datetime)->format('d M Y H:i') }}</td>
                                <td>
                                    @if($assignment->status == 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($assignment->status == 'cancelled')
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vehicle-usage.show', $assignment) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye fa-sm"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data riwayat penggunaan kendaraan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $assignments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 