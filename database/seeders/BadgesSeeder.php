<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('badges')->insert([
            [
                'name' => 'Math Whiz',
                'description' => 'Awarded for excellence in math',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Science Explorer',
                'description' => 'Awarded for science projects',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Art Master',
                'description' => 'Awarded for artistic achievements',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
