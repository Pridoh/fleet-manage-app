<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicles = [
            // Kendaraan Penumpang
            [
                'vehicle_id' => 1,
                'registration_number' => 'B 1234 KPN',
                'vehicle_type_id' => 1,
                'brand' => 'Toyota',
                'model' => 'Fortuner',
                'year' => 2022,
                'capacity' => 7,
                'ownership_type' => 'owned',
                'lease_company' => null,
                'lease_start_date' => null,
                'lease_end_date' => null,
                'location_id' => 1,
                'status' => 'available',
                'last_service_date' => '2023-04-15',
                'next_service_date' => '2023-07-15',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'vehicle_id' => 2,
                'registration_number' => 'B 5678 KPN',
                'vehicle_type_id' => 1,
                'brand' => 'Mitsubishi',
                'model' => 'Pajero Sport',
                'year' => 2021,
                'capacity' => 7,
                'ownership_type' => 'owned',
                'lease_company' => null,
                'lease_start_date' => null,
                'lease_end_date' => null,
                'location_id' => 2,
                'status' => 'available',
                'last_service_date' => '2023-05-10',
                'next_service_date' => '2023-08-10',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Bus Karyawan
            [
                'vehicle_id' => 3,
                'registration_number' => 'B 9876 BUS',
                'vehicle_type_id' => 2,
                'brand' => 'Hino',
                'model' => 'RK8',
                'year' => 2020,
                'capacity' => 40,
                'ownership_type' => 'owned',
                'lease_company' => null,
                'lease_start_date' => null,
                'lease_end_date' => null,
                'location_id' => 3,
                'status' => 'available',
                'last_service_date' => '2023-05-20',
                'next_service_date' => '2023-08-20',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Pickup
            [
                'vehicle_id' => 4,
                'registration_number' => 'B 4321 PCK',
                'vehicle_type_id' => 3,
                'brand' => 'Toyota',
                'model' => 'Hilux',
                'year' => 2021,
                'capacity' => 2,
                'ownership_type' => 'owned',
                'lease_company' => null,
                'lease_start_date' => null,
                'lease_end_date' => null,
                'location_id' => 4,
                'status' => 'available',
                'last_service_date' => '2023-04-25',
                'next_service_date' => '2023-07-25',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Truk Barang
            [
                'vehicle_id' => 5,
                'registration_number' => 'B 8765 TRK',
                'vehicle_type_id' => 4,
                'brand' => 'Isuzu',
                'model' => 'Giga',
                'year' => 2019,
                'capacity' => 3500,
                'ownership_type' => 'owned',
                'lease_company' => null,
                'lease_start_date' => null,
                'lease_end_date' => null,
                'location_id' => 5,
                'status' => 'available',
                'last_service_date' => '2023-06-05',
                'next_service_date' => '2023-09-05',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Dump Truck
            [
                'vehicle_id' => 6,
                'registration_number' => 'B 1357 DMP',
                'vehicle_type_id' => 5,
                'brand' => 'Volvo',
                'model' => 'FMX',
                'year' => 2020,
                'capacity' => 8000,
                'ownership_type' => 'owned',
                'lease_company' => null,
                'lease_start_date' => null,
                'lease_end_date' => null,
                'location_id' => 6,
                'status' => 'available',
                'last_service_date' => '2023-04-30',
                'next_service_date' => '2023-07-30',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Kendaraan Operasional
            [
                'vehicle_id' => 7,
                'registration_number' => 'B 2468 OPS',
                'vehicle_type_id' => 6,
                'brand' => 'Toyota',
                'model' => 'Land Cruiser',
                'year' => 2021,
                'capacity' => 5,
                'ownership_type' => 'leased',
                'lease_company' => 'PT Astra Sedaya Finance',
                'lease_start_date' => '2021-01-01',
                'lease_end_date' => '2024-01-01',
                'location_id' => 7,
                'status' => 'available',
                'last_service_date' => '2023-05-15',
                'next_service_date' => '2023-08-15',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Kendaraan Penumpang Sewa
            [
                'vehicle_id' => 8,
                'registration_number' => 'B 3690 LSE',
                'vehicle_type_id' => 1,
                'brand' => 'Honda',
                'model' => 'CR-V',
                'year' => 2022,
                'capacity' => 5,
                'ownership_type' => 'leased',
                'lease_company' => 'PT Tunas Mobilindo',
                'lease_start_date' => '2022-01-01',
                'lease_end_date' => '2025-01-01',
                'location_id' => 8,
                'status' => 'available',
                'last_service_date' => '2023-06-10',
                'next_service_date' => '2023-09-10',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('vehicles')->insert($vehicles);

        // SUV - Kantor Pusat
        Vehicle::create([
            'registration_number' => 'B 1234 ABC',
            'vehicle_type_id' => 1, // SUV
            'brand' => 'Toyota',
            'model' => 'Fortuner',
            'year' => 2022,
            'capacity' => 7,
            'ownership_type' => 'owned',
            'location_id' => 1, // Kantor Pusat
            'status' => 'available',
            'last_service_date' => now()->subMonths(2),
            'next_service_date' => now()->addMonths(1)
        ]);

        // Truk - Tambang
        Vehicle::create([
            'registration_number' => 'B 5678 DEF',
            'vehicle_type_id' => 2, // Truk
            'brand' => 'Hino',
            'model' => 'Dutro',
            'year' => 2021,
            'capacity' => 8000,
            'ownership_type' => 'owned',
            'location_id' => 2, // Tambang
            'status' => 'available',
            'last_service_date' => now()->subMonths(1),
            'next_service_date' => now()->addMonths(2)
        ]);

        // Bus - Kantor Pusat
        Vehicle::create([
            'registration_number' => 'B 9012 GHI',
            'vehicle_type_id' => 4, // Bus
            'brand' => 'Mercedes Benz',
            'model' => 'OF 1521',
            'year' => 2020,
            'capacity' => 40,
            'ownership_type' => 'owned',
            'location_id' => 1, // Kantor Pusat
            'status' => 'available',
            'last_service_date' => now()->subMonths(3),
            'next_service_date' => now()
        ]);
        
        // SUV - Workshop
        Vehicle::create([
            'registration_number' => 'B 3456 JKL',
            'vehicle_type_id' => 1, // SUV
            'brand' => 'Mitsubishi',
            'model' => 'Pajero Sport',
            'year' => 2021,
            'capacity' => 7,
            'ownership_type' => 'leased',
            'lease_company' => 'Astra Rental',
            'lease_start_date' => now()->subYears(1),
            'lease_end_date' => now()->addYears(1),
            'location_id' => 3, // Workshop
            'status' => 'available',
            'last_service_date' => now()->subWeeks(2),
            'next_service_date' => now()->addMonths(3)
        ]);
    }
} 