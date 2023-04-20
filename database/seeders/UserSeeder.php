<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::create([
            'firstName' => 'Sylvester',
            'lastName' => 'Umole',
            'email' => 'sly.umole@gmail.com',
            'address' => '29B Itafaji, Ikoyi',
            'phoneNum' => '08034567890',
            'password' => Hash::make('password')
        ]);
    }
}
