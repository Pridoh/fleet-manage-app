@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Log Penggunaan Kendaraan</h1>
        <div>
            <a href="{{ route('vehicle-usage.logs.index', $assignment) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kendaraan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Kendaraan:</strong>
                        <p>{{ $assignment->vehicle->registration_number }} - {{ $assignment->vehicle->brand }} {{ $assignment->vehicle->model }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Driver:</strong>
                        <p>{{ $assignment->driver->name ?? 'Tanpa driver' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <p>
                            @if($assignment->status == 'assigned')
                                <span class="badge bg-warning text-dark">Ditugaskan</span>
                            @elseif($assignment->status == 'in_progress')
                                <span class="badge bg-info text-white">Sedang Digunakan</span>
                            @elseif($assignment->status == 'completed')
                                <span class="badge bg-success">Selesai</span>
                            @elseif($assignment->status == 'cancelled')
                                <span class="badge bg-danger">Dibatalkan</span>
                            @endif
                        </p>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i> Mengubah log dapat berpengaruh pada perhitungan BBM dan jarak tempuh kendaraan.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Log</h6>
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

                    <form action="{{ route('vehicle-usage.logs.update', [$assignment, $log]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="log_type" class="form-label">Tipe Log <span class="text-danger">*</span></label>
                                <select name="log_type" id="log_type" class="form-select @error('log_type') is-invalid @enderror" required onchange="toggleLogFields()">
                                    <option value="">Pilih Tipe Log</option>
                                    @foreach($logTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('log_type', $log->log_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('log_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="log_datetime" class="form-label">Waktu <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('log_datetime') is-invalid @enderror" id="log_datetime" name="log_datetime" value="{{ old('log_datetime', $log->log_datetime ? \Carbon\Carbon::parse($log->log_datetime)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
                                @error('log_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="location_id" class="form-label">Lokasi</label>
                                <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror">
                                    <option value="">Pilih Lokasi</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->location_id }}" {{ old('location_id', $log->location_id) == $location->location_id ? 'selected' : '' }}>
                                            {{ $location->location_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="odometer_reading" class="form-label">Odometer (km)</label>
                                <input type="number" class="form-control @error('odometer_reading') is-invalid @enderror" id="odometer_reading" name="odometer_reading" value="{{ old('odometer_reading', $log->odometer_reading) }}">
                                @error('odometer_reading')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Pembacaan odometer saat ini</small>
                            </div>
                        </div>
                        
                        <!-- Field khusus untuk tipe log 'refuel' (Pengisian BBM) -->
                        <div class="row mb-3" id="refuel-fields" style="display: none;">
                            <div class="col-md-4">
                                <label for="fuel_added" class="form-label">BBM Ditambahkan (liter)</label>
                                <input type="number" step="0.1" min="0" class="form-control @error('fuel_added') is-invalid @enderror" id="fuel_added" name="fuel_added" value="{{ old('fuel_added', $log->fuel_added) }}">
                                @error('fuel_added')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="fuel_cost" class="form-label">Biaya BBM (Rp)</label>
                                <input type="number" step="100" min="0" class="form-control @error('fuel_cost') is-invalid @enderror" id="fuel_cost" name="fuel_cost" value="{{ old('fuel_cost', $log->fuel_cost) }}">
                                @error('fuel_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="fuel_level" class="form-label">Level BBM setelah isi (liter)</label>
                                <input type="number" step="0.1" min="0" class="form-control @error('fuel_level') is-invalid @enderror" id="fuel_level" name="fuel_level" value="{{ old('fuel_level', $log->fuel_level) }}">
                                @error('fuel_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Field untuk level BBM (untuk tipe log selain 'refuel') -->
                        <div class="row mb-3" id="fuel-level-field">
                            <div class="col-md-6">
                                <label for="general_fuel_level" class="form-label">Level BBM (liter)</label>
                                <input type="number" step="0.1" min="0" class="form-control @error('fuel_level') is-invalid @enderror" id="general_fuel_level" name="fuel_level" value="{{ old('fuel_level', $log->fuel_level) }}">
                                @error('fuel_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Perkiraan level BBM saat ini</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $log->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('vehicle-usage.logs.index', $assignment) }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleLogFields() {
        const logType = document.getElementById('log_type').value;
        const refuelFields = document.getElementById('refuel-fields');
        const fuelLevelField = document.getElementById('fuel-level-field');
        
        if (logType === 'refuel') {
            refuelFields.style.display = 'flex';
            fuelLevelField.style.display = 'none';
        } else {
            refuelFields.style.display = 'none';
            fuelLevelField.style.display = 'flex';
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleLogFields();
    });
</script>
@endsection 