<?php

namespace Database\Seeders;

use App\Models\Playground;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReservationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $user = User::where('role', 'user')->first();
        $playground = Playground::first();

        Reservation::create([
            'user_id' => $user->id,
            'playground_id' => $playground->id,
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'total_price' => 90,
            'status' => 'confirmed',
        ]);

        Reservation::factory()->count(10)->create(['user_id' => $user->id, 'playground_id' => $playground->id]);
    }
}
