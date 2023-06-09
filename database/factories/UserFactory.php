<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstName' => $this->faker->firstname,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
           'address' => $this->faker->streetAddress,
           'phoneNum' =>$this->faker->phoneNumber
        ];
    }
}
