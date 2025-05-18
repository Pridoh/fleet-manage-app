@extends('layouts.app')

@section('title', 'Buat Pemesanan Kendaraan - Sistem Manajemen Armada')

@section('page-title', 'Buat Pemesanan Kendaraan')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Form Pemesanan Kendaraan</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('vehicle-requests.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="from_location" class="form-label">Lokasi Awal <span class="text-danger">*</span></label>
                            <select name="from_location_id" id="from_location" class="form-select @error('from_location_id') is-invalid @enderror" required>
                                <option value="">Pilih Lokasi Awal</option>
                                @foreach($locations ?? [] as $location)
                                    <option value="{{ $location->location_id }}" {{ old('from_location_id') == $location->location_id ? 'selected' : '' }}>
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
                                    <option value="{{ $location->location_id }}" {{ old('to_location_id') == $location->location_id ? 'selected' : '' }}>
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
                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Tanggal & Jam Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
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
                                <option value="{{ $type->vehicle_type_id }}" {{ old('vehicle_type_id') == $type->vehicle_type_id ? 'selected' : '' }}>
                                    {{ $type->type_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="vehicle_id" class="form-label">Kendaraan <span class="text-danger">*</span></label>
                        <select name="vehicle_id" id="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                            <option value="">Pilih Kendaraan</option>
                            @if(isset($availableVehicles) && count($availableVehicles) > 0)
                                @foreach($availableVehicles as $vehicle)
                                    <option value="{{ $vehicle->vehicle_id }}" data-vehicle-type="{{ $vehicle->vehicle_type_id }}" {{ old('vehicle_id') == $vehicle->vehicle_id ? 'selected' : '' }} style="display: none;">
                                        {{ $vehicle->registration_number }} - {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->vehicleType->type_name ?? 'Tipe Kendaraan' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('vehicle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Kendaraan yang tersedia berdasarkan jenis yang dipilih</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="driver_id" class="form-label">Pengemudi <span class="text-danger">*</span></label>
                        <select name="driver_id" id="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                            <option value="">Pilih Pengemudi</option>
                            @if(isset($drivers) && count($drivers) > 0)
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->driver_id }}" {{ old('driver_id') == $driver->driver_id ? 'selected' : '' }}>
                                        {{ $driver->name }} - {{ $driver->license_type }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('driver_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Pengemudi yang tersedia untuk tugas</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="num_passengers" class="form-label">Jumlah Penumpang <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('num_passengers') is-invalid @enderror" id="num_passengers" name="num_passengers" value="{{ old('num_passengers') }}" min="1" required>
                        @error('num_passengers')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Tujuan Penggunaan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('purpose') is-invalid @enderror" id="purpose" name="purpose" rows="3" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
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
                                    <option value="{{ $approver->user_id }}" {{ old('first_approver_id') == $approver->user_id ? 'selected' : '' }}>
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
                                    <option value="{{ $approver->user_id }}" {{ old('second_approver_id') == $approver->user_id ? 'selected' : '' }}>
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
                            <i class="fas fa-save me-1"></i> Simpan Pemesanan
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
                            <strong>Manajemen Pusat Kendaraan (Level 2)</strong>
                            <p class="mb-0 small text-muted">Manajemen pusat akan melakukan verifikasi ketersediaan kendaraan</p>
                        </div>
                    </li>
                </ul>
                <div class="card mb-3 bg-light border-0">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-clock me-1"></i> Waktu Pemrosesan</h6>
                        <p class="mb-0 small">Pemesanan kendaraan sebaiknya dilakukan minimal 1 hari sebelum tanggal penggunaan untuk memastikan ketersediaan kendaraan.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Kendaraan yang Tersedia</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush small overflow-auto" style="max-height: 500px;">
                    @if(isset($availableVehicles) && count($availableVehicles) > 0)
                        @foreach($availableVehicles as $vehicle)
                            <div class="list-group-item" data-vehicle-id="{{ $vehicle->vehicle_id }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold">{{ $vehicle->registration_number }}</span> 
                                        <p class="mb-0 text-muted">{{ $vehicle->vehicleType->type_name ?? 'Tipe Kendaraan' }}</p>
                                    </div>
                                    <span class="badge bg-success">Tersedia</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="list-group-item text-center py-3">
                            <p class="mb-0 text-muted">Tidak ada informasi kendaraan tersedia</p>
                        </div>
                    @endif
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
        updateAvailableVehiclesAndDrivers();
    });
    
    document.getElementById('end_date').addEventListener('change', function() {
        updateAvailableVehiclesAndDrivers();
    });
    
    // Function to update available vehicles and drivers based on selected dates
    function updateAvailableVehiclesAndDrivers() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const vehicleTypeId = document.getElementById('vehicle_type').value;
        
        if (!startDate || !endDate) return;
        
        // Tampilkan loading indicator
        document.getElementById('vehicle_id').disabled = true;
        document.getElementById('driver_id').disabled = true;
        
        const loadingMsg = document.createElement('div');
        loadingMsg.id = 'loading-message';
        loadingMsg.className = 'alert alert-info mt-2';
        loadingMsg.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memeriksa ketersediaan kendaraan dan pengemudi...';
        
        // Hapus pesan loading sebelumnya jika ada
        const oldLoadingMsg = document.getElementById('loading-message');
        if (oldLoadingMsg) oldLoadingMsg.remove();
        
        document.querySelector('.card-body').appendChild(loadingMsg);
        
        // Simpan pilihan saat ini
        const currentVehicleId = document.getElementById('vehicle_id').value;
        const currentDriverId = document.getElementById('driver_id').value;
        
        // Kirim permintaan AJAX untuk mendapatkan kendaraan dan pengemudi yang tersedia
        fetch(`{{ route('vehicle-requests.available') }}?start_date=${startDate}&end_date=${endDate}&vehicle_type_id=${vehicleTypeId}`)
            .then(response => response.json())
            .then(data => {
                // Hapus loading indicator
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) loadingMsg.remove();
                
                document.getElementById('vehicle_id').disabled = false;
                document.getElementById('driver_id').disabled = false;
                
                // Reset vehicle dan driver select
                const vehicleSelect = document.getElementById('vehicle_id');
                const driverSelect = document.getElementById('driver_id');
                
                // Hapus semua opsi kecuali default
                while (vehicleSelect.options.length > 1) {
                    vehicleSelect.remove(1);
                }
                
                // Tambahkan opsi kendaraan yang tersedia
                if (data.vehicles && data.vehicles.length > 0) {
                    data.vehicles.forEach(vehicle => {
                        const option = document.createElement('option');
                        option.value = vehicle.id;
                        option.textContent = `${vehicle.registration_number} - ${vehicle.brand} ${vehicle.model} (${vehicle.type})`;
                        option.dataset.vehicleType = vehicle.type;
                        option.dataset.capacity = vehicle.capacity;
                        vehicleSelect.appendChild(option);
                    });
                    
                    // Jika kendaraan yang dipilih sebelumnya masih tersedia, pilih kembali
                    if (currentVehicleId) {
                        const stillAvailable = data.vehicles.some(v => v.id == currentVehicleId);
                        if (stillAvailable) {
                            vehicleSelect.value = currentVehicleId;
                        }
                    }
                    
                    // Tampilkan pesan jika tidak ada kendaraan yang tersedia
                    if (data.vehicles.length === 0) {
                        const noVehiclesMsg = document.createElement('div');
                        noVehiclesMsg.className = 'alert alert-warning mt-2';
                        noVehiclesMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Tidak ada kendaraan yang tersedia pada waktu yang dipilih.';
                        vehicleSelect.parentNode.appendChild(noVehiclesMsg);
                    }
                }
                
                // Reset driver options
                while (driverSelect.options.length > 1) {
                    driverSelect.remove(1);
                }
                
                // Tambahkan opsi pengemudi yang tersedia
                if (data.drivers && data.drivers.length > 0) {
                    data.drivers.forEach(driver => {
                        const option = document.createElement('option');
                        option.value = driver.id;
                        option.textContent = `${driver.name} - ${driver.license_number}`;
                        driverSelect.appendChild(option);
                    });
                    
                    // Jika pengemudi yang dipilih sebelumnya masih tersedia, pilih kembali
                    if (currentDriverId) {
                        const stillAvailable = data.drivers.some(d => d.id == currentDriverId);
                        if (stillAvailable) {
                            driverSelect.value = currentDriverId;
                        }
                    }
                    
                    // Tampilkan pesan jika tidak ada pengemudi yang tersedia
                    if (data.drivers.length === 0) {
                        const noDriversMsg = document.createElement('div');
                        noDriversMsg.className = 'alert alert-warning mt-2';
                        noDriversMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Tidak ada pengemudi yang tersedia pada waktu yang dipilih.';
                        driverSelect.parentNode.appendChild(noDriversMsg);
                    }
                }
                
                // Update status ketersediaan di panel informasi
                updateAvailabilityInfo(data);
            })
            .catch(error => {
                console.error('Error fetching available vehicles and drivers:', error);
                
                // Hapus loading indicator jika terjadi error
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) loadingMsg.remove();
                
                document.getElementById('vehicle_id').disabled = false;
                document.getElementById('driver_id').disabled = false;
                
                // Tampilkan pesan error
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger mt-2';
                errorMsg.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> Terjadi kesalahan saat memeriksa ketersediaan. Silakan coba lagi.';
                document.querySelector('.card-body').appendChild(errorMsg);
                
                // Hapus pesan error setelah 5 detik
                setTimeout(() => {
                    errorMsg.remove();
                }, 5000);
            });
    }
    
    // Function to update availability information in the info panel
    function updateAvailabilityInfo(data) {
        const infoPanel = document.querySelector('.col-md-4 .card-body');
        
        // Hapus info ketersediaan sebelumnya jika ada
        const oldInfo = document.getElementById('availability-info');
        if (oldInfo) oldInfo.remove();
        
        // Buat panel info ketersediaan baru
        const availabilityInfo = document.createElement('div');
        availabilityInfo.id = 'availability-info';
        availabilityInfo.className = 'mt-3';
        
        let infoContent = `
            <h6 class="mb-2"><i class="fas fa-info-circle me-1"></i> Ketersediaan</h6>
            <div class="mb-2">
                <span class="badge bg-${data.vehicles.length > 0 ? 'success' : 'danger'} me-1">
                    ${data.vehicles.length} Kendaraan
                </span>
                <span class="badge bg-${data.drivers.length > 0 ? 'success' : 'danger'}">
                    ${data.drivers.length} Pengemudi
                </span>
            </div>
        `;
        
        // Tambahkan daftar kendaraan yang tersedia
        if (data.vehicles.length > 0) {
            infoContent += `
                <div class="small mb-2">
                    <strong>Kendaraan Tersedia:</strong>
                    <ul class="list-unstyled ms-2 mb-0">
            `;
            
            // Tampilkan maksimal 5 kendaraan
            const displayVehicles = data.vehicles.slice(0, 5);
            displayVehicles.forEach(vehicle => {
                infoContent += `<li>• ${vehicle.registration_number} - ${vehicle.brand} ${vehicle.model}</li>`;
            });
            
            // Tampilkan jumlah kendaraan lainnya jika ada
            if (data.vehicles.length > 5) {
                infoContent += `<li>• dan ${data.vehicles.length - 5} kendaraan lainnya</li>`;
            }
            
            infoContent += `
                    </ul>
                </div>
            `;
        }
        
        availabilityInfo.innerHTML = infoContent;
        infoPanel.appendChild(availabilityInfo);
    }
    
    // Filter vehicles based on selected vehicle type
    document.getElementById('vehicle_type').addEventListener('change', function() {
        const selectedType = this.value;
        const vehicleSelect = document.getElementById('vehicle_id');
        const vehicleOptions = vehicleSelect.querySelectorAll('option');
        
        // Reset vehicle selection
        vehicleSelect.value = '';
        
        // Hide all options first
        vehicleOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block'; // Always show the default option
            } else {
                option.style.display = 'none';
            }
        });
        
        // Show only vehicles of selected type
        if (selectedType) {
            vehicleOptions.forEach(option => {
                if (option.dataset.vehicleType === selectedType) {
                    // Check if this option is disabled (unavailable)
                    if (!option.disabled) {
                    option.style.display = 'block';
                    }
                }
            });
        }
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
        
        // Validasi dua approver tidak boleh sama
        const firstApproverId = document.getElementById('first_approver_id').value;
        const secondApproverId = document.getElementById('second_approver_id').value;
        
        if (firstApproverId === secondApproverId && firstApproverId !== '') {
            e.preventDefault();
            alert('Pihak yang menyetujui pada Level 1 dan Level 2 tidak boleh sama.');
        }
        
        // Validasi kendaraan dan pengemudi harus dipilih
        const vehicleId = document.getElementById('vehicle_id').value;
        const driverId = document.getElementById('driver_id').value;
        
        if (!vehicleId) {
            e.preventDefault();
            alert('Silakan pilih kendaraan yang akan digunakan.');
        }
        
        if (!driverId) {
            e.preventDefault();
            alert('Silakan pilih pengemudi yang akan bertugas.');
        }
    });
    
    // Trigger vehicle type change to initialize filtering
    document.addEventListener('DOMContentLoaded', function() {
        const vehicleTypeSelect = document.getElementById('vehicle_type');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        // Tambahkan event listener untuk perubahan tanggal
        startDateInput.addEventListener('change', updateAvailableVehiclesAndDrivers);
        endDateInput.addEventListener('change', updateAvailableVehiclesAndDrivers);
        
        // Tambahkan event listener untuk perubahan tipe kendaraan
        vehicleTypeSelect.addEventListener('change', function() {
            // Jika tanggal sudah diisi, update ketersediaan kendaraan
            if (startDateInput.value && endDateInput.value) {
                updateAvailableVehiclesAndDrivers();
            }
        });
        
        if (vehicleTypeSelect.value) {
            // Trigger the change event if a type is already selected (e.g. from old input)
            const event = new Event('change');
            vehicleTypeSelect.dispatchEvent(event);
        }
        
        // Jika tanggal sudah diisi (misalnya dari old input), update ketersediaan
        if (startDateInput.value && endDateInput.value) {
            updateAvailableVehiclesAndDrivers();
        }
    });
</script>
@endpush 