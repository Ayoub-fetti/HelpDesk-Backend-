<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Ticket permissions
        Permission::create(['name' => 'view tickets']);
        Permission::create(['name' => 'create tickets']);
        Permission::create(['name' => 'edit tickets']);
        Permission::create(['name' => 'delete tickets']);
        Permission::create(['name' => 'assign tickets']);
        Permission::create(['name' => 'change tickets']);
        Permission::create(['name' => 'resolve tickets']);
        Permission::create(['name' => 'close tickets']);
        Permission::create(['name' => 'reopen tickets']);
        
        // Comment permissions 
        Permission::create(['name' => 'view comments']);
        Permission::create(['name' => 'add comments']);
        
        // Time tracking permissions 
        Permission::create(['name' => 'view time tracking']);
        Permission::create(['name' => 'track time']);

        // create roles and assign permissions

        $finalUserRole = Role::create(['name' => 'final_user']);
        $finalUserRole->givePermissionTo([
            'view tickets',
            'create tickets',
            'view comments',
            'add comments',
        ]);

        $technicianRole = Role::create((['name' => 'technician']));
        $technicianRole->givePermissionTo([
            'view tickets',
            'edit tickets',
            'view comments',
            'add comments',
            'change tickets',
            'resolve tickets',
            'view time tracking',
            'track time',   
        ]);

        $supervisorRole = Role::create(['name' => 'supervisor']);
        $supervisorRole->givePermissionTo([
            'view tickets',
            'create tickets',
            'edit tickets',
            'assign tickets',
            'change tickets',
            'resolve tickets',
            'close tickets',
            'view comments',
            'add comments',
            'view time tracking',
            'track time',
        ]);
        
        $adminRole = Role::create(['name' => 'administrator']);
        $adminRole->givePermissionTo(Permission::all());


    }
}
