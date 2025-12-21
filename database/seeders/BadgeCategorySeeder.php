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
                'code' => 'keperluan',
                'name' => 'Keperluan',
                'name_bm' => 'Keperluan',
                'description' => 'Keperluan pengguna dan asas interaksi.',
                'color' => '#1abc9c',
            ],
            [
                'code' => 'reka',
                'name' => 'Reka',
                'name_bm' => 'Reka',
                'description' => 'Reka bentuk dan prinsip HCI.',
                'color' => '#9b59b6',
            ],
            [
                'code' => 'prototaip',
                'name' => 'Prototaip',
                'name_bm' => 'Prototaip',
                'description' => 'Membina prototaip untuk UI/UX.',
                'color' => '#e67e22',
            ],
            [
                'code' => 'penilaian',
                'name' => 'Penilaian',
                'name_bm' => 'Penilaian',
                'description' => 'Ujian kebolehgunaan dan analisis maklum balas.',
                'color' => '#8e44ad',
            ],
            [
                'code' => 'projek',
                'name' => 'Projek',
                'name_bm' => 'Projek',
                'description' => 'Reka bentuk projek dan implementasi.',
                'color' => '#27ae60',
            ],
            [
                'code' => 'inovasi',
                'name' => 'Inovasi',
                'name_bm' => 'Inovasi',
                'description' => 'Idea kreatif dan teknologi baharu.',
                'color' => '#f1c40f',
            ],
            [
                'code' => 'solusi',
                'name' => 'Solusi',
                'name_bm' => 'Solusi',
                'description' => 'Penyelesaian masalah dan iterasi reka bentuk.',
                'color' => '#c0392b',
            ],
            [
                'code' => 'etika',
                'name' => 'Etika',
                'name_bm' => 'Etika',
                'description' => 'Privasi pengguna dan reka bentuk beretika.',
                'color' => '#7f8c8d',
            ],
            [
                'code' => 'aktiviti',
                'name' => 'Aktiviti Sistem',
                'name_bm' => 'Aktiviti Sistem',
                'description' => 'Badge berdasarkan aktiviti pengguna seperti login atau feedback.',
                'color' => '#10B981',
            ],
            [
                'code' => 'social',
                'name' => 'Sosial',
                'name_bm' => 'Sosial',
                'description' => 'Badge berkaitan interaksi sosial pengguna.',
                'color' => '#EC4899',
            ],
            [
                'code' => 'achievement',
                'name' => 'Pencapaian',
                'name_bm' => 'Pencapaian',
                'description' => 'Badge untuk pencapaian tertentu dalam sistem.',
                'color' => '#F59E0B',
            ],
            [
                'code' => 'assessment',
                'name' => 'Penilaian Akademik',
                'name_bm' => 'Penilaian Akademik',
                'description' => 'Badge berkaitan pembelajaran atau kuiz.',
                'color' => '#8B5CF6',
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
