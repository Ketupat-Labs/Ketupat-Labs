<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    public function run()
    {
        $achievements = [
            // Somaa category
            [
                'category' => 'Somaa',
                'name' => 'Akademia',
                'description' => 'Complete academic requirements',
                'icon' => 'academia-icon',
                'points' => 10,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],
            [
                'category' => 'Somaa',
                'name' => 'Kameraman',
                'description' => 'Master camera skills',
                'icon' => 'camera-icon',
                'points' => 15,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],
            [
                'category' => 'Somaa',
                'name' => 'Social',
                'description' => 'Excel in social activities',
                'icon' => 'social-icon',
                'points' => 10,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],
            [
                'category' => 'Somaa',
                'name' => 'Penibali',
                'description' => 'Achieve writing excellence',
                'icon' => 'writing-icon',
                'points' => 12,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],

            // Pengaluruna Permulaan
            [
                'category' => 'Pengaluruna Permulaan',
                'name' => 'Complete Beginner Module',
                'description' => 'Complete the beginner programming module',
                'icon' => 'beginner-icon',
                'points' => 20,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],

            // Kunu
            [
                'category' => 'Kunu',
                'name' => 'Web Development',
                'description' => 'Complete HTML & CSS module',
                'icon' => 'web-dev-icon',
                'points' => 25,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],
            [
                'category' => 'Kunu',
                'name' => 'JavaScript Master',
                'description' => 'Complete JavaScript course',
                'icon' => 'js-icon',
                'points' => 30,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],
            [
                'category' => 'Kunu',
                'name' => 'Database Expert',
                'description' => 'Complete database module with schema',
                'icon' => 'database-icon',
                'points' => 25,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],
            [
                'category' => 'Kunu',
                'name' => 'Mobile Developer',
                'description' => 'Build first mobile application',
                'icon' => 'mobile-icon',
                'points' => 35,
                'condition_type' => 'boolean',
                'condition_value' => 1
            ],

            // Reading achievements
            [
                'category' => 'Reading',
                'name' => 'Bookworm',
                'description' => 'Read 10 programming e-books',
                'icon' => 'book-icon',
                'points' => 20,
                'condition_type' => 'count',
                'condition_value' => 10
            ],

            // Course achievements
            [
                'category' => 'Courses',
                'name' => 'Course Master',
                'description' => 'Complete 5 full courses',
                'icon' => 'course-icon',
                'points' => 40,
                'condition_type' => 'count',
                'condition_value' => 5
            ],

            // Academic achievements
            [
                'category' => 'Academic',
                'name' => 'Straight A\'s',
                'description' => 'Get A grade in 10 quizzes',
                'icon' => 'grade-a-icon',
                'points' => 30,
                'condition_type' => 'count',
                'condition_value' => 10
            ],
            [
                'category' => 'Academic',
                'name' => 'Perfect Score',
                'description' => 'Score 100% in any assessment',
                'icon' => 'perfect-score-icon',
                'points' => 25,
                'condition_type' => 'count',
                'condition_value' => 1
            ],

            // Project achievements
            [
                'category' => 'Projects',
                'name' => 'Project Manager',
                'description' => 'Complete 3 actual projects',
                'icon' => 'project-icon',
                'points' => 45,
                'condition_type' => 'count',
                'condition_value' => 3
            ],

            // Coding achievements
            [
                'category' => 'Coding',
                'name' => 'Debug Master',
                'description' => 'Fix 50 coding errors',
                'icon' => 'debug-icon',
                'points' => 35,
                'condition_type' => 'count',
                'condition_value' => 50
            ],
            [
                'category' => 'Coding',
                'name' => 'Speed Coder',
                'description' => 'Write 1000 lines of code without errors',
                'icon' => 'speed-code-icon',
                'points' => 40,
                'condition_type' => 'count',
                'condition_value' => 1000
            ],
            [
                'category' => 'Coding',
                'name' => 'Problem Solver',
                'description' => 'Solve 100 coding challenges',
                'icon' => 'problem-solve-icon',
                'points' => 50,
                'condition_type' => 'count',
                'condition_value' => 100
            ],

            // Design achievements
            [
                'category' => 'Design',
                'name' => 'UI Designer',
                'description' => 'Design 3 user interfaces',
                'icon' => 'ui-design-icon',
                'points' => 30,
                'condition_type' => 'count',
                'condition_value' => 3
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}