<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            ['department_id' => 1, 'department_name' => 'Manajemen Pusat', 'description' => 'Departemen Manajemen Kantor Pusat'],
            ['department_id' => 2, 'department_name' => 'Operasional Tambang', 'description' => 'Departemen Operasional Tambang'],
            ['department_id' => 3, 'department_name' => 'Logistik', 'description' => 'Departemen Logistik dan Pengadaan'],
            ['department_id' => 4, 'department_name' => 'Transportasi', 'description' => 'Departemen Transportasi dan Armada'],
            ['department_id' => 5, 'department_name' => 'Sumber Daya Manusia', 'description' => 'Departemen SDM'],
        ];

        DB::table('departments')->insert($departments);
    }
} 