<?php

namespace Database\Seeders;

use App\Models\FinancialReport;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinancialReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $owner = User::where('role', 'owner')->first();

        FinancialReport::create([
            'owner_id' => $owner->id,
            'report_date' => now()->toDateString(),
            'total_revenue' => 5000,
        ]);

        FinancialReport::factory()->count(10)->create(['owner_id' => $owner->id]);
    }
}
