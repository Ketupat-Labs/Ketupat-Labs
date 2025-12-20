<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kemahiran',
                'code' => 'skill',
                'color' => '#3B82F6', // Blue
                'icon' => 'fas fa-tools',
                'description' => 'Lencana berkaitan kemahiran teknikal dan praktikal.'
            ],
            [
                'name' => 'Keperluan',
                'code' => 'requirement',
                'color' => '#10B981', // Green
                'icon' => 'fas fa-check-circle',
                'description' => 'Lencana wajib yang perlu diselesaikan.'
            ],
            [
                'name' => 'Pencapaian Khas',
                'code' => 'achievement',
                'color' => '#F59E0B', // Amber
                'icon' => 'fas fa-trophy',
                'description' => 'Pencapaian istimewa luar biasa.'
            ],
            [
                'name' => 'Sosial',
                'code' => 'social',
                'color' => '#EC4899', // Pink
                'icon' => 'fas fa-users',
                'description' => 'Lencana berkaitan interaksi sosial dan rakan.'
            ],
            [
                'name' => 'Penilaian',
                'code' => 'assessment',
                'color' => '#8B5CF6', // Purple
                'icon' => 'fas fa-clipboard-check',
                'description' => 'Lencana berkaitan kuiz dan ujian.'
            ],
        ];

        foreach ($categories as $category) {
            DB::table('badge_category')->updateOrInsert(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
