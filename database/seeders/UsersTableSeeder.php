<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'phone' => '0923290545',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'wallet_balance' => 0,
        ]);

        User::create([
            'name' => 'Owner User',
            'phone' => '0913290545',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'wallet_balance' => 0,
        ]);

        User::create([
            'name' => 'Regular User',
            'phone' => '0943290545',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'wallet_balance' => 100,
        ]);

        User::factory()->count(10)->create();
    }
}
