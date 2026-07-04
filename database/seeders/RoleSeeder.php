<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage all resources',
            'view products',
            'create products',
            'view inventory transactions',
            'create inventory transactions',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $manager = Role::findOrCreate('Manager');
        $warehouseStaff = Role::findOrCreate('Warehouse Staff');

        $manager->syncPermissions($permissions);
        $warehouseStaff->syncPermissions([
            'view products',
            'create products',
            'view inventory transactions',
            'create inventory transactions',
        ]);

        User::where('email', 'admin@erp.com')->first()?->assignRole($manager);
    }
}
