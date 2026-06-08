<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@loadcafe.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Cashier users
        $cashier1 = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@loadcafe.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $cashier1->assignRole('cashier');

        $cashier2 = User::create([
            'name' => 'John Reyes',
            'email' => 'john@loadcafe.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $cashier2->assignRole('cashier');

        // Kitchen staff
        $kitchen1 = User::create([
            'name' => 'Chef Rosa',
            'email' => 'rosa@loadcafe.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $kitchen1->assignRole('kitchen');

        $kitchen2 = User::create([
            'name' => 'Pedro Dela Cruz',
            'email' => 'pedro@loadcafe.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $kitchen2->assignRole('kitchen');

        // Auditor
        $auditor = User::create([
            'name' => 'Anna Cruz',
            'email' => 'anna@loadcafe.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $auditor->assignRole('auditor');
    }
}
