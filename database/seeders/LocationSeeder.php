<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $locations = [
            ['location_id' => 1, 'location_name' => 'Kantor Pusat', 'location_type' => 'headquarters', 'address' => 'Jl. Jenderal Sudirman No. 123, Jakarta', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12920', 'created_at' => now(), 'updated_at' => now()],
            ['location_id' => 2, 'location_name' => 'Kantor Cabang', 'location_type' => 'branch', 'address' => 'Jl. Ahmad Yani No. 45, Makassar', 'city' => 'Makassar', 'province' => 'Sulawesi Selatan', 'postal_code' => '90111', 'created_at' => now(), 'updated_at' => now()],
            ['location_id' => 3, 'location_name' => 'Tambang Nikel Site A', 'location_type' => 'mine', 'address' => 'Pulau Obi, Halmahera Selatan, Maluku Utara', 'city' => 'Halmahera Selatan', 'province' => 'Maluku Utara', 'postal_code' => '97761', 'created_at' => now(), 'updated_at' => now()],
            ['location_id' => 4, 'location_name' => 'Tambang Nikel Site B', 'location_type' => 'mine', 'address' => 'Sorowako, Luwu Timur, Sulawesi Selatan', 'city' => 'Sorowako', 'province' => 'Sulawesi Selatan', 'postal_code' => '92171', 'created_at' => now(), 'updated_at' => now()],
            ['location_id' => 5, 'location_name' => 'Tambang Nikel Site C', 'location_type' => 'mine', 'address' => 'Konawe, Sulawesi Tenggara', 'city' => 'Konawe', 'province' => 'Sulawesi Tenggara', 'postal_code' => '93411', 'created_at' => now(), 'updated_at' => now()],
            ['location_id' => 6, 'location_name' => 'Tambang Nikel Site D', 'location_type' => 'mine', 'address' => 'Morowali, Sulawesi Tengah', 'city' => 'Morowali', 'province' => 'Sulawesi Tengah', 'postal_code' => '94911', 'created_at' => now(), 'updated_at' => now()],
            ['location_id' => 7, 'location_name' => 'Tambang Nikel Site E', 'location_type' => 'mine', 'address' => 'Pomalaa, Kolaka, Sulawesi Tenggara', 'city' => 'Pomalaa', 'province' => 'Sulawesi Tenggara', 'postal_code' => '94711', 'created_at' => now(), 'updated_at' => now()],
            ['location_id' => 8, 'location_name' => 'Tambang Nikel Site F', 'location_type' => 'mine', 'address' => 'Weda, Halmahera Tengah, Maluku Utara', 'city' => 'Weda', 'province' => 'Halmahera Tengah', 'postal_code' => '97771', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('locations')->insert($locations);
    }
} 