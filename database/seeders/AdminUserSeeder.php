<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'angelottimauro@gmail.com'],
            [
                'name'              => 'Mauro Angelotti',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        Role::firstOrCreate(['name' => 'admin']);

        $user->assignRole('admin');
    }
}
