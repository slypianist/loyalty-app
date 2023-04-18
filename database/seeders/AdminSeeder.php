<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admins = Admin::create([
            'firstNname' => 'Dayo',
            'lastName' => 'Ajala',
            'email' => 'dayo@gmail.com',
            'dept' => 'Monitoring',
            'password' => Hash::make('password')
        ]);
    }
}
