<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::firstOrCreate(
            ['username' => 'testuser'],
            [
                'full_name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'student',
                'password' => bcrypt('password'), // Ensure a password is set if creating new
            ]
        );

        $this->call([
            UsersSeeder::class,
            BadgeCategorySeeder::class,
            StudentBadgeSeeder::class,
            TeacherBadgeSeeder::class,
            // BadgesSeeder::class, 
            // AchievementSeeder::class, // Table does not exist
            ActivitySeeder::class,
        ]);
    }

    
}