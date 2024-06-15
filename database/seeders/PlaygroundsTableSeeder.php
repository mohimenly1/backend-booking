<?php

namespace Database\Seeders;

use App\Models\Playground;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlaygroundsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $owner = User::where('role', 'owner')->first();

        Playground::create([
            'owner_id' => $owner->id,
            'name' => 'Main Playground',
            'description' => 'A large, well-maintained playground.',
            'price_per_half_hour' => 50,
            'price_per_hour' => 90,
            'images' => json_encode(['image1.jpg', 'image2.jpg']),
        ]);

        Playground::factory()->count(5)->create(['owner_id' => $owner->id]);
    }
}
