<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     *
     */
    public function run()
    {
        $shop = Shop::create([
            'name' => 'Shop A',
            'address' => 'No 3 Sobodu',
            'location' => 'Ojota',
            'shopCode' => '98f833b6',
            'user_id' => 1,
            'status' => 'ASSIGNED',

        ]);
    }
}
