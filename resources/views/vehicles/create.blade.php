@extends('layouts.app')

@section('title', 'Tambah Kendaraan')

@section('page-title', 'Tambah Kendaraan Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('vehicles.store') }}">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Informasi Dasar</h5>
                                <hr>
                            </div>
                        
                            <div class="col-md-4 mb-3">
                                <label for="registration_number" class="form-label">Nomor Polisi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('registration_number') is-invalid @enderror" 
                                    id="registration_number" name="registration_number" value="{{ old('registration_number') }}" required>
                                @error('registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_type_id" class="form-label">Jenis Kendaraan <span class="text-danger">*</span></label>
                                <select class="form-select @error('vehicle_type_id') is-invalid @enderror" 
                                    id="vehicle_type_id" name="vehicle_type_id" required>
                                    <option value="" selected disabled>Pilih jenis kendaraan</option>
                                    @foreach($vehicleTypes as $type)
                                        <option value="{{ $type->vehicle_type_id }}" {{ old('vehicle_type_id') == $type->vehicle_type_id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="location_id" class="form-label">Lokasi</label>
                                <select class="form-select @error('location_id') is-invalid @enderror" 
                                    id="location_id" name="location_id">
                                    <option value="">Pilih lokasi</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->location_id }}" {{ old('location_id') == $location->location_id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="brand" class="form-label">Merek <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                    id="brand" name="brand" value="{{ old('brand') }}" required>
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                    id="model" name="model" value="{{ old('model') }}" required>
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('year') is-invalid @enderror" 
                                    id="year" name="year" value="{{ old('year') }}" min="1900" max="{{ date('Y') + 1 }}" required>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="capacity" class="form-label">Kapasitas Penumpang</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                    id="capacity" name="capacity" value="{{ old('capacity') }}" min="1">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Informasi Kepemilikan</h5>
                                <hr>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="ownership_type" class="form-label">Jenis Kepemilikan <span class="text-danger">*</span></label>
                                <select class="form-select @error('ownership_type') is-invalid @enderror" 
                                    id="ownership_type" name="ownership_type" required>
                                    @foreach($ownershipTypes as $type)
                                        <option value="{{ $type }}" {{ old('ownership_type') == $type ? 'selected' : '' }}>
                                            {{ $type === 'owned' ? 'Milik Sendiri' : 'Sewa' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ownership_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3 leasing-field">
                                <label for="lease_company" class="form-label">Perusahaan Leasing</label>
                                <input type="text" class="form-control @error('lease_company') is-invalid @enderror" 
                                    id="lease_company" name="lease_company" value="{{ old('lease_company') }}">
                                @error('lease_company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-2 mb-3 leasing-field">
                                <label for="lease_start_date" class="form-label">Mulai Sewa</label>
                                <input type="date" class="form-control @error('lease_start_date') is-invalid @enderror" 
                                    id="lease_start_date" name="lease_start_date" value="{{ old('lease_start_date') }}">
                                @error('lease_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-2 mb-3 leasing-field">
                                <label for="lease_end_date" class="form-label">Selesai Sewa</label>
                                <input type="date" class="form-control @error('lease_end_date') is-invalid @enderror" 
                                    id="lease_end_date" name="lease_end_date" value="{{ old('lease_end_date') }}">
                                @error('lease_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Status & Pemeliharaan</h5>
                                <hr>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                                            {{ $status === 'available' ? 'Tersedia' : 
                                               ($status === 'in_use' ? 'Digunakan' : 
                                               ($status === 'maintenance' ? 'Pemeliharaan' : 'Tidak Aktif')) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="last_service_date" class="form-label">Tanggal Service Terakhir</label>
                                <input type="date" class="form-control @error('last_service_date') is-invalid @enderror" 
                                    id="last_service_date" name="last_service_date" value="{{ old('last_service_date') }}">
                                @error('last_service_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="next_service_date" class="form-label">Jadwal Service Berikutnya</label>
                                <input type="date" class="form-control @error('next_service_date') is-invalid @enderror" 
                                    id="next_service_date" name="next_service_date" value="{{ old('next_service_date') }}">
                                @error('next_service_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                                                
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Kendaraan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle leasing fields based on ownership type
        function toggleLeasingFields() {
            const ownershipType = $('#ownership_type').val();
            if (ownershipType === 'leased') {
                $('.leasing-field').show();
                $('#lease_company, #lease_start_date, #lease_end_date').prop('required', true);
            } else {
                $('.leasing-field').hide();
                $('#lease_company, #lease_start_date, #lease_end_date').prop('required', false);
            }
        }
        
        // Initial state
        toggleLeasingFields();
        
        // On change
        $('#ownership_type').on('change', function() {
            toggleLeasingFields();
        });
    });
</script>
@endpush
