<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role_admin = Role::create(['name' => 'admin']);
        $role_sucursal = Role::create(['name' => 'sucursal']);
        $role_trabajador = Role::create(['name' => 'trabajador']);

        $permission_create_role = Permission::create(['name' => 'create role']);
        $permission_read_role = Permission::create(['name' => 'read role']);
        $permission_update_role = Permission::create(['name' => 'update role']);
        $permission_delete_role = Permission::create(['name' => 'delete role']);

        $permission_create_ingrediente = Permission::create(['name' => 'create ingrediente']);
        $permission_read_ingrediente = Permission::create(['name' => 'read ingrediente']);
        $permission_update_ingrediente = Permission::create(['name' => 'update ingrediente']);
        $permission_delete_ingrediente = Permission::create(['name' => 'delete ingrediente']);
        
        $permission_create_bebida = Permission::create(['name' => 'create bebida']);
        $permission_read_bebida = Permission::create(['name' => 'read bebida']);
        $permission_update_bebida = Permission::create(['name' => 'update bebida']);
        $permission_delete_bebida = Permission::create(['name' => 'delete bebida']);

        $permission_create_marquesita = Permission::create(['name' => 'create marquesita']);
        $permission_read_marquesita = Permission::create(['name' => 'read marquesita']);
        $permission_update_marquesita = Permission::create(['name' => 'update marquesita']);
        $permission_delete_marquesita = Permission::create(['name' => 'delete marquesita']);
        
        $permission_create_order = Permission::create(['name' => 'create order']);
        $permission_read_order = Permission::create(['name' => 'read order']);
        $permission_update_order = Permission::create(['name' => 'update order']);
        $permission_delete_order = Permission::create(['name' => 'delete order']);

        $permission_create_sucursal = Permission::create(['name' => 'create sucursal']);
        $permission_read_sucursal = Permission::create(['name' => 'read sucursal']);
        $permission_update_sucursal = Permission::create(['name' => 'update sucursal']);
        $permission_delete_sucursal = Permission::create(['name' => 'delete sucursal']);

        $permissions_admin = [$permission_create_role, $permission_read_role, $permission_update_role,
                $permission_delete_role, $permission_create_ingrediente, $permission_read_ingrediente, 
                $permission_update_ingrediente, $permission_delete_ingrediente, $permission_create_bebida, 
                $permission_read_bebida, $permission_update_bebida, $permission_delete_bebida, 
                $permission_create_marquesita, $permission_read_marquesita, $permission_update_marquesita, 
                $permission_delete_marquesita, $permission_create_order, $permission_read_order, $permission_update_order, 
                $permission_delete_order, $permission_create_sucursal, $permission_read_sucursal, $permission_update_sucursal, 
                $permission_delete_sucursal];

        $permisisions_trabajador = [$permission_create_marquesita, $permission_read_marquesita, $permission_update_marquesita,
                $permission_delete_marquesita, $permission_create_order, $permission_read_order, $permission_update_order,
                $permission_delete_order, $permission_read_ingrediente, $permission_update_ingrediente];


                $role_admin->syncPermissions($permissions_admin);
                $role_sucursal->syncPermissions($permissions_admin);
                $role_trabajador->syncPermissions($permisisions_trabajador);

    
    }
}
