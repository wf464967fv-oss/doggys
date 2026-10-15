<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Reference data is idempotent and safe to replay in production.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            PostalCodeSeeder::class,
            CommuneSeeder::class,
            GuideSeeder::class,
        ]);

        if (app()->isLocal()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
