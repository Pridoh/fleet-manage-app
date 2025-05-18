<?php

namespace App\Http\Controllers;

use App\Models\RequestApproval;
use App\Models\VehicleRequest;
use App\Models\SystemLog;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\VehicleAssignment;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Cek apakah user adalah admin atau approver
        if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('approver')) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses untuk halaman persetujuan.');
        }

        $query = RequestApproval::with(['request', 'request.requester']);

        // Filter by tab (pending or history)
        if ($request->tab == 'history') {
            $query->whereIn('status', ['approved', 'rejected']);
            
            // Filter by status in history view
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'pending');
        }

        // Filter by search in both views
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('request', function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhere('pickup_location_id', 'like', "%{$search}%")
                  ->orWhere('destination_location_id', 'like', "%{$search}%")
                  ->orWhereHas('requester', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Only show approvals assigned to current user (unless admin)
        if (!Auth::user()->isAdmin()) {
            $query->where('approver_id', Auth::id());
        }

        $pendingApprovals = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('approvals.index', compact('pendingApprovals'));
    }

    /**
     * Display the specified approval.
     *
     * @param  \App\Models\RequestApproval  $approval
     * @return \Illuminate\View\View
     */
    public function show(RequestApproval $approval)
    {
        // Check if user has permission to view
        if (!Auth::user()->isAdmin() && Auth::id() !== $approval->approver_id) {
            return redirect()->route('approvals.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat persetujuan ini.');
        }

        $approval->load([
            'request', 
            'request.requester', 
            'request.requester.department', 
            'request.vehicleType',
            'request.assignment',
            'request.assignment.vehicle',
            'request.assignment.driver'
        ]);

        // Get all approvals for this request to show the chain
        $approvalChain = RequestApproval::with('approver')
            ->where('request_id', $approval->request_id)
            ->orderBy('approval_level')
            ->get();

        return view('approvals.show', compact('approval', 'approvalChain'));
    }

    /**
     * Approve a request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestApproval  $approval
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, RequestApproval $approval)
    {
        // Validasi bahwa user adalah approver
        if (!Auth::user()->hasRole('approver')) {
            return redirect()->route('approvals.index')
                ->with('error', 'Anda tidak memiliki wewenang untuk menyetujui permintaan. Hanya approver yang dapat melakukan persetujuan.');
        }

        // Validasi bahwa user yang login adalah approver yang ditunjuk
        if (Auth::id() != $approval->approver_id) {
            return redirect()->route('approvals.index')
                ->with('error', 'Anda tidak memiliki wewenang untuk menyetujui permintaan ini.');
        }

        // Validasi bahwa approval masih pending
        if ($approval->status != 'pending') {
            return redirect()->route('approvals.index')
                ->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        
        try {
            // Update status approval menjadi approved
            $approval->status = 'approved';
            $approval->approval_datetime = now();
            $approval->comments = $request->notes;
            $approval->save();

            // Cek apakah ini approval level 2 (final approval)
            $vehicleRequest = $approval->request;
            
            if ($approval->approval_level == 2) {
                // Cek apakah approval level 1 sudah approved
                $level1Approval = RequestApproval::where('request_id', $approval->request_id)
                    ->where('approval_level', 1)
                    ->first();
                
                if ($level1Approval && $level1Approval->status == 'approved') {
                    // Jika kedua level sudah approved, update status request menjadi approved
                    $vehicleRequest->status = 'approved';
                    $vehicleRequest->save();
                    
                    // Update status kendaraan menjadi in_use jika tanggal pickup sudah tiba
                    $assignment = VehicleAssignment::where('request_id', $vehicleRequest->request_id)->first();
                    if ($assignment) {
                        $vehicle = Vehicle::find($assignment->vehicle_id);
                        
                        // Jika tanggal pickup sudah tiba atau hari ini, ubah status kendaraan menjadi in_use
                        if ($vehicle && $vehicle->status === 'available' && 
                            Carbon::parse($vehicleRequest->pickup_datetime)->startOfDay()->lte(Carbon::now()->startOfDay())) {
                            $vehicle->status = 'in_use';
                            $vehicle->save();
                            
                            // Log perubahan status kendaraan
                            \App\Models\SystemLog::create([
                                'user_id' => Auth::id(),
                                'action' => 'update',
                                'entity_type' => 'vehicle',
                                'entity_id' => $vehicle->vehicle_id,
                                'description' => "Status kendaraan {$vehicle->registration_number} berubah menjadi in_use karena permintaan disetujui"
                            ]);
                        }
                    }
                    
                    // Kirim notifikasi ke requester
                    \App\Models\Notification::success(
                        $vehicleRequest->requester_id,
                        'Permintaan Kendaraan Disetujui',
                        "Permintaan kendaraan Anda telah disetujui dan siap digunakan sesuai jadwal.",
                        'vehicle_request',
                        $vehicleRequest->request_id
                    );
                    
                    // Kirim notifikasi ke driver jika ada
                    $assignment = $vehicleRequest->assignment;
                    if ($assignment && $assignment->driver_id) {
                        \App\Models\Notification::info(
                            $assignment->driver_id,
                            'Tugas Mengemudi Baru',
                            "Anda telah ditugaskan untuk mengemudi pada " . Carbon::parse($vehicleRequest->pickup_datetime)->format('d M Y H:i'),
                            'vehicle_request',
                            $vehicleRequest->request_id
                        );
                    }
                }
            } else {
                // Jika ini approval level 1, kirim notifikasi ke approver level 2
                $level2Approval = RequestApproval::where('request_id', $approval->request_id)
                    ->where('approval_level', 2)
                    ->first();
                
                if ($level2Approval) {
                    \App\Models\Notification::info(
                        $level2Approval->approver_id,
                        'Permintaan Persetujuan Level 2',
                        "Permintaan kendaraan dari {$vehicleRequest->requester->name} telah disetujui oleh approver level 1 dan membutuhkan persetujuan Anda.",
                        'approval',
                        $level2Approval->approval_id
                    );
                }
            }
            
            // Create system log
            \App\Models\SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'approve',
                'entity_type' => 'request_approval',
                'entity_id' => $approval->approval_id,
                'description' => "Menyetujui permintaan kendaraan #{$approval->request_id} (Level {$approval->approval_level})"
            ]);

            // Update all vehicle statuses to ensure they are current
            app(VehicleController::class)->updateAllStatuses();

            DB::commit();
            
            return redirect()->route('approvals.index')
                ->with('success', 'Permintaan berhasil disetujui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Permintaan gagal disetujui: ' . $e->getMessage());
        }
    }

    /**
     * Reject the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestApproval  $approval
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, RequestApproval $approval)
    {
        // Validasi bahwa user adalah approver
        if (!Auth::user()->hasRole('approver')) {
            return redirect()->route('approvals.index')
                ->with('error', 'Anda tidak memiliki wewenang untuk menolak permintaan. Hanya approver yang dapat melakukan penolakan.');
        }

        // Check if user has permission to reject
        if (!Auth::user()->isAdmin() && Auth::id() !== $approval->approver_id) {
            return redirect()->route('approvals.index')
                ->with('error', 'Anda tidak memiliki akses untuk menolak permintaan ini.');
        }

        // Check if approval is still pending
        if ($approval->status !== 'pending') {
            return redirect()->route('approvals.index')
                ->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Update approval status
            $approval->status = 'rejected';
            $approval->approval_datetime = now();
            $approval->comments = $request->rejection_reason;
            $approval->save();

            // Update request status to rejected regardless of approval level
            $vehicleRequest = $approval->request;
            $vehicleRequest->status = 'rejected';
            $vehicleRequest->save();

            // Log the rejection
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'reject',
                'entity_type' => 'request_approval',
                'entity_id' => $approval->approval_id,
                'description' => 'Rejected vehicle request #' . $approval->request_id . ' at level ' . $approval->approval_level,
            ]);

            // Notify requester
            Notification::create([
                'user_id' => $vehicleRequest->requester_id,
                'title' => 'Permintaan Kendaraan Ditolak',
                'message' => 'Permintaan kendaraan Anda dengan tujuan ' . $vehicleRequest->destinationLocation->location_name . ' ditolak dengan alasan: ' . $request->rejection_reason,
                'link' => route('vehicle-requests.show', $vehicleRequest->request_id),
                'is_read' => false,
            ]);

            DB::commit();

            return redirect()->route('approvals.index')
                ->with('success', 'Permintaan berhasil ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses penolakan: ' . $e->getMessage());
        }
    }
} 