<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'reservation_id' => Reservation::inRandomOrder()->first()->id,
            'user_id' => User::where('role', 'user')->inRandomOrder()->first()->id,
            'amount' => $this->faker->randomFloat(2, 50, 200),
            'payment_method' => $this->faker->randomElement(['Anis', 'Libyana', 'bank_transfer']),
        ];
    }
}
