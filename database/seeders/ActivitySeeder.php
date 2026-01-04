<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\User;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first teacher or created user
        $teacher = User::where('role', 'teacher')->first() ?? User::first();

        if (!$teacher) {
            $this->command->info('No teacher found. Skipping activity seeding.');
            return;
        }

        $activities = [
            [
                'title' => 'Math Bingo',
                'type' => 'Game',
                'suggested_duration' => '20 Minit',
                'description' => 'A fun bingo game to practice multiplication tables.',
            ],
            [
                'title' => 'Science Quiz: Planets',
                'type' => 'Exercise',
                'suggested_duration' => '15 Minit',
                'description' => 'Quick quiz about the solar system.',
            ],
            [
                'title' => 'History Video: Merdeka',
                'type' => 'Video',
                'suggested_duration' => '45 Minit',
                'description' => 'Watch the documentary about Malaysia Independence.',
            ],
            [
                'title' => 'Coding Challenge: Loops',
                'type' => 'Game',
                'suggested_duration' => '30 Minit',
                'description' => 'Solve coding puzzles using loops in Scratch.',
            ],
        ];

        foreach ($activities as $data) {
            Activity::firstOrCreate(
                ['title' => $data['title'], 'teacher_id' => $teacher->id],
                $data
            );
        }

        $this->command->info('Sample activities seeded successfully for teacher: ' . $teacher->full_name);
    }
}
