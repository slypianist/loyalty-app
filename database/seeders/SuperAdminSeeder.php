<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $admin = Admin::create([
            'firstName' => 'Oludayo',
            'lastName' => 'Ajala',
            'email' => 'ajalaoludayo@gmail.com',
            'dept' => 'IT/Monitoring',
            'image' => 'default.png',
            'password' => Hash::make('password')
        ]);

        $admin->assignRole('Super Admin');
    }
}
