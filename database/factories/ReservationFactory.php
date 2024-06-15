<?php

// database/factories/ReservationFactory.php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Playground;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        return [
            'user_id' => User::where('role', 'user')->inRandomOrder()->first()->id,
            'playground_id' => Playground::inRandomOrder()->first()->id,
            'start_time' => $this->faker->dateTimeBetween('+1 days', '+2 days'),
            'end_time' => $this->faker->dateTimeBetween('+2 days', '+3 days'),
            'total_price' => $this->faker->randomFloat(2, 50, 200),
            'status' => 'pending',
        ];
    }
}
