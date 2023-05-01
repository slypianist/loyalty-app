<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $gender = $this->faker->randomElement(['male', 'female']);
        return [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'phoneNum' => $this->faker->phoneNumber,
            'address' => $this->faker->streetAddress,
            'gender' => $gender,

        ];
    }
}
