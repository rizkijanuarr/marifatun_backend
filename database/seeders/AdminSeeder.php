<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@marifatun.test'],
            [
                'name' => 'Admin Marifatun',
                'password' => Hash::make('password'),
                'active' => true,
                'createdBy' => 'system',
            ]
        );

        if (! $admin->hasRole(RoleEnum::ADMIN->value)) {
            $admin->assignRole(RoleEnum::ADMIN->value);
        }
    }
}
