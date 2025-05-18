<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Location;

class DriverController extends Controller
{
    /**
     * Display a listing of the drivers
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $drivers = Driver::with('location')
            ->orderBy('name')
            ->paginate(10);
            
        return view('drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new driver
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $locations = Location::all();
        $statuses = ['available', 'on_duty', 'day_off', 'sick', 'inactive'];
        
        return view('drivers.create', compact('locations', 'statuses'));
    }

    /**
     * Store a newly created driver in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'license_number' => 'required|string|max:50|unique:drivers',
            'license_type' => 'required|string|max:20',
            'license_expiry' => 'required|date|after:today',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'required|date|before:today',
            'join_date' => 'required|date',
            'status' => 'required|in:available,on_duty,day_off,sick,inactive',
            'location_id' => 'nullable|exists:locations,location_id',
        ]);
        
        // Pastikan status adalah salah satu dari nilai yang diizinkan
        if (!in_array($validated['status'], ['available', 'on_duty', 'day_off', 'sick', 'inactive'])) {
            $validated['status'] = 'available'; // Default ke available jika tidak valid
        }
        
        // Map birth_date ke date_of_birth sesuai dengan kolom di database
        if (isset($validated['birth_date'])) {
            $validated['date_of_birth'] = $validated['birth_date'];
            unset($validated['birth_date']);
        }
        
        Driver::create($validated);
        
        return redirect()->route('drivers.index')
            ->with('success', 'Driver berhasil ditambahkan.');
    }

    /**
     * Display the specified driver
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\View\View
     */
    public function show(Driver $driver)
    {
        $driver->load('location');
        
        // Ambil data assignment driver (jika ada)
        $assignments = $driver->assignments()
            ->with([
                'vehicle', 
                'request',
                'request.requester'
            ])
            ->orderBy('actual_start_datetime', 'desc')
            ->take(5)
            ->get();
        
        return view('drivers.show', compact('driver', 'assignments'));
    }

    /**
     * Show the form for editing the specified driver
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\View\View
     */
    public function edit(Driver $driver)
    {
        $locations = Location::all();
        $statuses = ['available', 'on_duty', 'day_off', 'sick', 'inactive'];
        
        return view('drivers.edit', compact('driver', 'locations', 'statuses'));
    }

    /**
     * Update the specified driver in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'license_number' => 'required|string|max:50|unique:drivers,license_number,' . $driver->driver_id . ',driver_id',
            'license_type' => 'required|string|max:20',
            'license_expiry' => 'required|date|after:today',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'required|date|before:today',
            'join_date' => 'required|date',
            'status' => 'required|in:available,on_duty,day_off,sick,inactive',
            'location_id' => 'nullable|exists:locations,location_id',
        ]);
        
        // Pastikan status adalah salah satu dari nilai yang diizinkan
        if (!in_array($validated['status'], ['available', 'on_duty', 'day_off', 'sick', 'inactive'])) {
            $validated['status'] = 'available'; // Default ke available jika tidak valid
        }
        
        // Map birth_date ke date_of_birth sesuai dengan kolom di database
        if (isset($validated['birth_date'])) {
            $validated['date_of_birth'] = $validated['birth_date'];
            unset($validated['birth_date']);
        }
        
        $driver->update($validated);
        
        return redirect()->route('drivers.index')
            ->with('success', 'Data driver berhasil diperbarui.');
    }

    /**
     * Remove the specified driver from storage
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Driver $driver)
    {
        // Cek apakah driver sedang bertugas
        $activeAssignments = $driver->assignments()
            ->whereDate('end_date', '>=', now())
            ->orWhereNull('end_date')
            ->count();
            
        if ($activeAssignments > 0) {
            return redirect()->route('drivers.index')
                ->with('error', 'Driver tidak dapat dihapus karena sedang bertugas.');
        }
        
        $driver->delete();
        
        return redirect()->route('drivers.index')
            ->with('success', 'Driver berhasil dihapus.');
    }
} 