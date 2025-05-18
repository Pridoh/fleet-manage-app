<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ApproverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Buat beberapa user dengan role approver
        
        // Approver 1
        User::create([
            'username' => 'budi_approve',
            'password' => Hash::make('password'),
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567891',
            'role' => 'approver',
            'department_id' => 1, // Manajemen Pusat
            'position' => 'Kepala Departemen',
            'is_active' => true
        ]);
        
        // Approver 2
        User::create([
            'username' => 'dewi_approve',
            'password' => Hash::make('password'),
            'name' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'phone' => '081234567892',
            'role' => 'approver',
            'department_id' => 2, // Operasional Tambang
            'position' => 'Manajer',
            'is_active' => true
        ]);
        
        // Approver 3
        User::create([
            'username' => 'anton_approve',
            'password' => Hash::make('password'),
            'name' => 'Anton Wijaya',
            'email' => 'anton@example.com',
            'phone' => '081234567893',
            'role' => 'approver', 
            'department_id' => 4, // Transportasi
            'position' => 'Supervisor',
            'is_active' => true
        ]);
    }
} 