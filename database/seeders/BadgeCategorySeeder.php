<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('badge_categories')->insert([
            ['name' => 'Science'],
            ['name' => 'Arts'],
            ['name' => 'Sports'],
        ]);
    }
}
