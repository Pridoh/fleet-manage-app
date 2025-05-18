@extends('layouts.app')

@section('title', 'Tambah Driver')

@section('page-title', 'Tambah Driver Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('drivers.store') }}">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Informasi Pribadi</h5>
                                <hr>
                            </div>
                        
                            <div class="col-md-4 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                    id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="birth_date" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                    id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-8 mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                    id="address" name="address" rows="2">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Informasi SIM</h5>
                                <hr>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="license_number" class="form-label">Nomor SIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                    id="license_number" name="license_number" value="{{ old('license_number') }}" required>
                                @error('license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="license_type" class="form-label">Jenis SIM <span class="text-danger">*</span></label>
                                <select class="form-select @error('license_type') is-invalid @enderror" 
                                    id="license_type" name="license_type" required>
                                    <option value="" selected disabled>Pilih jenis SIM</option>
                                    <option value="A" {{ old('license_type') == 'A' ? 'selected' : '' }}>SIM A</option>
                                    <option value="B1" {{ old('license_type') == 'B1' ? 'selected' : '' }}>SIM B1</option>
                                    <option value="B2" {{ old('license_type') == 'B2' ? 'selected' : '' }}>SIM B2</option>
                                    <option value="C" {{ old('license_type') == 'C' ? 'selected' : '' }}>SIM C</option>
                                </select>
                                @error('license_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="license_expiry" class="form-label">Tanggal Berlaku <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('license_expiry') is-invalid @enderror" 
                                    id="license_expiry" name="license_expiry" value="{{ old('license_expiry') }}" required>
                                @error('license_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Informasi Pekerjaan</h5>
                                <hr>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="location_id" class="form-label">Lokasi Penempatan</label>
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
                                <label for="join_date" class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('join_date') is-invalid @enderror" 
                                    id="join_date" name="join_date" value="{{ old('join_date') ?? date('Y-m-d') }}" required>
                                @error('join_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                    <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                                    <option value="on_duty" {{ old('status') == 'on_duty' ? 'selected' : '' }}>Sedang Bertugas</option>
                                    <option value="day_off" {{ old('status') == 'day_off' ? 'selected' : '' }}>Libur</option>
                                    <option value="sick" {{ old('status') == 'sick' ? 'selected' : '' }}>Sakit</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('drivers.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Driver</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
