<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DepartmentSeeder::class,
            UserSeeder::class,
            LocationSeeder::class,
            VehicleTypeSeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
            ApproverSeeder::class,
        ]);
    }
}
