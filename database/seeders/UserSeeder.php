<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            // Admin
            [
                'user_id' => 1,
                'username' => 'admin',
                'password' => Hash::make('password'),
                'name' => 'Admin Pool',
                'email' => 'admin@example.com',
                'phone' => '081234567890',
                'role' => 'admin',
                'department_id' => 1, // Manajemen Pusat
                'position' => 'Fleet Administrator',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Approver Level 1
            [
                'user_id' => 2,
                'username' => 'approver1',
                'password' => Hash::make('password'),
                'name' => 'Kepala Departemen Operasional',
                'email' => 'approver1@example.com',
                'phone' => '081234567891',
                'role' => 'approver',
                'department_id' => 2, // Operasional Tambang
                'position' => 'Kepala Departemen',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Approver Level 2
            [
                'user_id' => 3,
                'username' => 'approver2',
                'password' => Hash::make('password'),
                'name' => 'Manajer Transportasi',
                'email' => 'approver2@example.com',
                'phone' => '081234567892',
                'role' => 'approver',
                'department_id' => 4, // Transportasi
                'position' => 'Manajer',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
} 