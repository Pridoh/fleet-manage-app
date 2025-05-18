<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleMaintenance;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the maintenance records.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $maintenances = VehicleMaintenance::with('vehicle')
            ->orderBy('start_date', 'desc')
            ->paginate(10);
            
        return view('maintenance.index', compact('maintenances'));
    }

    /**
     * Show the form for creating a new maintenance record.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $vehicles = Vehicle::all();
        $maintenanceTypes = ['routine', 'repair', 'inspection', 'emergency'];
        
        return view('maintenance.create', compact('vehicles', 'maintenanceTypes'));
    }

    /**
     * Store a newly created maintenance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,vehicle_id',
            'maintenance_type' => 'required|in:routine,repair,inspection,emergency',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'odometer_reading' => 'nullable|integer',
            'cost' => 'nullable|numeric',
            'performed_by' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        $maintenance = new VehicleMaintenance($validated);
        $maintenance->status = 'scheduled';
        $maintenance->save();
        
        return redirect()->route('maintenance.index')
            ->with('success', 'Jadwal maintenance berhasil dibuat');
    }

    /**
     * Display the specified maintenance record.
     *
     * @param  \App\Models\VehicleMaintenance  $maintenance
     * @return \Illuminate\View\View
     */
    public function show(VehicleMaintenance $maintenance)
    {
        return view('maintenance.show', compact('maintenance'));
    }

    /**
     * Show the form for editing the specified maintenance record.
     *
     * @param  \App\Models\VehicleMaintenance  $maintenance
     * @return \Illuminate\View\View
     */
    public function edit(VehicleMaintenance $maintenance)
    {
        $vehicles = Vehicle::all();
        $maintenanceTypes = ['routine', 'repair', 'inspection', 'emergency'];
        
        return view('maintenance.edit', compact('maintenance', 'vehicles', 'maintenanceTypes'));
    }

    /**
     * Update the specified maintenance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleMaintenance  $maintenance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, VehicleMaintenance $maintenance)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,vehicle_id',
            'maintenance_type' => 'required|in:routine,repair,inspection,emergency',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'odometer_reading' => 'nullable|integer',
            'cost' => 'nullable|numeric',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'performed_by' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        $maintenance->update($validated);
        
        return redirect()->route('maintenance.index')
            ->with('success', 'Jadwal maintenance berhasil diperbarui');
    }

    /**
     * Remove the specified maintenance record from storage.
     *
     * @param  \App\Models\VehicleMaintenance  $maintenance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(VehicleMaintenance $maintenance)
    {
        $maintenance->delete();
        
        return redirect()->route('maintenance.index')
            ->with('success', 'Jadwal maintenance berhasil dihapus');
    }

    /**
     * Update maintenance status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleMaintenance  $maintenance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, VehicleMaintenance $maintenance)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            $oldStatus = $maintenance->status;
            $vehicle = $maintenance->vehicle;
            
            // Update maintenance record
            $maintenance->status = $validated['status'];
            
            if ($validated['status'] === 'completed' && !$maintenance->end_date) {
                $maintenance->end_date = $validated['end_date'] ?? now();
            }
            
            if ($validated['notes']) {
                $maintenance->notes = $validated['notes'];
            }
            
            $maintenance->save();
            
            // Update vehicle status based on maintenance status
            if ($validated['status'] === 'in_progress') {
                // Jika pemeliharaan dimulai, ubah status kendaraan menjadi maintenance
                $vehicle->status = 'maintenance';
                $vehicle->save();
                
                // Log perubahan status kendaraan
                \App\Models\SystemLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'entity_type' => 'vehicle',
                    'entity_id' => $vehicle->vehicle_id,
                    'description' => "Status kendaraan {$vehicle->registration_number} berubah menjadi maintenance karena pemeliharaan dimulai"
                ]);
            } 
            elseif ($validated['status'] === 'completed' && $oldStatus === 'in_progress') {
                // Jika pemeliharaan selesai, cek apakah kendaraan sedang digunakan
                $activeAssignment = $vehicle->assignments()
                    ->whereHas('request', function($query) {
                        $query->whereIn('status', ['approved', 'in_progress'])
                            ->whereDate('pickup_datetime', '<=', now())
                            ->whereDate('return_datetime', '>=', now());
                    })
                    ->first();
                
                if ($activeAssignment) {
                    // Jika kendaraan sedang digunakan, ubah status menjadi in_use
                    $vehicle->status = 'in_use';
                } else {
                    // Jika tidak sedang digunakan, ubah status menjadi available
                    $vehicle->status = 'available';
                }
                
                $vehicle->save();
                
                // Log perubahan status kendaraan
                \App\Models\SystemLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'entity_type' => 'vehicle',
                    'entity_id' => $vehicle->vehicle_id,
                    'description' => "Status kendaraan {$vehicle->registration_number} berubah menjadi {$vehicle->status} karena pemeliharaan selesai"
                ]);
            }
            
            // Create system log for maintenance status update
            \App\Models\SystemLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'entity_type' => 'maintenance',
                'entity_id' => $maintenance->maintenance_id,
                'description' => "Status pemeliharaan untuk kendaraan {$vehicle->registration_number} berubah dari {$oldStatus} menjadi {$maintenance->status}"
            ]);
            
            DB::commit();
            
            return redirect()->route('maintenance.show', $maintenance)
                ->with('success', 'Status pemeliharaan berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Status pemeliharaan gagal diperbarui: ' . $e->getMessage());
        }
    }
} 