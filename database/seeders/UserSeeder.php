<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'sucursal_id' => '1',   
            'email' => 'admin',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $sucursal = User::create([
            'name' => 'Sucursal 1',
            'sucursal_id' => '1',
            'email' => 'sucursal1',
            'password' => Hash::make('password'),
        ]);
        $sucursal->assignRole('sucursal');

        $sucursal = User::create([
            'name' => 'Sucursal 2',
            'sucursal_id' => '2',
            'email' => 'sucursal2',
            'password' => Hash::make('password'),
        ]);
        $sucursal->assignRole('sucursal');
    
    }
}
