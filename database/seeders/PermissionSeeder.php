<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            //Customers Permission
            'list-customers',
            'create-customer',
            'view-customer',
            'update-customer',
            'delete-customer',

            //Reps permission
            'list-reps',
            'create-rep',
            'view-rep',
            'update-rep',
            'delete-rep',

            // Partners Permission
            'list-partners',
            'create-partner',
            'view-partner',
            'update-partner',
            'delete-partner',

            // Admins Permission
            'list-admins',
            'create-admin',
            'view-admin',
            'update-admin',
            'delete-admin',

            // Center Permission
            'list-centers',
            'create-center',
            'view-center',
            'update-center',
            'delete-center',
            'assign-center-partner',
            'unassign-center-partner',
            'assign-center-rep',
            'unassign-center-rep',

            //Role
            'list-roles',
            'create-role',
            'view-role',
            'update-role',
            'delete-role',

            //Others
            'list-loyalty-rules',
            'create-loyalty-rule',
            'set-loyalty-rule',
            'delete-loyalty-rule',
            'reset-app',
           // 'set-claims-status',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['guard_name'=>'admin', 'name'=>$permission]);
        }
    }
}
