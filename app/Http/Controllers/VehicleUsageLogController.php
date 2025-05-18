<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleAssignment;
use App\Models\VehicleUsageLog;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleUsageLogController extends Controller
{
    /**
     * Menampilkan log penggunaan kendaraan berdasarkan ID assignment
     *
     * @param  \App\Models\VehicleAssignment|string  $assignment
     * @return \Illuminate\View\View
     */
    public function index($assignment)
    {
        // Jika parameter adalah 'all', ambil semua log dari semua assignment
        if ($assignment === 'all') {
            $logs = VehicleUsageLog::with(['location', 'logger', 'assignment', 'assignment.vehicle', 'assignment.driver'])
                    ->orderBy('log_datetime', 'desc')
                    ->paginate(20);
            
            return view('vehicle-usage.logs.all', compact('logs'));
        }
        
        // Jika bukan 'all', proses seperti biasa untuk assignment tertentu
        if (!$assignment instanceof VehicleAssignment) {
            $assignment = VehicleAssignment::findOrFail($assignment);
        }
        
        $logs = VehicleUsageLog::with(['location', 'logger'])
                ->where('assignment_id', $assignment->assignment_id)
                ->orderBy('log_datetime', 'desc')
                ->get();
                
        return view('vehicle-usage.logs.index', compact('assignment', 'logs'));
    }
    
    /**
     * Menampilkan form untuk membuat log baru
     *
     * @param  \App\Models\VehicleAssignment  $assignment
     * @return \Illuminate\View\View
     */
    public function create(VehicleAssignment $assignment)
    {
        $locations = Location::orderBy('location_name')->get();
        $logTypes = [
            'departure' => 'Keberangkatan',
            'arrival' => 'Kedatangan',
            'refuel' => 'Pengisian BBM',
            'checkpoint' => 'Titik Pemeriksaan',
            'issue' => 'Masalah/Insiden'
        ];
        
        return view('vehicle-usage.logs.create', compact('assignment', 'locations', 'logTypes'));
    }
    
    /**
     * Menyimpan log penggunaan kendaraan baru
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleAssignment  $assignment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, VehicleAssignment $assignment)
    {
        $validated = $request->validate([
            'log_type' => 'required|in:departure,arrival,refuel,checkpoint,issue',
            'location_id' => 'nullable|exists:locations,location_id',
            'odometer_reading' => 'nullable|integer',
            'fuel_level' => 'nullable|numeric|min:0',
            'fuel_added' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'log_datetime' => 'required|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Buat log baru
            $log = new VehicleUsageLog();
            $log->assignment_id = $assignment->assignment_id;
            $log->log_type = $validated['log_type'];
            $log->location_id = $validated['location_id'] ?? null;
            $log->odometer_reading = $validated['odometer_reading'] ?? null;
            $log->fuel_level = $validated['fuel_level'] ?? null;
            $log->fuel_added = $validated['fuel_added'] ?? null;
            $log->fuel_cost = $validated['fuel_cost'] ?? null;
            $log->notes = $validated['notes'] ?? null;
            $log->logged_by = Auth::id();
            $log->log_datetime = Carbon::parse($validated['log_datetime']);
            $log->save();
            
            // Update odometer kendaraan jika ini adalah log tipe departure atau arrival
            if (in_array($validated['log_type'], ['departure', 'arrival']) && !empty($validated['odometer_reading'])) {
                if ($validated['log_type'] == 'departure' && $assignment->status == 'assigned') {
                    $assignment->start_odometer = $validated['odometer_reading'];
                    $assignment->status = 'in_progress';
                    $assignment->actual_start_datetime = Carbon::parse($validated['log_datetime']);
                    
                    // Update vehicle status to in_use
                    $vehicle = $assignment->vehicle;
                    $vehicle->status = 'in_use';
                    $vehicle->save();
                    
                    // Update driver status to on_duty if any
                    if ($assignment->driver) {
                        $driver = $assignment->driver;
                        $driver->status = 'on_duty';
                        $driver->save();
                    }
                } elseif ($validated['log_type'] == 'arrival' && $assignment->status == 'in_progress') {
                    $assignment->end_odometer = $validated['odometer_reading'];
                }
                $assignment->save();
            }
            
            // Update total BBM jika ini adalah log tipe refuel
            if ($validated['log_type'] == 'refuel' && !empty($validated['fuel_added'])) {
                $assignment->fuel_used = ($assignment->fuel_used ?? 0) + $validated['fuel_added'];
                $assignment->save();
            }
            
            DB::commit();
            
            return redirect()->route('vehicle-usage.logs.index', $assignment)
                ->with('success', 'Log penggunaan kendaraan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Menampilkan detail log
     *
     * @param  \App\Models\VehicleAssignment  $assignment
     * @param  \App\Models\VehicleUsageLog  $log
     * @return \Illuminate\View\View
     */
    public function show(VehicleAssignment $assignment, VehicleUsageLog $log)
    {
        if ($log->assignment_id != $assignment->assignment_id) {
            abort(403, 'Log ini tidak terkait dengan assignment yang dipilih');
        }
        
        return view('vehicle-usage.logs.show', compact('assignment', 'log'));
    }
    
    /**
     * Menampilkan form untuk mengedit log
     *
     * @param  \App\Models\VehicleAssignment  $assignment
     * @param  \App\Models\VehicleUsageLog  $log
     * @return \Illuminate\View\View
     */
    public function edit(VehicleAssignment $assignment, VehicleUsageLog $log)
    {
        if ($log->assignment_id != $assignment->assignment_id) {
            abort(403, 'Log ini tidak terkait dengan assignment yang dipilih');
        }
        
        $locations = Location::orderBy('location_name')->get();
        $logTypes = [
            'departure' => 'Keberangkatan',
            'arrival' => 'Kedatangan',
            'refuel' => 'Pengisian BBM',
            'checkpoint' => 'Titik Pemeriksaan',
            'issue' => 'Masalah/Insiden'
        ];
        
        return view('vehicle-usage.logs.edit', compact('assignment', 'log', 'locations', 'logTypes'));
    }
    
    /**
     * Memperbarui log yang ada
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleAssignment  $assignment
     * @param  \App\Models\VehicleUsageLog  $log
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, VehicleAssignment $assignment, VehicleUsageLog $log)
    {
        if ($log->assignment_id != $assignment->assignment_id) {
            abort(403, 'Log ini tidak terkait dengan assignment yang dipilih');
        }
        
        $validated = $request->validate([
            'log_type' => 'required|in:departure,arrival,refuel,checkpoint,issue',
            'location_id' => 'nullable|exists:locations,location_id',
            'odometer_reading' => 'nullable|integer',
            'fuel_level' => 'nullable|numeric|min:0',
            'fuel_added' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'log_datetime' => 'required|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update log
            $log->log_type = $validated['log_type'];
            $log->location_id = $validated['location_id'] ?? null;
            $log->odometer_reading = $validated['odometer_reading'] ?? null;
            $log->fuel_level = $validated['fuel_level'] ?? null;
            
            // Simpan nilai fuel_added sebelumnya untuk kalkulasi selisih
            $oldFuelAdded = $log->fuel_added;
            $log->fuel_added = $validated['fuel_added'] ?? null;
            
            $log->fuel_cost = $validated['fuel_cost'] ?? null;
            $log->notes = $validated['notes'] ?? null;
            $log->log_datetime = Carbon::parse($validated['log_datetime']);
            $log->save();
            
            // Update total BBM jika ini adalah log tipe refuel
            if ($validated['log_type'] == 'refuel' && isset($validated['fuel_added'])) {
                // Hitung selisih BBM yang diubah
                $fuelDifference = ($validated['fuel_added'] ?? 0) - ($oldFuelAdded ?? 0);
                if ($fuelDifference != 0) {
                    $assignment->fuel_used = ($assignment->fuel_used ?? 0) + $fuelDifference;
                    $assignment->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('vehicle-usage.logs.index', $assignment)
                ->with('success', 'Log penggunaan kendaraan berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Menghapus log penggunaan kendaraan
     *
     * @param  \App\Models\VehicleAssignment  $assignment
     * @param  \App\Models\VehicleUsageLog  $log
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(VehicleAssignment $assignment, VehicleUsageLog $log)
    {
        if ($log->assignment_id != $assignment->assignment_id) {
            abort(403, 'Log ini tidak terkait dengan assignment yang dipilih');
        }
        
        DB::beginTransaction();
        
        try {
            // Update total BBM jika ini adalah log tipe refuel
            if ($log->log_type == 'refuel' && $log->fuel_added > 0) {
                $assignment->fuel_used = ($assignment->fuel_used ?? 0) - $log->fuel_added;
                $assignment->save();
            }
            
            $log->delete();
            
            DB::commit();
            
            return redirect()->route('vehicle-usage.logs.index', $assignment)
                ->with('success', 'Log penggunaan kendaraan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 