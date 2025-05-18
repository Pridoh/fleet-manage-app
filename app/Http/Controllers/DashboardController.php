<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Models\RequestApproval;
use App\Models\VehicleMaintenance;
use App\Models\VehicleUsageLog;
use App\Models\VehicleAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard with statistics and charts
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Statistik dasar
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', 'available')->count();
        $newRequests = VehicleRequest::whereDate('created_at', Carbon::today())->count();
        $pendingApprovals = RequestApproval::where('status', 'pending')->count();
        $totalFuel = VehicleAssignment::whereMonth('updated_at', Carbon::now()->month)
            ->whereNotNull('fuel_used')
            ->sum('fuel_used');

        // Data jadwal maintenance terdekat
        $maintenanceSchedules = VehicleMaintenance::with('vehicle', 'vehicle.vehicleType')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Data permintaan kendaraan terbaru
        $latestRequests = VehicleRequest::with(['requester', 'requester.department'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Data penggunaan kendaraan per hari dalam seminggu
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Mengambil data nyata penggunaan kendaraan dari database
        $weeklyUsageData = VehicleAssignment::select(
                DB::raw('DATE(actual_start_datetime) as date'),
                DB::raw('COUNT(*) as total_assignments')
            )
            ->whereBetween('actual_start_datetime', [$startOfWeek, $endOfWeek])
            ->whereNotNull('actual_start_datetime')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Format untuk grafik mingguan
        $weeklyLabels = [];
        $weeklyData = [];
        
        // Siapkan array dengan 7 hari dalam seminggu
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weeklyLabels[] = $date->translatedFormat('D'); // Nama hari singkat (dalam bahasa Indonesia jika locale diatur)
            $dayData = $weeklyUsageData->firstWhere('date', $date->format('Y-m-d'));
            $weeklyData[] = $dayData ? $dayData->total_assignments : 0;
        }
        
        // Data penggunaan kendaraan per bulan dalam setahun
        $currentYear = Carbon::now()->year;
        
        // Mengambil data nyata penggunaan kendaraan bulanan dari database
        $monthlyUsageData = VehicleAssignment::select(
                DB::raw('MONTH(actual_start_datetime) as month'),
                DB::raw('COUNT(*) as total_assignments')
            )
            ->whereYear('actual_start_datetime', $currentYear)
            ->whereNotNull('actual_start_datetime')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total_assignments', 'month')
            ->toArray();
            
        // Format untuk grafik bulanan
        $monthlyLabels = [];
        $monthlyData = [];
        
        // Siapkan array dengan 12 bulan
        for ($i = 1; $i <= 12; $i++) {
            $date = Carbon::createFromDate($currentYear, $i, 1);
            $monthlyLabels[] = $date->translatedFormat('M'); // Nama bulan singkat (dalam bahasa Indonesia jika locale diatur)
            $monthlyData[] = $monthlyUsageData[$i] ?? 0;
        }
        
        // Data konsumsi BBM per kendaraan (top 5)
        $vehicleFuelData = VehicleAssignment::select(
                'vehicles.vehicle_id',
                'vehicles.registration_number',
                DB::raw('SUM(vehicle_assignments.fuel_used) as total_fuel')
            )
            ->join('vehicles', 'vehicle_assignments.vehicle_id', '=', 'vehicles.vehicle_id')
            ->whereNotNull('vehicle_assignments.fuel_used')
            ->where('vehicle_assignments.fuel_used', '>', 0)
            ->whereYear('vehicle_assignments.updated_at', $currentYear)
            ->groupBy('vehicles.vehicle_id', 'vehicles.registration_number')
            ->orderBy('total_fuel', 'desc')
            ->take(5)
            ->get();
            
        $fuelLabels = $vehicleFuelData->pluck('registration_number')->toArray();
        $fuelData = $vehicleFuelData->pluck('total_fuel')->toArray();

        // Jika tidak ada data, berikan pesan untuk ditampilkan
        if (count($fuelLabels) == 0) {
            $fuelLabels = ['Belum ada data'];
            $fuelData = [0];
        }

        // Jika data penggunaan mingguan kosong, beri pesan
        if (array_sum($weeklyData) == 0) {
            $weeklyLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $weeklyData = [0, 0, 0, 0, 0, 0, 0];
        }

        // Jika data penggunaan bulanan kosong, beri pesan
        if (array_sum($monthlyData) == 0) {
            $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $monthlyData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }

        return view('dashboard', compact(
            'totalVehicles',
            'availableVehicles',
            'newRequests',
            'pendingApprovals',
            'totalFuel',
            'maintenanceSchedules',
            'latestRequests',
            'weeklyLabels',
            'weeklyData',
            'monthlyLabels',
            'monthlyData',
            'fuelLabels',
            'fuelData'
        ));
    }
} 