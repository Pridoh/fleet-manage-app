@extends('layouts.app')

@section('title', 'Edit Pemesanan Kendaraan - Sistem Manajemen Armada')

@section('page-title', 'Edit Pemesanan Kendaraan')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Form Edit Pemesanan Kendaraan</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('vehicle-requests.update', $vehicleRequest) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="from_location" class="form-label">Lokasi Awal <span class="text-danger">*</span></label>
                            <select name="from_location_id" id="from_location" class="form-select @error('from_location_id') is-invalid @enderror" required>
                                <option value="">Pilih Lokasi Awal</option>
                                @foreach($locations ?? [] as $location)
                                    <option value="{{ $location->location_id }}" {{ old('from_location_id', $vehicleRequest->pickup_location_id) == $location->location_id ? 'selected' : '' }}>
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('from_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="to_location" class="form-label">Lokasi Tujuan <span class="text-danger">*</span></label>
                            <select name="to_location_id" id="to_location" class="form-select @error('to_location_id') is-invalid @enderror" required>
                                <option value="">Pilih Lokasi Tujuan</option>
                                @foreach($locations ?? [] as $location)
                                    <option value="{{ $location->location_id }}" {{ old('to_location_id', $vehicleRequest->destination_location_id) == $location->location_id ? 'selected' : '' }}>
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Tanggal & Jam Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $vehicleRequest->pickup_datetime ? date('Y-m-d\TH:i', strtotime($vehicleRequest->pickup_datetime)) : '') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Tanggal & Jam Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $vehicleRequest->return_datetime ? date('Y-m-d\TH:i', strtotime($vehicleRequest->return_datetime)) : '') }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vehicle_type" class="form-label">Jenis Kendaraan <span class="text-danger">*</span></label>
                        <select name="vehicle_type_id" id="vehicle_type" class="form-select @error('vehicle_type_id') is-invalid @enderror" required>
                            <option value="">Pilih Jenis Kendaraan</option>
                            @foreach($vehicleTypes ?? [] as $type)
                                <option value="{{ $type->vehicle_type_id }}" {{ old('vehicle_type_id', $vehicleRequest->vehicle_type_id) == $type->vehicle_type_id ? 'selected' : '' }}>
                                    {{ $type->type_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="num_passengers" class="form-label">Jumlah Penumpang <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('num_passengers') is-invalid @enderror" id="num_passengers" name="num_passengers" value="{{ old('num_passengers', $vehicleRequest->passenger_count) }}" min="1" required>
                        @error('num_passengers')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Tujuan Penggunaan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('purpose') is-invalid @enderror" id="purpose" name="purpose" rows="3" required>{{ old('purpose', $vehicleRequest->purpose) }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $vehicleRequest->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_approver_id" class="form-label">Pihak Persetujuan Level 1 <span class="text-danger">*</span></label>
                            <select name="first_approver_id" id="first_approver_id" class="form-select @error('first_approver_id') is-invalid @enderror" required>
                                <option value="">Pilih Pihak yang Menyetujui (Level 1)</option>
                                @foreach($approvers ?? [] as $approver)
                                    @php 
                                        $firstApproval = $vehicleRequest->approvals ? $vehicleRequest->approvals->where('approval_level', 1)->first() : null;
                                        $firstApproverId = $firstApproval ? $firstApproval->approver_id : null;
                                    @endphp
                                    <option value="{{ $approver->user_id }}" {{ old('first_approver_id', $firstApproverId) == $approver->user_id ? 'selected' : '' }}>
                                        {{ $approver->name }} - {{ $approver->department->department_name ?? 'Tidak ada departemen' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('first_approver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Pilih atasan atau pihak yang berwenang untuk persetujuan awal</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="second_approver_id" class="form-label">Pihak Persetujuan Level 2 <span class="text-danger">*</span></label>
                            <select name="second_approver_id" id="second_approver_id" class="form-select @error('second_approver_id') is-invalid @enderror" required>
                                <option value="">Pilih Pihak yang Menyetujui (Level 2)</option>
                                @foreach($approvers ?? [] as $approver)
                                    @php 
                                        $secondApproval = $vehicleRequest->approvals ? $vehicleRequest->approvals->where('approval_level', 2)->first() : null;
                                        $secondApproverId = $secondApproval ? $secondApproval->approver_id : null;
                                    @endphp
                                    <option value="{{ $approver->user_id }}" {{ old('second_approver_id', $secondApproverId) == $approver->user_id ? 'selected' : '' }}>
                                        {{ $approver->name }} - {{ $approver->department->department_name ?? 'Tidak ada departemen' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('second_approver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Pilih admin pool kendaraan atau pihak berwenang untuk persetujuan final</div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('vehicle-requests.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Perbarui Pemesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> Proses Persetujuan</h6>
                    <p class="mb-0">Pemesanan kendaraan Anda akan disetujui melalui 2 level persetujuan. Jika salah satu tidak menyetujui, maka permintaan akan ditolak.</p>
                </div>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex">
                        <i class="fas fa-check-circle text-primary me-2 mt-1"></i>
                        <div>
                            <strong>Atasan Langsung (Level 1)</strong>
                            <p class="mb-0 small text-muted">Atasan atau pihak yang berwenang dari departemen Anda</p>
                        </div>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-check-circle text-primary me-2 mt-1"></i>
                        <div>
                            <strong>Admin Pool Kendaraan (Level 2)</strong>
                            <p class="mb-0 small text-muted">Admin akan melakukan verifikasi ketersediaan kendaraan</p>
                        </div>
                    </li>
                </ul>
                <div class="alert alert-warning">
                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-1"></i> Perhatian</h6>
                    <p class="mb-0 small">Mengubah pihak yang menyetujui akan mengatur ulang status persetujuan menjadi 'menunggu'.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Prevent invalid date range selection
    document.getElementById('start_date').addEventListener('change', function() {
        document.getElementById('end_date').min = this.value;
    });
    
    // Validation when form is submitted
    document.querySelector('form').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('Tanggal & jam selesai harus lebih dari tanggal & jam mulai.');
        }
        
        const fromLocation = document.getElementById('from_location').value;
        const toLocation = document.getElementById('to_location').value;
        
        if (fromLocation === toLocation && fromLocation !== '') {
            e.preventDefault();
            alert('Lokasi awal dan tujuan tidak boleh sama.');
        }

        const firstApproverId = document.getElementById('first_approver_id').value;
        const secondApproverId = document.getElementById('second_approver_id').value;
        
        if (firstApproverId === secondApproverId && firstApproverId !== '') {
            e.preventDefault();
            alert('Pihak yang menyetujui pada Level 1 dan Level 2 tidak boleh sama.');
        }
    });
</script>
@endpush 