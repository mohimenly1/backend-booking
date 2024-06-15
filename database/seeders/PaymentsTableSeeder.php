<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $reservation = Reservation::first();
        $user = $reservation->user;

        Payment::create([
            'reservation_id' => $reservation->id,
            'user_id' => $user->id,
            'amount' => 90,
            'payment_method' => 'credit_card',
        ]);

        Payment::factory()->count(10)->create(['reservation_id' => $reservation->id, 'user_id' => $user->id]);
    }
}
