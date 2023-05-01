<?php

namespace Database\Seeders;

use App\Models\Rep;
use App\Models\Shop;
use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        Admin::factory()->count(10)->create();
        User::factory()->count(20)->create();
    //    Rep::factory()->count(40)->create();
        Shop::factory()->count(45)->create();
        Customer::factory()->count(100)->create();
    }
}
