<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Location;
use App\Models\VehicleAssignment;
use App\Models\VehicleMaintenance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleController extends Controller
{
    /**
     * Display a listing of the vehicles
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Update vehicle statuses before displaying
        $this->updateVehicleStatuses();
        
        $vehicles = Vehicle::with(['vehicleType', 'location'])
            ->orderBy('registration_number')
            ->paginate(10);
            
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Update vehicle statuses based on assignments, maintenance, and lease end dates
     */
    private function updateVehicleStatuses()
    {
        // Get all vehicles
        $vehicles = Vehicle::all();
        $now = Carbon::now();
        
        foreach ($vehicles as $vehicle) {
            $statusChanged = false;
            $oldStatus = $vehicle->status;
            
            // Cek kendaraan sewa yang sudah habis masa sewanya
            if ($vehicle->ownership_type === 'leased' && $vehicle->lease_end_date && $now->greaterThan($vehicle->lease_end_date)) {
                $vehicle->status = 'inactive';
                $statusChanged = true;
            } 
            // Jika kendaraan sewa yang sebelumnya inactive tapi masa sewanya sudah diperpanjang
            elseif ($vehicle->ownership_type === 'leased' && $vehicle->lease_end_date && $vehicle->status === 'inactive' && $now->lessThan($vehicle->lease_end_date)) {
                $vehicle->status = 'available';
                $statusChanged = true;
            }
            // Cek kendaraan yang sedang dalam pemeliharaan
            else {
                $activeMaintenance = VehicleMaintenance::where('vehicle_id', $vehicle->vehicle_id)
                    ->where('status', 'in_progress')
                    ->whereDate('start_date', '<=', $now)
                    ->where(function($query) use ($now) {
                        $query->whereDate('end_date', '>=', $now)
                              ->orWhereNull('end_date');
                    })
                    ->first();
                
                if ($activeMaintenance) {
                    $vehicle->status = 'maintenance';
                    $statusChanged = true;
                }
                // Cek kendaraan yang sedang digunakan
                else {
                    // Perbaikan query untuk memeriksa kendaraan yang sedang digunakan
                    $activeAssignment = VehicleAssignment::where('vehicle_id', $vehicle->vehicle_id)
                        ->whereHas('request', function($query) use ($now) {
                            $query->where('status', 'approved')
                                  ->where(function($q) use ($now) {
                                      $q->where(function($innerQ) use ($now) {
                                          $innerQ->whereDate('pickup_datetime', '<=', $now->format('Y-m-d'))
                                                ->whereDate('return_datetime', '>=', $now->format('Y-m-d'));
                                      });
                                  });
                        })
                        ->first();
                    
                    if ($activeAssignment) {
                        $vehicle->status = 'in_use';
                        $statusChanged = true;
                    } 
                    // Jika tidak sedang digunakan dan tidak dalam pemeliharaan, set available
                    elseif ($vehicle->status === 'in_use' || $vehicle->status === 'maintenance') {
                        $vehicle->status = 'available';
                        $statusChanged = true;
                    }
                }
            }
            
            // Simpan perubahan jika status berubah
            if ($statusChanged && $oldStatus !== $vehicle->status) {
                $vehicle->save();
                
                // Log perubahan status
                \App\Models\SystemLog::create([
                    'user_id' => auth()->id() ?? 1, // Default ke admin jika tidak ada user yang login
                    'action' => 'update',
                    'entity_type' => 'vehicle',
                    'entity_id' => $vehicle->vehicle_id,
                    'description' => "Status kendaraan {$vehicle->registration_number} berubah dari {$oldStatus} menjadi {$vehicle->status}"
                ]);
            }
        }
    }

    /**
     * Show the form for creating a new vehicle
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $vehicleTypes = VehicleType::all();
        $locations = Location::all();
        $ownershipTypes = ['owned', 'leased'];
        $statuses = ['available', 'in_use', 'maintenance', 'inactive'];
        
        return view('vehicles.create', compact('vehicleTypes', 'locations', 'ownershipTypes', 'statuses'));
    }

    /**
     * Store a newly created vehicle in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|max:20|unique:vehicles',
            'vehicle_type_id' => 'required|exists:vehicle_types,vehicle_type_id',
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'capacity' => 'nullable|integer|min:1',
            'ownership_type' => 'required|in:owned,leased',
            'lease_company' => 'nullable|required_if:ownership_type,leased|string|max:100',
            'lease_start_date' => 'nullable|required_if:ownership_type,leased|date',
            'lease_end_date' => 'nullable|required_if:ownership_type,leased|date|after_or_equal:lease_start_date',
            'location_id' => 'nullable|exists:locations,location_id',
            'status' => 'required|in:available,in_use,maintenance,inactive',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date|after_or_equal:last_service_date',
        ]);
        
        if ($request->ownership_type == 'owned') {
            // Jika statusnya owned, kosongkan data terkait leasing
            $validated['lease_company'] = null;
            $validated['lease_start_date'] = null;
            $validated['lease_end_date'] = null;
        }
        
        // Jika kendaraan sewa dan tanggal akhir sewa sudah lewat, set status ke inactive
        if ($request->ownership_type == 'leased' && $request->lease_end_date && Carbon::parse($request->lease_end_date)->isPast()) {
            $validated['status'] = 'inactive';
        }
        
        Vehicle::create($validated);
        
        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    /**
     * Display the specified vehicle
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\View\View
     */
    public function show(Vehicle $vehicle)
    {
        // Update status kendaraan sebelum menampilkan
        $this->updateVehicleStatus($vehicle);
        
        $vehicle->load([
            'vehicleType', 
            'location', 
            'maintenanceRecords' => function($query) {
                $query->orderBy('start_date', 'desc');
            },
            'assignments' => function($query) {
                $query->orderBy('actual_start_datetime', 'desc');
            },
            'assignments.request',
            'assignments.request.requester',
            'assignments.driver'
        ]);
        
        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Update status for a single vehicle
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return void
     */
    private function updateVehicleStatus(Vehicle $vehicle)
    {
        $now = Carbon::now();
        $oldStatus = $vehicle->status;
        $statusChanged = false;
        
        // Cek kendaraan sewa yang sudah habis masa sewanya
        if ($vehicle->ownership_type === 'leased' && $vehicle->lease_end_date && $now->greaterThan($vehicle->lease_end_date)) {
            $vehicle->status = 'inactive';
            $statusChanged = true;
        } 
        // Jika kendaraan sewa yang sebelumnya inactive tapi masa sewanya sudah diperpanjang
        elseif ($vehicle->ownership_type === 'leased' && $vehicle->lease_end_date && $vehicle->status === 'inactive' && $now->lessThan($vehicle->lease_end_date)) {
            $vehicle->status = 'available';
            $statusChanged = true;
        }
        // Cek kendaraan yang sedang dalam pemeliharaan
        else {
            $activeMaintenance = VehicleMaintenance::where('vehicle_id', $vehicle->vehicle_id)
                ->where('status', 'in_progress')
                ->whereDate('start_date', '<=', $now)
                ->where(function($query) use ($now) {
                    $query->whereDate('end_date', '>=', $now)
                          ->orWhereNull('end_date');
                })
                ->first();
            
            if ($activeMaintenance) {
                $vehicle->status = 'maintenance';
                $statusChanged = true;
            }
            // Cek kendaraan yang sedang digunakan
            else {
                // Perbaikan query untuk memeriksa kendaraan yang sedang digunakan
                $activeAssignment = VehicleAssignment::where('vehicle_id', $vehicle->vehicle_id)
                    ->whereHas('request', function($query) use ($now) {
                        $query->where('status', 'approved')
                              ->where(function($q) use ($now) {
                                  $q->where(function($innerQ) use ($now) {
                                      $innerQ->whereDate('pickup_datetime', '<=', $now->format('Y-m-d'))
                                            ->whereDate('return_datetime', '>=', $now->format('Y-m-d'));
                                  });
                              });
                    })
                    ->first();
                
                if ($activeAssignment) {
                    $vehicle->status = 'in_use';
                    $statusChanged = true;
                } 
                // Jika tidak sedang digunakan dan tidak dalam pemeliharaan, set available
                elseif ($vehicle->status === 'in_use' || $vehicle->status === 'maintenance') {
                    $vehicle->status = 'available';
                    $statusChanged = true;
                }
            }
        }
        
        // Simpan perubahan jika status berubah
        if ($statusChanged && $oldStatus !== $vehicle->status) {
            $vehicle->save();
            
            // Log perubahan status
            \App\Models\SystemLog::create([
                'user_id' => auth()->id() ?? 1, // Default ke admin jika tidak ada user yang login
                'action' => 'update',
                'entity_type' => 'vehicle',
                'entity_id' => $vehicle->vehicle_id,
                'description' => "Status kendaraan {$vehicle->registration_number} berubah dari {$oldStatus} menjadi {$vehicle->status}"
            ]);
        }
    }

    /**
     * Show the form for editing the specified vehicle
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\View\View
     */
    public function edit(Vehicle $vehicle)
    {
        $vehicleTypes = VehicleType::all();
        $locations = Location::all();
        $ownershipTypes = ['owned', 'leased'];
        $statuses = ['available', 'in_use', 'maintenance', 'inactive'];
        
        return view('vehicles.edit', compact('vehicle', 'vehicleTypes', 'locations', 'ownershipTypes', 'statuses'));
    }

    /**
     * Update the specified vehicle in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|max:20|unique:vehicles,registration_number,' . $vehicle->vehicle_id . ',vehicle_id',
            'vehicle_type_id' => 'required|exists:vehicle_types,vehicle_type_id',
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'capacity' => 'nullable|integer|min:1',
            'ownership_type' => 'required|in:owned,leased',
            'lease_company' => 'nullable|required_if:ownership_type,leased|string|max:100',
            'lease_start_date' => 'nullable|required_if:ownership_type,leased|date',
            'lease_end_date' => 'nullable|required_if:ownership_type,leased|date|after_or_equal:lease_start_date',
            'location_id' => 'nullable|exists:locations,location_id',
            'status' => 'required|in:available,in_use,maintenance,inactive',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date|after_or_equal:last_service_date',
        ]);
        
        if ($request->ownership_type == 'owned') {
            // Jika statusnya owned, kosongkan data terkait leasing
            $validated['lease_company'] = null;
            $validated['lease_start_date'] = null;
            $validated['lease_end_date'] = null;
        }
        
        // Jika kendaraan sewa dan tanggal akhir sewa sudah lewat, set status ke inactive
        if ($request->ownership_type == 'leased' && $request->lease_end_date) {
            if (Carbon::parse($request->lease_end_date)->isPast()) {
                $validated['status'] = 'inactive';
            } 
            // Jika tanggal sewa diperpanjang dan status sebelumnya inactive, ubah ke available
            elseif ($vehicle->status === 'inactive' && $vehicle->ownership_type === 'leased' && 
                    ($vehicle->lease_end_date === null || Carbon::parse($vehicle->lease_end_date)->isPast())) {
                $validated['status'] = 'available';
                
                // Log perubahan status
                \App\Models\SystemLog::create([
                    'user_id' => auth()->id() ?? 1,
                    'action' => 'update',
                    'entity_type' => 'vehicle',
                    'entity_id' => $vehicle->vehicle_id,
                    'description' => "Status kendaraan {$vehicle->registration_number} berubah dari inactive menjadi available karena masa sewa diperpanjang"
                ]);
            }
        }
        
        $vehicle->update($validated);
        
        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil diperbarui.');
    }

    /**
     * Remove the specified vehicle from storage
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Vehicle $vehicle)
    {
        // Cek apakah kendaraan sedang digunakan (memiliki assignments aktif)
        $activeAssignments = $vehicle->assignments()
            ->whereDate('end_date', '>=', now())
            ->orWhereNull('end_date')
            ->count();
            
        if ($activeAssignments > 0) {
            return redirect()->route('vehicles.index')
                ->with('error', 'Kendaraan tidak dapat dihapus karena sedang digunakan.');
        }
        
        $vehicle->delete();
        
        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil dihapus.');
    }
    
    /**
     * Command to update all vehicle statuses (can be called via scheduler)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAllStatuses(Request $request = null)
    {
        // Jika ada parameter vehicle_id, hanya update kendaraan tersebut
        if ($request && $request->has('vehicle_id')) {
            $vehicle = Vehicle::find($request->vehicle_id);
            if ($vehicle) {
                $this->updateVehicleStatus($vehicle);
                return redirect()->back()->with('success', "Status kendaraan {$vehicle->registration_number} berhasil diperbarui.");
            }
        }
        
        // Jika tidak ada parameter, update semua kendaraan
        $this->updateVehicleStatuses();
        
        return redirect()->route('vehicles.index')
            ->with('success', 'Status semua kendaraan berhasil diperbarui.');
    }
} 