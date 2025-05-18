@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Jadwal Maintenance</h1>
        <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left fa-sm"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Jadwal Maintenance</h6>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('maintenance.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="font-weight-bold text-primary mb-3">Informasi Kendaraan</h5>
                                        
                                        <div class="mb-3">
                                            <label for="vehicle_id" class="form-label">Kendaraan <span class="text-danger">*</span></label>
                                            <select class="form-control @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id" required>
                                                <option value="">Pilih Kendaraan</option>
                                                @foreach($vehicles as $vehicle)
                                                    <option value="{{ $vehicle->vehicle_id }}" {{ old('vehicle_id') == $vehicle->vehicle_id ? 'selected' : '' }}>
                                                        {{ $vehicle->registration_number }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('vehicle_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="odometer_reading" class="form-label">Odometer (km)</label>
                                            <input type="number" class="form-control @error('odometer_reading') is-invalid @enderror" id="odometer_reading" name="odometer_reading" value="{{ old('odometer_reading') }}">
                                            @error('odometer_reading')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="font-weight-bold text-primary mb-3">Jenis & Jadwal</h5>
                                        
                                        <div class="mb-3">
                                            <label for="maintenance_type" class="form-label">Jenis Maintenance <span class="text-danger">*</span></label>
                                            <select class="form-control @error('maintenance_type') is-invalid @enderror" id="maintenance_type" name="maintenance_type" required>
                                                <option value="">Pilih Jenis</option>
                                                @foreach($maintenanceTypes as $type)
                                                    @php
                                                        $typeLabel = [
                                                            'routine' => 'Service Rutin',
                                                            'repair' => 'Perbaikan',
                                                            'inspection' => 'Inspeksi',
                                                            'emergency' => 'Darurat'
                                                        ][$type] ?? ucfirst($type);
                                                    @endphp
                                                    <option value="{{ $type }}" {{ old('maintenance_type') == $type ? 'selected' : '' }}>
                                                        {{ $typeLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('maintenance_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') ?? date('Y-m-d') }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">Perkiraan Tanggal Selesai</label>
                                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="font-weight-bold text-primary mb-3">Detail Maintenance</h5>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Deskripsi Maintenance <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="cost" class="form-label">Perkiraan Biaya (Rp)</label>
                                                    <input type="number" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost') }}">
                                                    @error('cost')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="performed_by" class="form-label">Dilakukan Oleh</label>
                                                    <input type="text" class="form-control @error('performed_by') is-invalid @enderror" id="performed_by" name="performed_by" value="{{ old('performed_by') }}">
                                                    @error('performed_by')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Catatan</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Status maintenance akan otomatis diatur sebagai <strong>Dijadwalkan</strong>.
                            Status kendaraan akan diubah menjadi <strong>Maintenance</strong> ketika jadwal maintenance dimulai.
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Font size adjustments */
    .h3 {
        font-size: 1.5rem !important;
    }
    .h5, .h6, h5, h6 {
        font-size: 0.95rem !important;
    }
    .form-label {
        font-size: 0.85rem;
    }
    .alert {
        font-size: 0.85rem;
    }
    .text-danger {
        font-size: 0.85rem;
    }
    .btn {
        font-size: 0.85rem;
    }
</style>
@endsection 