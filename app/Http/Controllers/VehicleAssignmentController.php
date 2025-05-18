<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleAssignment;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleAssignmentController extends Controller
{
    /**
     * Tampilkan daftar penggunaan kendaraan aktif
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = VehicleAssignment::with(['vehicle', 'driver', 'request', 'request.requester'])
                ->whereIn('status', ['assigned', 'in_progress']);

        // Filter berdasarkan pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('vehicle', function($vehicleQ) use ($search) {
                    $vehicleQ->where('registration_number', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                })
                ->orWhereHas('driver', function($driverQ) use ($search) {
                    $driverQ->where('name', 'like', "%{$search}%");
                });
            });
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan kendaraan
        if ($request->has('vehicle_id') && !empty($request->vehicle_id)) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        $assignments = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Statistik penggunaan kendaraan
        $stats = [
            'total_active' => VehicleAssignment::whereIn('status', ['assigned', 'in_progress'])->count(),
            'total_in_progress' => VehicleAssignment::where('status', 'in_progress')->count(),
            'total_assigned' => VehicleAssignment::where('status', 'assigned')->count(),
        ];
        
        // Data untuk filter dropdown
        $vehicles = Vehicle::orderBy('registration_number')->get();
        
        return view('vehicle-usage.index', compact('assignments', 'vehicles', 'stats'));
    }
    
    /**
     * Tampilkan riwayat penggunaan kendaraan
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function history(Request $request)
    {
        $query = VehicleAssignment::with(['vehicle', 'driver', 'request', 'request.requester'])
                ->whereIn('status', ['completed', 'cancelled']);

        // Filter berdasarkan pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('vehicle', function($vehicleQ) use ($search) {
                    $vehicleQ->where('registration_number', 'like', "%{$search}%");
                });
            });
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('actual_start_datetime', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('actual_end_datetime', '<=', $request->end_date);
        }
        
        $assignments = $query->orderBy('actual_end_datetime', 'desc')->paginate(10);
        
        return view('vehicle-usage.history', compact('assignments'));
    }
    
    /**
     * Tampilkan detail penggunaan kendaraan
     *
     * @param  \App\Models\VehicleAssignment  $assignment
     * @return \Illuminate\View\View
     */
    public function show(VehicleAssignment $assignment)
    {
        $assignment->load(['vehicle', 'driver', 'request', 'request.requester']);
        
        return view('vehicle-usage.show', compact('assignment'));
    }
    
    /**
     * Update status penggunaan kendaraan
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleAssignment  $assignment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, VehicleAssignment $assignment)
    {
        $validated = $request->validate([
            'status' => 'required|in:assigned,in_progress,completed,cancelled',
            'start_odometer' => 'nullable|integer',
            'end_odometer' => 'nullable|integer|min:' . ($assignment->start_odometer ?? 0),
            'fuel_used' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);
        
        DB::beginTransaction();
        
        try {
            $oldStatus = $assignment->status;
            
            // Update assignment status
            $assignment->status = $validated['status'];
            
            if ($validated['status'] === 'in_progress') {
                $assignment->actual_start_datetime = now();
                if (isset($validated['start_odometer'])) {
                    $assignment->start_odometer = $validated['start_odometer'];
                }
                
                // Update status kendaraan
                $vehicle = $assignment->vehicle;
                $vehicle->status = 'in_use';
                $vehicle->save();
                
                // Update status driver jika ada
                if ($assignment->driver) {
                    $driver = $assignment->driver;
                    $driver->status = 'on_duty';
                    $driver->save();
                }
            }
            
            if ($validated['status'] === 'completed') {
                $assignment->actual_end_datetime = now();
                if (isset($validated['end_odometer'])) {
                    $assignment->end_odometer = $validated['end_odometer'];
                }
                if (isset($validated['fuel_used'])) {
                    $assignment->fuel_used = $validated['fuel_used'];
                }
                if (isset($validated['notes'])) {
                    $assignment->notes = $validated['notes'];
                }
                
                // Update vehicle status back to available
                $vehicle = $assignment->vehicle;
                $vehicle->status = 'available';
                $vehicle->save();
                
                // Update status driver jika ada
                if ($assignment->driver) {
                    $driver = $assignment->driver;
                    $driver->status = 'available';
                    $driver->save();
                }
                
                // Update status permintaan
                $vehicleRequest = $assignment->request;
                $vehicleRequest->status = 'completed';
                $vehicleRequest->save();
            }
            
            // Jika status diubah menjadi cancelled (dibatalkan)
            if ($validated['status'] === 'cancelled') {
                $assignment->notes = $validated['notes'] ?? 'Dibatalkan';
                
                // Update status kendaraan
                $vehicle = $assignment->vehicle;
                $vehicle->status = 'available';
                $vehicle->save();
                
                // Update status driver jika ada
                if ($assignment->driver) {
                    $driver = $assignment->driver;
                    $driver->status = 'available';
                    $driver->save();
                }
                
                // Update status permintaan
                $vehicleRequest = $assignment->request;
                $vehicleRequest->status = 'cancelled';
                $vehicleRequest->save();
            }
            
            $assignment->save();
            
            // Log the activity
            \App\Models\SystemLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'entity_type' => 'vehicle_assignment',
                'entity_id' => $assignment->assignment_id,
                'description' => "Status penggunaan kendaraan #{$assignment->assignment_id} diubah dari {$oldStatus} menjadi {$assignment->status}"
            ]);
            
            DB::commit();
            
            return redirect()->route('vehicle-usage.show', $assignment)
                ->with('success', 'Status penggunaan kendaraan berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
