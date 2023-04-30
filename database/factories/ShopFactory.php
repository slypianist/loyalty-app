<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Shop::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'shopCode' => $this->faker->swiftBicNumber,
            'name' => $this->faker->company(),
            'address' => $this->faker->streetAddress,
            'location' => $this->faker->city
        ];
    }
}
