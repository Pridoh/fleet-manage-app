<?php

namespace App\Http\Controllers;

use App\Models\VehicleRequest;
use App\Models\RequestApproval;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Location;
use App\Models\SystemLog;
use App\Models\Driver;
use App\Models\VehicleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VehicleRequestsExport;

class VehicleRequestController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('can:access-vehicle-requests');
    }

    /**
     * Display a listing of vehicle requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = VehicleRequest::with(['requester', 'vehicleType', 'approvals']);

        // Filter by search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                    ->orWhere('from_location', 'like', "%{$search}%")
                    ->orWhere('to_location', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('pickup_datetime', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('return_datetime', '<=', $request->end_date);
        }

        // For non-admin users, only show their own requests
        if (!Auth::user()->isAdmin()) {
            $query->where('requester_id', Auth::id());
        }

        $vehicleRequests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('vehicle-requests.index', compact('vehicleRequests'));
    }

    /**
     * Show the form for creating a new vehicle request.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $locations = Location::all();
        $vehicleTypes = VehicleType::all();
        $approvers = User::where('role', 'approver')->get();
        $availableVehicles = Vehicle::where('status', 'available')->get();
        $drivers = Driver::where('status', 'available')->get();

        return view('vehicle-requests.create', compact('locations', 'vehicleTypes', 'approvers', 'availableVehicles', 'drivers'));
    }

    /**
     * Store a newly created vehicle request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_location_id' => 'required|exists:locations,location_id',
            'to_location_id' => 'required|exists:locations,location_id|different:from_location_id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'vehicle_type_id' => 'required|exists:vehicle_types,vehicle_type_id',
            'vehicle_id' => 'required|exists:vehicles,vehicle_id',
            'driver_id' => 'required|exists:drivers,driver_id',
            'num_passengers' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
            'first_approver_id' => 'required|exists:users,user_id',
            'second_approver_id' => 'required|exists:users,user_id|different:first_approver_id',
        ]);

        DB::beginTransaction();
        
        try {
            // Get location names
            $fromLocation = Location::find($request->from_location_id)->location_name;
            $toLocation = Location::find($request->to_location_id)->location_name;
            
            // Create vehicle request
            $vehicleRequest = new VehicleRequest();
            $vehicleRequest->requester_id = Auth::id();
            $vehicleRequest->pickup_location_id = $request->from_location_id;
            $vehicleRequest->destination_location_id = $request->to_location_id;
            $vehicleRequest->pickup_datetime = $request->start_date;
            $vehicleRequest->return_datetime = $request->end_date;
            $vehicleRequest->vehicle_type_id = $request->vehicle_type_id;
            $vehicleRequest->passenger_count = $request->num_passengers;
            $vehicleRequest->purpose = $request->purpose;
            $vehicleRequest->notes = $request->notes;
            $vehicleRequest->priority = 'medium'; // Default priority
            $vehicleRequest->status = 'pending';
            $vehicleRequest->save();

            // Create vehicle assignment
            $assignment = new VehicleAssignment();
            $assignment->request_id = $vehicleRequest->request_id;
            $assignment->vehicle_id = $request->vehicle_id;
            $assignment->driver_id = $request->driver_id;
            $assignment->assigned_by = Auth::id();
            $assignment->status = 'assigned';
            $assignment->save();

            // Create first level approval
            $firstApproval = new RequestApproval();
            $firstApproval->request_id = $vehicleRequest->request_id;
            $firstApproval->approver_id = $request->first_approver_id;
            $firstApproval->approval_level = 1;
            $firstApproval->status = 'pending';
            $firstApproval->save();
            
            // Create second level approval
            $secondApproval = new RequestApproval();
            $secondApproval->request_id = $vehicleRequest->request_id;
            $secondApproval->approver_id = $request->second_approver_id;
            $secondApproval->approval_level = 2;
            $secondApproval->status = 'pending';
            $secondApproval->save();

            // Create system log
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'entity_type' => 'vehicle_request',
                'entity_id' => $vehicleRequest->request_id,
                'description' => 'Created new vehicle request to ' . $toLocation . ' with 2 level approvals',
            ]);

            // Kirim notifikasi ke approver level 1
            $fromLocationName = Location::find($request->from_location_id)->location_name;
            $toLocationName = Location::find($request->to_location_id)->location_name;
            $requesterName = Auth::user()->name;
            
            // Notifikasi untuk approver level 1
            \App\Models\Notification::info(
                $request->first_approver_id,
                'Permintaan Persetujuan Baru',
                "Permintaan kendaraan baru dari $requesterName menuju $toLocationName membutuhkan persetujuan Anda (Level 1)",
                'vehicle_request',
                $vehicleRequest->request_id
            );
            
            // Notifikasi untuk approver level 2
            \App\Models\Notification::info(
                $request->second_approver_id,
                'Permintaan Persetujuan Baru',
                "Permintaan kendaraan baru dari $requesterName menuju $toLocationName memerlukan persetujuan Anda (Level 2) setelah disetujui oleh approver level 1",
                'vehicle_request',
                $vehicleRequest->request_id
            );

            DB::commit();

            return redirect()->route('vehicle-requests.index')
                ->with('success', 'Permintaan kendaraan berhasil dibuat. Menunggu persetujuan dari 2 level approver.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Permintaan kendaraan gagal dibuat: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified vehicle request.
     *
     * @param  \App\Models\VehicleRequest  $vehicleRequest
     * @return \Illuminate\View\View
     */
    public function show(VehicleRequest $vehicleRequest)
    {
        // Check if user has permission to view
        if (!Auth::user()->isAdmin() && Auth::id() !== $vehicleRequest->requester_id) {
            return redirect()->route('vehicle-requests.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat permintaan ini.');
        }

        $vehicleRequest->load([
            'requester', 
            'requester.department', 
            'vehicleType', 
            'approvals', 
            'approvals.approver',
            'assignment',
            'assignment.vehicle',
            'assignment.driver'
        ]);

        return view('vehicle-requests.show', compact('vehicleRequest'));
    }

    /**
     * Show the form for editing the specified vehicle request.
     *
     * @param  \App\Models\VehicleRequest  $vehicleRequest
     * @return \Illuminate\View\View
     */
    public function edit(VehicleRequest $vehicleRequest)
    {
        // Check if user has permission to edit
        if (!Auth::user()->isAdmin() && Auth::id() !== $vehicleRequest->requester_id) {
            return redirect()->route('vehicle-requests.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit permintaan ini.');
        }

        // Check if request is editable (only pending status can be edited)
        if ($vehicleRequest->status !== 'pending') {
            return redirect()->route('vehicle-requests.index')
                ->with('error', 'Permintaan yang sudah diproses tidak dapat diedit.');
        }

        // Load related data
        $vehicleRequest->load(['approvals']);

        $locations = Location::all();
        $vehicleTypes = VehicleType::all();
        $approvers = User::role('approver')->get();

        return view('vehicle-requests.edit', compact('vehicleRequest', 'locations', 'vehicleTypes', 'approvers'));
    }

    /**
     * Update the specified vehicle request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleRequest  $vehicleRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, VehicleRequest $vehicleRequest)
    {
        // Check if user has permission to edit
        if (!Auth::user()->isAdmin() && Auth::id() !== $vehicleRequest->requester_id) {
            return redirect()->route('vehicle-requests.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit permintaan ini.');
        }

        // Check if request is editable (only pending status can be edited)
        if ($vehicleRequest->status !== 'pending') {
            return redirect()->route('vehicle-requests.index')
                ->with('error', 'Permintaan yang sudah diproses tidak dapat diedit.');
        }

        $validated = $request->validate([
            'from_location_id' => 'required|exists:locations,location_id',
            'to_location_id' => 'required|exists:locations,location_id|different:from_location_id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'vehicle_type_id' => 'required|exists:vehicle_types,vehicle_type_id',
            'num_passengers' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
            'first_approver_id' => 'required|exists:users,user_id',
            'second_approver_id' => 'required|exists:users,user_id|different:first_approver_id',
        ]);

        DB::beginTransaction();
        
        try {
            // Get location names
            $fromLocation = Location::find($request->from_location_id)->location_name;
            $toLocation = Location::find($request->to_location_id)->location_name;
            
            // Update vehicle request
            $vehicleRequest->pickup_location_id = $request->from_location_id;
            $vehicleRequest->destination_location_id = $request->to_location_id;
            $vehicleRequest->pickup_datetime = $request->start_date;
            $vehicleRequest->return_datetime = $request->end_date;
            $vehicleRequest->vehicle_type_id = $request->vehicle_type_id;
            $vehicleRequest->passenger_count = $request->num_passengers;
            $vehicleRequest->purpose = $request->purpose;
            $vehicleRequest->notes = $request->notes;
            $vehicleRequest->save();

            // Update level 1 approval if approver changed
            $firstApproval = RequestApproval::where('request_id', $vehicleRequest->request_id)
                ->where('approval_level', 1)
                ->first();
                
            if ($firstApproval && $firstApproval->approver_id != $request->first_approver_id) {
                $firstApproval->approver_id = $request->first_approver_id;
                $firstApproval->status = 'pending'; // Reset status to pending if approver changed
                $firstApproval->save();
                
                // Kirim notifikasi ke approver level 1 baru
                \App\Models\Notification::info(
                    $request->first_approver_id,
                    'Permintaan Persetujuan Baru',
                    "Anda telah ditunjuk sebagai approver level 1 untuk permintaan kendaraan dari " . $vehicleRequest->requester->name . " menuju " . $toLocation,
                    'vehicle_request',
                    $vehicleRequest->request_id
                );
            } else if (!$firstApproval) {
                // Create if not exists
                $firstApproval = new RequestApproval();
                $firstApproval->request_id = $vehicleRequest->request_id;
                $firstApproval->approver_id = $request->first_approver_id;
                $firstApproval->approval_level = 1;
                $firstApproval->status = 'pending';
                $firstApproval->save();
            }

            // Update level 2 approval if approver changed
            $secondApproval = RequestApproval::where('request_id', $vehicleRequest->request_id)
                ->where('approval_level', 2)
                ->first();
                
            if ($secondApproval && $secondApproval->approver_id != $request->second_approver_id) {
                $secondApproval->approver_id = $request->second_approver_id;
                $secondApproval->status = 'pending'; // Reset status to pending if approver changed
                $secondApproval->save();
                
                // Kirim notifikasi ke approver level 2 baru
                \App\Models\Notification::info(
                    $request->second_approver_id,
                    'Permintaan Persetujuan Baru',
                    "Anda telah ditunjuk sebagai approver level 2 untuk permintaan kendaraan dari " . $vehicleRequest->requester->name . " menuju " . $toLocation,
                    'vehicle_request',
                    $vehicleRequest->request_id
                );
            } else if (!$secondApproval) {
                // Create if not exists
                $secondApproval = new RequestApproval();
                $secondApproval->request_id = $vehicleRequest->request_id;
                $secondApproval->approver_id = $request->second_approver_id;
                $secondApproval->approval_level = 2;
                $secondApproval->status = 'pending';
                $secondApproval->save();
            }

            // Create system log
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'entity_type' => 'vehicle_request',
                'entity_id' => $vehicleRequest->request_id,
                'description' => 'Updated vehicle request to ' . $toLocation,
            ]);

            DB::commit();

            return redirect()->route('vehicle-requests.show', $vehicleRequest)
                ->with('success', 'Permintaan kendaraan berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Permintaan kendaraan gagal diperbarui: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified vehicle request from storage.
     *
     * @param  \App\Models\VehicleRequest  $vehicleRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(VehicleRequest $vehicleRequest)
    {
        // Check if user has permission to delete
        if (!Auth::user()->isAdmin() && Auth::id() !== $vehicleRequest->requester_id) {
            return redirect()->route('vehicle-requests.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus permintaan ini.');
        }

        // Check if request is deletable (only pending status can be deleted)
        if ($vehicleRequest->status !== 'pending') {
            return redirect()->route('vehicle-requests.index')
                ->with('error', 'Permintaan yang sudah diproses tidak dapat dihapus.');
        }

        DB::beginTransaction();
        
        try {
            // Delete approvals first
            RequestApproval::where('request_id', $vehicleRequest->request_id)->delete();
            
            // Delete vehicle request
            $vehicleRequest->delete();

            // Create system log
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'entity_type' => 'vehicle_request',
                'entity_id' => $vehicleRequest->request_id,
                'description' => 'Deleted vehicle request to ' . $vehicleRequest->to_location,
            ]);

            DB::commit();

            return redirect()->route('vehicle-requests.index')
                ->with('success', 'Permintaan kendaraan berhasil dihapus.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Permintaan kendaraan gagal dihapus: ' . $e->getMessage());
        }
    }

    /**
     * Get available vehicles and drivers based on requested date range
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $vehicleTypeId = $request->vehicle_type_id;

        // Validasi input
        if (!$startDate || !$endDate) {
            return response()->json([
                'error' => 'Tanggal mulai dan selesai harus diisi'
            ], 400);
        }

        // Update vehicle statuses before checking availability
        app(VehicleController::class)->updateAllStatuses();

        // Query dasar untuk kendaraan yang tersedia
        $vehiclesQuery = Vehicle::where('status', 'available');
        
        // Filter berdasarkan tipe kendaraan jika ada
        if ($vehicleTypeId) {
            $vehiclesQuery->where('vehicle_type_id', $vehicleTypeId);
        }
        
        // Dapatkan semua kendaraan yang tersedia
        $allVehicles = $vehiclesQuery->get();
        
        // Dapatkan semua pengemudi yang tersedia
        $allDrivers = Driver::where('status', 'available')->get();

        // Dapatkan kendaraan yang sedang digunakan pada periode yang diminta
        $busyVehicleIds = VehicleAssignment::whereHas('request', function($query) use ($startDate, $endDate) {
            $query->where(function($q) use ($startDate, $endDate) {
                // Cek tumpang tindih jadwal
                $q->where(function($dateQuery) use ($startDate, $endDate) {
                    // Kasus 1: Pemesanan baru berada di dalam rentang pemesanan yang ada
                    $dateQuery->where('pickup_datetime', '<=', $startDate)
                              ->where('return_datetime', '>=', $endDate);
                })->orWhere(function($dateQuery) use ($startDate, $endDate) {
                    // Kasus 2: Awal pemesanan baru tumpang tindih dengan pemesanan yang ada
                    $dateQuery->where('pickup_datetime', '<=', $startDate)
                              ->where('return_datetime', '>=', $startDate);
                })->orWhere(function($dateQuery) use ($startDate, $endDate) {
                    // Kasus 3: Akhir pemesanan baru tumpang tindih dengan pemesanan yang ada
                    $dateQuery->where('pickup_datetime', '<=', $endDate)
                              ->where('return_datetime', '>=', $endDate);
                })->orWhere(function($dateQuery) use ($startDate, $endDate) {
                    // Kasus 4: Pemesanan baru melingkupi pemesanan yang ada
                    $dateQuery->where('pickup_datetime', '>=', $startDate)
                              ->where('return_datetime', '<=', $endDate);
                });
            })->whereIn('status', ['pending', 'approved', 'in_progress']);
        })->pluck('vehicle_id')->toArray();
        
        // Dapatkan pengemudi yang sedang bertugas pada periode yang diminta
        $busyDriverIds = VehicleAssignment::whereHas('request', function($query) use ($startDate, $endDate) {
            $query->where(function($q) use ($startDate, $endDate) {
                // Cek tumpang tindih jadwal (sama seperti untuk kendaraan)
                $q->where(function($dateQuery) use ($startDate, $endDate) {
                    $dateQuery->where('pickup_datetime', '<=', $startDate)
                              ->where('return_datetime', '>=', $endDate);
                })->orWhere(function($dateQuery) use ($startDate, $endDate) {
                    $dateQuery->where('pickup_datetime', '<=', $startDate)
                              ->where('return_datetime', '>=', $startDate);
                })->orWhere(function($dateQuery) use ($startDate, $endDate) {
                    $dateQuery->where('pickup_datetime', '<=', $endDate)
                              ->where('return_datetime', '>=', $endDate);
                })->orWhere(function($dateQuery) use ($startDate, $endDate) {
                    $dateQuery->where('pickup_datetime', '>=', $startDate)
                              ->where('return_datetime', '<=', $endDate);
                });
            })->whereIn('status', ['pending', 'approved', 'in_progress']);
        })->pluck('driver_id')->toArray();
        
        // Filter kendaraan dan pengemudi yang tersedia
        $availableVehicles = $allVehicles->filter(function($vehicle) use ($busyVehicleIds) {
            return !in_array($vehicle->vehicle_id, $busyVehicleIds);
        })->values();
        
        $availableDrivers = $allDrivers->filter(function($driver) use ($busyDriverIds) {
            return !in_array($driver->driver_id, $busyDriverIds);
        })->values();
        
        // Siapkan data yang akan dikembalikan dengan informasi lengkap
        $vehiclesData = $availableVehicles->map(function($vehicle) {
            return [
                'id' => $vehicle->vehicle_id,
                'registration_number' => $vehicle->registration_number,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'type' => $vehicle->vehicleType ? $vehicle->vehicleType->type_name : 'Tidak diketahui',
                'capacity' => $vehicle->capacity
            ];
        });
        
        $driversData = $availableDrivers->map(function($driver) {
            return [
                'id' => $driver->driver_id,
                'name' => $driver->name,
                'license_number' => $driver->license_number,
                'phone' => $driver->phone_number
            ];
        });
        
        return response()->json([
            'vehicles' => $vehiclesData,
            'drivers' => $driversData
        ]);
    }

    /**
     * Export vehicle requests to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        // Pass the request to the export class to handle filters
        $export = new VehicleRequestsExport($request);
        return $export->export();
    }
} 