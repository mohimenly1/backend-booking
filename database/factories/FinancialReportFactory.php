<?php

// database/factories/FinancialReportFactory.php

namespace Database\Factories;

use App\Models\FinancialReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialReportFactory extends Factory
{
    protected $model = FinancialReport::class;

    public function definition()
    {
        return [
            'owner_id' => User::where('role', 'owner')->inRandomOrder()->first()->id,
            'report_date' => $this->faker->date(),
            'total_revenue' => $this->faker->randomFloat(2, 1000, 10000),
        ];
    }
}
