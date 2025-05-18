<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleMaintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateVehicleStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicles:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbarui status kendaraan berdasarkan penggunaan, pemeliharaan, dan masa sewa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembaruan status kendaraan...');
        
        // Get all vehicles
        $vehicles = Vehicle::all();
        $now = Carbon::now();
        $updatedCount = 0;
        
        foreach ($vehicles as $vehicle) {
            $statusChanged = false;
            $oldStatus = $vehicle->status;
            
            // Cek kendaraan sewa yang sudah habis masa sewanya
            if ($vehicle->ownership_type === 'leased' && $vehicle->lease_end_date && $now->greaterThan($vehicle->lease_end_date)) {
                $vehicle->status = 'inactive';
                $statusChanged = true;
                $this->info("Kendaraan {$vehicle->registration_number}: Masa sewa habis, status diubah ke inactive");
            } 
            // Cek kendaraan yang sedang dalam pemeliharaan
            else {
                $activeMaintenance = VehicleMaintenance::where('vehicle_id', $vehicle->vehicle_id)
                    ->where('status', 'in_progress')
                    ->whereDate('start_date', '<=', $now)
                    ->where(function ($query) use ($now) {
                        $query->whereDate('end_date', '>=', $now)
                              ->orWhereNull('end_date');
                    })
                    ->first();
                
                if ($activeMaintenance) {
                    $vehicle->status = 'maintenance';
                    $statusChanged = true;
                    $this->info("Kendaraan {$vehicle->registration_number}: Sedang dalam pemeliharaan, status diubah ke maintenance");
                }
                // Cek kendaraan yang sedang digunakan
                else {
                    $activeAssignment = VehicleAssignment::where('vehicle_id', $vehicle->vehicle_id)
                        ->whereHas('request', function($query) use ($now) {
                            $query->whereIn('status', ['approved', 'in_progress'])
                                ->whereDate('pickup_datetime', '<=', $now)
                                ->whereDate('return_datetime', '>=', $now);
                        })
                        ->first();
                    
                    if ($activeAssignment) {
                        $vehicle->status = 'in_use';
                        $statusChanged = true;
                        $this->info("Kendaraan {$vehicle->registration_number}: Sedang digunakan, status diubah ke in_use");
                    } 
                    // Jika tidak sedang digunakan dan tidak dalam pemeliharaan, set available
                    elseif ($vehicle->status === 'in_use' || $vehicle->status === 'maintenance') {
                        $vehicle->status = 'available';
                        $statusChanged = true;
                        $this->info("Kendaraan {$vehicle->registration_number}: Tidak sedang digunakan, status diubah ke available");
                    }
                }
            }
            
            // Simpan perubahan jika status berubah
            if ($statusChanged && $oldStatus !== $vehicle->status) {
                $vehicle->save();
                $updatedCount++;
                
                // Log perubahan status
                \App\Models\SystemLog::create([
                    'user_id' => 1, // Default ke admin
                    'action' => 'update',
                    'entity_type' => 'vehicle',
                    'entity_id' => $vehicle->vehicle_id,
                    'description' => "Status kendaraan {$vehicle->registration_number} berubah dari {$oldStatus} menjadi {$vehicle->status} (via scheduler)"
                ]);
            }
        }
        
        $this->info("Pembaruan status kendaraan selesai. {$updatedCount} kendaraan diperbarui.");
        
        // Log untuk monitoring
        Log::info("Vehicle status update completed. {$updatedCount} vehicles updated.");
        
        return 0;
    }
}
