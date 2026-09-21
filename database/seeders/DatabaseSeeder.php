<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Mrs. de Loyola (Admin)',
            'email' => 'admin@maxgym.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Front Desk Cashier',
            'email' => 'cashier@maxgym.test',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);
    }
}
