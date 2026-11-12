<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Администратор ICC', 'admin@icc.test', UserRole::Admin],
            ['Креатор ICC', 'creator@icc.test', UserRole::Creator],
            ['Пользователь ICC', 'user@icc.test', UserRole::User],
        ] as [$name, $email, $role]) {
            if (!User::where('email', $email)->exists()) {
                User::factory()->create(compact('name', 'email', 'role') + ['password' => 'DemoPass123!']);
            }
        }

        $this->call(VectorMakerSeeder::class);
    }
}
