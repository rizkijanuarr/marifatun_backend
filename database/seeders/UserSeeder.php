<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserCredit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'user@marifatun.test'],
            [
                'name' => 'Marifatun User',
                'password' => Hash::make('password'),
                'active' => true,
                'createdBy' => 'system',
            ]
        );

        if (! $user->hasRole(RoleEnum::MARIFATUN_USER->value)) {
            $user->assignRole(RoleEnum::MARIFATUN_USER->value);
        }

        UserCredit::firstOrCreate(
            ['user_id' => $user->id],
            [
                'credits' => 1,
                'last_daily_claim' => now(),
                'active' => true,
                'createdBy' => 'system',
            ]
        );
    }
}
