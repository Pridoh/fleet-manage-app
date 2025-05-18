<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $drivers = [
            [
                'driver_id' => 1,
                'name' => 'Budi Santoso',
                'license_number' => 'SIM-A-12345678',
                'license_type' => 'A',
                'license_expiry' => '2025-05-15',
                'phone' => '081234567001',
                'address' => 'Jl. Pramuka No. 45, Jakarta',
                'date_of_birth' => '1985-05-15',
                'join_date' => '2024-01-01',
                'status' => 'available',
                'location_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'driver_id' => 2,
                'name' => 'Ahmad Hidayat',
                'license_number' => 'SIM-B1-87654321',
                'license_type' => 'B1',
                'license_expiry' => '2025-05-15',
                'phone' => '081234567002',
                'address' => 'Jl. Imam Bonjol No. 78, Makassar',
                'date_of_birth' => '1988-08-20',
                'join_date' => '2024-01-01',
                'status' => 'available',
                'location_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'driver_id' => 3,
                'name' => 'Hendra Wijaya',
                'license_number' => 'SIM-A-23456789',
                'license_type' => 'A',
                'license_expiry' => '2025-05-15',
                'phone' => '081234567003',
                'address' => 'Jl. Diponegoro No. 123, Sorowako',
                'date_of_birth' => '1990-10-10',
                'join_date' => '2024-01-01',
                'status' => 'available',
                'location_id' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'driver_id' => 4,
                'name' => 'Joko Trianto',
                'license_number' => 'SIM-B1-34567890',
                'license_type' => 'B1',
                'license_expiry' => '2025-05-15',
                'phone' => '081234567004',
                'address' => 'Jl. Soedirman No. 56, Morowali',
                'date_of_birth' => '1987-12-05',
                'join_date' => '2024-01-01',
                'status' => 'available',
                'location_id' => 6,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'driver_id' => 5,
                'name' => 'Rudi Hartono',
                'license_number' => 'SIM-A-45678901',
                'license_type' => 'A',
                'license_expiry' => '2025-05-15',
                'phone' => '081234567005',
                'address' => 'Jl. Pahlawan No. 34, Konawe',
                'date_of_birth' => '1992-03-25',
                'join_date' => '2024-01-01',
                'status' => 'available',
                'location_id' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('drivers')->insert($drivers);
    }
} 