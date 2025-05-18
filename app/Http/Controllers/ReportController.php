<?php

namespace App\Http\Controllers;

use App\Models\VehicleRequest;
use App\Models\Vehicle;
use App\Models\Department;
use App\Models\VehicleMaintenance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VehicleRequestsExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman laporan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $reportType = $request->get('report_type', 'requests');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $vehicles = Vehicle::all();
        $departments = Department::all();
        
        $reportData = null;
        $chartData = [];
        
        if ($reportType === 'requests') {
            // Laporan pemesanan kendaraan yang sudah diproses kedua level approver (completed atau rejected)
            $query = VehicleRequest::with(['requester', 'requester.department', 'pickupLocation', 'destinationLocation', 'assignment', 'assignment.vehicle'])
                ->whereIn('status', ['completed', 'rejected']);  // Menampilkan yang sudah completed atau rejected
                
            // Filter berdasarkan departemen
            if ($request->has('department_id') && $request->department_id) {
                $query->whereHas('requester.department', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
            
            // Filter berdasarkan kendaraan
            if ($request->has('vehicle_id') && $request->vehicle_id) {
                $query->whereHas('assignment', function($q) use ($request) {
                    $q->where('vehicle_id', $request->vehicle_id);
                });
            }
            
            // Filter berdasarkan status (completed/rejected)
            if ($request->has('status') && $request->status && in_array($request->status, ['completed', 'rejected'])) {
                $query->where('status', $request->status);
            }
            
            // Filter berdasarkan tanggal
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('pickup_datetime', '>=', $request->start_date);
            }
            
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('pickup_datetime', '<=', $request->end_date);
            }
            
            $reportData = $query->orderBy('pickup_datetime', 'desc')->paginate(10);
            
            // Prepare chart data - menghitung permintaan per hari berdasarkan status
            $startDateObj = Carbon::parse($startDate);
            $endDateObj = Carbon::parse($endDate);
            
            // Membuat array tanggal sebagai keys
            $dates = [];
            $current = $startDateObj->copy();
            while ($current <= $endDateObj) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
            
            // Data untuk status "completed" (disetujui)
            $approvedData = VehicleRequest::where('status', 'completed')
                ->select(DB::raw('DATE(pickup_datetime) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();
            
            // Data untuk status "rejected" (ditolak)
            $rejectedData = VehicleRequest::where('status', 'rejected')
                ->select(DB::raw('DATE(pickup_datetime) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();
            
            $chartData = [
                'dates' => $dates,
                'approved' => array_map(function($date) use ($approvedData) {
                    return $approvedData[$date] ?? 0;
                }, $dates),
                'rejected' => array_map(function($date) use ($rejectedData) {
                    return $rejectedData[$date] ?? 0;
                }, $dates),
            ];
        } elseif ($reportType === 'fuel') {
            // Laporan konsumsi BBM
            $query = DB::table('vehicle_assignments')
                ->select(
                    'vehicle_assignments.assignment_id',
                    'vehicle_assignments.actual_end_datetime as date',
                    'vehicles.registration_number',
                    'vehicles.brand',
                    'vehicles.model',
                    'drivers.name as driver_name',
                    'vehicle_assignments.fuel_used',
                    'vehicle_assignments.end_odometer',
                    'vehicle_assignments.start_odometer'
                )
                ->join('vehicles', 'vehicle_assignments.vehicle_id', '=', 'vehicles.vehicle_id')
                ->leftJoin('drivers', 'vehicle_assignments.driver_id', '=', 'drivers.driver_id')
                ->whereNotNull('vehicle_assignments.fuel_used')
                ->where('vehicle_assignments.fuel_used', '>', 0)
                ->where('vehicle_assignments.status', '=', 'completed');

            // Filter berdasarkan kendaraan
            if ($request->has('vehicle_id') && $request->vehicle_id) {
                $query->where('vehicle_assignments.vehicle_id', $request->vehicle_id);
            }
            
            // Filter berdasarkan tanggal
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('vehicle_assignments.actual_end_datetime', '>=', $request->start_date);
            }
            
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('vehicle_assignments.actual_end_datetime', '<=', $request->end_date);
            }
            
            $reportData = $query->orderBy('vehicle_assignments.actual_end_datetime', 'desc')->paginate(10);
            
            // Prepare chart data - total BBM per kendaraan
            $fuelPerVehicle = DB::table('vehicle_assignments')
                ->select(
                    'vehicles.registration_number',
                    DB::raw('SUM(vehicle_assignments.fuel_used) as total_fuel')
                )
                ->join('vehicles', 'vehicle_assignments.vehicle_id', '=', 'vehicles.vehicle_id')
                ->whereNotNull('vehicle_assignments.fuel_used')
                ->where('vehicle_assignments.fuel_used', '>', 0)
                ->where('vehicle_assignments.status', '=', 'completed')
                ->whereDate('vehicle_assignments.actual_end_datetime', '>=', $startDate)
                ->whereDate('vehicle_assignments.actual_end_datetime', '<=', $endDate)
                ->groupBy('vehicles.registration_number')
                ->orderBy('total_fuel', 'desc')
                ->take(6)
                ->get();
                
            $chartData = [
                'labels' => $fuelPerVehicle->pluck('registration_number')->toArray(),
                'data' => $fuelPerVehicle->pluck('total_fuel')->toArray()
            ];
        } elseif ($reportType === 'maintenance') {
            // Laporan maintenance
            $query = VehicleMaintenance::with(['vehicle'])
                ->select(
                    'vehicle_maintenance.*',
                    'vehicles.registration_number',
                    'vehicles.brand',
                    'vehicles.model'
                )
                ->join('vehicles', 'vehicle_maintenance.vehicle_id', '=', 'vehicles.vehicle_id');
                
            // Filter berdasarkan kendaraan
            if ($request->has('vehicle_id') && $request->vehicle_id) {
                $query->where('vehicle_maintenance.vehicle_id', $request->vehicle_id);
            }
            
            // Filter berdasarkan jenis maintenance
            if ($request->has('maintenance_type') && $request->maintenance_type) {
                $query->where('vehicle_maintenance.maintenance_type', $request->maintenance_type);
            }
            
            // Filter berdasarkan tanggal
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('vehicle_maintenance.start_date', '>=', $request->start_date);
            }
            
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('vehicle_maintenance.end_date', '<=', $request->end_date);
            }
            
            $reportData = $query->orderBy('vehicle_maintenance.start_date', 'desc')->paginate(10);
            
            // Prepare chart data - biaya maintenance per kendaraan
            $maintenanceCostPerVehicle = DB::table('vehicle_maintenance')
                ->select(
                    'vehicles.registration_number',
                    DB::raw('SUM(vehicle_maintenance.cost) as total_cost')
                )
                ->join('vehicles', 'vehicle_maintenance.vehicle_id', '=', 'vehicles.vehicle_id')
                ->whereNotNull('vehicle_maintenance.cost')
                ->whereDate('vehicle_maintenance.start_date', '>=', $startDate)
                ->whereDate('vehicle_maintenance.end_date', '<=', $endDate)
                ->groupBy('vehicles.registration_number')
                ->orderBy('total_cost', 'desc')
                ->take(6)
                ->get();
                
            $chartData = [
                'labels' => $maintenanceCostPerVehicle->pluck('registration_number')->toArray(),
                'data' => $maintenanceCostPerVehicle->pluck('total_cost')->toArray()
            ];
        }
        
        return view('reports.index', compact(
            'reportType', 
            'reportData', 
            'chartData', 
            'vehicles', 
            'departments', 
            'startDate', 
            'endDate'
        ));
    }
    
    /**
     * Export laporan ke CSV (kompatibel dengan Excel).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export(Request $request)
    {
        $reportType = $request->get('report_type', 'requests');
        $fileName = 'laporan-' . $reportType . '-' . date('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($request, $reportType) {
            // Buka output sebagai "php://output"
            $file = fopen('php://output', 'w');
            
            // Tentukan encoding untuk CSV (BOM untuk Excel)
            // fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if ($reportType === 'requests') {
                // Export data pemesanan kendaraan
                $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
                $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
                
                // Header kolom
                fputcsv($file, [
                    'ID Permintaan', 'Pemohon', 'Departemen', 'Lokasi Asal', 'Lokasi Tujuan', 
                    'Tanggal Mulai', 'Tanggal Selesai', 'Status', 'Kendaraan', 'Pengemudi'
                ]);
                
                // Data
                $query = VehicleRequest::with(['requester', 'requester.department', 'pickupLocation', 'destinationLocation', 'assignment', 'assignment.vehicle'])
                    ->whereIn('status', ['completed', 'rejected']);
                
                // Filter yang sama seperti di index
                if ($request->has('department_id') && $request->department_id) {
                    $query->whereHas('requester.department', function($q) use ($request) {
                        $q->where('department_id', $request->department_id);
                    });
                }
                
                if ($request->has('vehicle_id') && $request->vehicle_id) {
                    $query->whereHas('assignment', function($q) use ($request) {
                        $q->where('vehicle_id', $request->vehicle_id);
                    });
                }
                
                if ($request->has('status') && $request->status && in_array($request->status, ['completed', 'rejected'])) {
                    $query->where('status', $request->status);
                }
                
                if ($request->has('start_date') && $request->start_date) {
                    $query->whereDate('pickup_datetime', '>=', $request->start_date);
                }
                
                if ($request->has('end_date') && $request->end_date) {
                    $query->whereDate('pickup_datetime', '<=', $request->end_date);
                }
                
                $data = $query->orderBy('pickup_datetime', 'desc')->get();
                
                foreach ($data as $item) {
                    $statusLabel = $item->status === 'completed' ? 'Disetujui' : 'Ditolak';
                    
                    fputcsv($file, [
                        'REQ-' . str_pad($item->request_id, 5, '0', STR_PAD_LEFT),
                        $item->requester->name ?? 'User',
                        $item->requester->department->department_name ?? '-',
                        $item->pickupLocation->location_name ?? 'Asal',
                        $item->destinationLocation->location_name ?? 'Tujuan',
                        isset($item->pickup_datetime) ? Carbon::parse($item->pickup_datetime)->format('d/m/Y H:i') : '-',
                        isset($item->return_datetime) ? Carbon::parse($item->return_datetime)->format('d/m/Y H:i') : '-',
                        $statusLabel,
                        $item->assignment->vehicle->registration_number ?? '-',
                        $item->assignment->driver->name ?? '-'
                    ]);
                }
            } elseif ($reportType === 'fuel') {
                // Export data konsumsi BBM
                $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
                $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
                
                // Header kolom
                fputcsv($file, [
                    'Kendaraan', 'Driver', 'Tanggal', 'BBM (Liter)', 
                    'Odometer Awal (km)', 'Odometer Akhir (km)', 
                    'Jarak Tempuh (km)', 'Efisiensi (km/L)'
                ]);
                
                // Data
                $query = DB::table('vehicle_assignments')
                    ->select(
                        'vehicle_assignments.assignment_id',
                        'vehicle_assignments.actual_end_datetime as date',
                        'vehicles.registration_number',
                        'vehicles.brand',
                        'vehicles.model',
                        'drivers.name as driver_name',
                        'vehicle_assignments.fuel_used',
                        'vehicle_assignments.end_odometer',
                        'vehicle_assignments.start_odometer',
                        DB::raw('(vehicle_assignments.end_odometer - vehicle_assignments.start_odometer) as distance'),
                        DB::raw('CASE WHEN vehicle_assignments.fuel_used > 0 THEN (vehicle_assignments.end_odometer - vehicle_assignments.start_odometer) / vehicle_assignments.fuel_used ELSE NULL END as efficiency')
                    )
                    ->join('vehicles', 'vehicle_assignments.vehicle_id', '=', 'vehicles.vehicle_id')
                    ->leftJoin('drivers', 'vehicle_assignments.driver_id', '=', 'drivers.driver_id')
                    ->whereNotNull('vehicle_assignments.fuel_used')
                    ->where('vehicle_assignments.fuel_used', '>', 0)
                    ->where('vehicle_assignments.status', '=', 'completed');
    
                // Filter
                if ($request->has('vehicle_id') && $request->vehicle_id) {
                    $query->where('vehicle_assignments.vehicle_id', $request->vehicle_id);
                }
                
                if ($request->has('start_date') && $request->start_date) {
                    $query->whereDate('vehicle_assignments.actual_end_datetime', '>=', $request->start_date);
                }
                
                if ($request->has('end_date') && $request->end_date) {
                    $query->whereDate('vehicle_assignments.actual_end_datetime', '<=', $request->end_date);
                }
                
                $data = $query->orderBy('vehicle_assignments.actual_end_datetime', 'desc')->get();
                
                foreach ($data as $item) {
                    fputcsv($file, [
                        $item->registration_number . ' - ' . $item->brand . ' ' . $item->model,
                        $item->driver_name ?? '-',
                        Carbon::parse($item->date)->format('d/m/Y'),
                        number_format($item->fuel_used, 1),
                        number_format($item->start_odometer),
                        number_format($item->end_odometer),
                        number_format($item->distance),
                        $item->efficiency ? number_format($item->efficiency, 1) : '-'
                    ]);
                }
            } elseif ($reportType === 'maintenance') {
                // Export data maintenance
                $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
                $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
                
                // Header kolom
                fputcsv($file, [
                    'Kendaraan', 'Jenis Maintenance', 'Tanggal Mulai', 
                    'Tanggal Selesai', 'Odometer (km)', 'Deskripsi', 
                    'Biaya (Rp)', 'Status'
                ]);
                
                // Mapping status dan jenis maintenance
                $statusMap = [
                    'scheduled' => 'Dijadwalkan',
                    'in_progress' => 'Dalam Proses',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan'
                ];
                
                $typeMap = [
                    'routine' => 'Service Rutin',
                    'repair' => 'Perbaikan',
                    'inspection' => 'Inspeksi',
                    'emergency' => 'Darurat'
                ];
                
                // Data
                $query = VehicleMaintenance::with(['vehicle'])
                    ->select(
                        'vehicle_maintenance.*',
                        'vehicles.registration_number',
                        'vehicles.brand',
                        'vehicles.model'
                    )
                    ->join('vehicles', 'vehicle_maintenance.vehicle_id', '=', 'vehicles.vehicle_id');
                    
                // Filter
                if ($request->has('vehicle_id') && $request->vehicle_id) {
                    $query->where('vehicle_maintenance.vehicle_id', $request->vehicle_id);
                }
                
                if ($request->has('maintenance_type') && $request->maintenance_type) {
                    $query->where('vehicle_maintenance.maintenance_type', $request->maintenance_type);
                }
                
                if ($request->has('start_date') && $request->start_date) {
                    $query->whereDate('vehicle_maintenance.start_date', '>=', $request->start_date);
                }
                
                if ($request->has('end_date') && $request->end_date) {
                    $query->whereDate('vehicle_maintenance.end_date', '<=', $request->end_date);
                }
                
                $data = $query->orderBy('vehicle_maintenance.start_date', 'desc')->get();
                
                foreach ($data as $item) {
                    fputcsv($file, [
                        $item->registration_number . ' - ' . $item->brand . ' ' . $item->model,
                        $typeMap[$item->maintenance_type] ?? $item->maintenance_type,
                        Carbon::parse($item->start_date)->format('d/m/Y'),
                        $item->end_date ? Carbon::parse($item->end_date)->format('d/m/Y') : '-',
                        $item->odometer_reading ? number_format($item->odometer_reading) : '-',
                        $item->description,
                        $item->cost ? 'Rp ' . number_format($item->cost) : '-',
                        $statusMap[$item->status] ?? $item->status
                    ]);
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
} 