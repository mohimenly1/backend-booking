<?php

// database/factories/PlaygroundFactory.php

namespace Database\Factories;

use App\Models\Playground;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaygroundFactory extends Factory
{
    protected $model = Playground::class;

    public function definition()
    {
        return [
            'owner_id' => User::where('role', 'owner')->inRandomOrder()->first()->id,
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'price_per_half_hour' => $this->faker->randomFloat(2, 20, 100),
            'price_per_hour' => $this->faker->randomFloat(2, 40, 200),
            'images' => json_encode([$this->faker->imageUrl(), $this->faker->imageUrl()]),
        ];
    }
}
