<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GestorRolSeeder extends Seeder
{
    public function run(): void
    {
        $role_gestor = Role::create(['name' => 'gestor']);

        $permission_read_ingrediente = Permission::findByName('read ingrediente');
        $permission_update_ingrediente = Permission::findByName('update ingrediente');
        $permission_create_marquesita = Permission::findByName('create marquesita');
        $permission_read_marquesita = Permission::findByName('read marquesita');
        $permission_update_marquesita = Permission::findByName('update marquesita');
        $permission_delete_marquesita = Permission::findByName('delete marquesita');
        $permission_create_order = Permission::findByName('create order');
        $permission_read_order = Permission::findByName('read order');
        $permission_update_order = Permission::findByName('update order');
        $permission_delete_order = Permission::findByName('delete order');


        $permisisions_almacen = [$permission_create_marquesita, $permission_read_marquesita, $permission_update_marquesita,
            $permission_delete_marquesita, $permission_create_order, $permission_read_order, $permission_update_order,
            $permission_delete_order, $permission_read_ingrediente, $permission_update_ingrediente];

        $role_gestor->syncPermissions($permisisions_almacen);

        Sucursal::create([
            'id' => '1000',
            'nombre' => 'Gestor',
            'direccion' => 'Calle 0',
            'telefono' => '123',
        ]);

        $user = User::create([
            'name' => 'Gestor',
            'sucursal_id' => '1000',
            'email' => 'gestor.ventas',
            'password' => Hash::make('password'),
        ]);
        
        $user->assignRole('gestor');



    
    }
}