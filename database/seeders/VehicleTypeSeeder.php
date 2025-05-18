<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicleTypes = [
            [
                'vehicle_type_id' => 1,
                'type_name' => 'Mobil',
                'category' => 'passenger',
                'description' => 'Mobil untuk mengangkut penumpang',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'vehicle_type_id' => 2,
                'type_name' => 'Bus',
                'category' => 'passenger',
                'description' => 'Bus untuk antar-jemput karyawan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'vehicle_type_id' => 3,
                'type_name' => 'Pickup',
                'category' => 'goods',
                'description' => 'Kendaraan pickup untuk angkut barang ringan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'vehicle_type_id' => 4,
                'type_name' => 'Truk',
                'category' => 'goods',
                'description' => 'Truk untuk mengangkut barang dan material',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'vehicle_type_id' => 5,
                'type_name' => 'Dump Truck',
                'category' => 'heavy_equipment',
                'description' => 'Truk besar untuk mengangkut hasil tambang',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'vehicle_type_id' => 6,
                'type_name' => 'Buldozer',
                'category' => 'heavy_equipment',
                'description' => 'Buldozer untuk keperluan operasional di area tambang',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('vehicle_types')->insert($vehicleTypes);
    }
} 