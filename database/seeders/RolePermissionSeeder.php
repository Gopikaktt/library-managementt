<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions=[
            'view books',
            'create books',
            'edit books',
            'delete books',
            'issue books',
            'return books',
            'manager users',
            'view borrowing history',
        ];

        foreach($permissions as $permission){
            Permission::firstOrCreate([
                'name'=>$permission,
                'guard_name'=>'web',
            ]);
        }
        $admin=Role::firstOrCreate([
            'name'=>'Admin',
            'guard_name'=>'web',
         ]);

         $librarian=Role::firstOrCreate([
            'name'=>'Librarian',
            'guard_name'=>'web',
         ]);

         $member=Role::firstOrCreate([
            'name'=>'Member',
            'guard_name'=>'web',
         ]);

         $admin->syncPermissions(Permission::all());
         $librarian->syncPermissions([
            'view books',
            'create books',
            'edit books',
            'issue books',
            'return books',
            'view borrowing history',
         ]);

         $member->syncPermissions([
            'view books',
            'view borrowing history',
         ]);

    }
}
