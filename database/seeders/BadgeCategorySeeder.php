<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BadgeCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Ensure role_restriction column exists (failsafe)
        $hasRoleColumn = Schema::hasColumn('badge_category', 'role_restriction');

        $categories = [
            // === Teacher Categories ===
            [
                'code' => 'kecemerlangan-guru',
                'name' => 'Kecemerlangan Guru',
                'name_bm' => 'Kecemerlangan Guru',
                'description' => 'Lencana khas untuk pencapaian guru.',
                'role_restriction' => 'teacher',
                'color' => '#3B82F6', // Blue
                'icon' => 'fas fa-chalkboard-teacher',
            ],
            [
                'code' => 'pembangunan-profesional',
                'name' => 'Pembangunan Profesional',
                'name_bm' => 'Pembangunan Profesional',
                'description' => 'Pencapaian dalam latihan dan kursus profesional.',
                'role_restriction' => 'teacher',
                'color' => '#10B981', // Emerald/Green
                'icon' => 'fas fa-briefcase',
            ],

            // === Student Categories ===
            [
                'code' => 'penilaian-akademik',
                'name' => 'Penilaian Akademik',
                'name_bm' => 'Penilaian Akademik',
                'description' => 'Lencana untuk pencapaian akademik pelajar.',
                'role_restriction' => 'student',
                'color' => '#8B5CF6', // Purple
                'icon' => 'fas fa-graduation-cap',
            ],
            [
                'code' => 'sosial',
                'name' => 'Sosial',
                'name_bm' => 'Sosial',
                'description' => 'Interaksi dan kemahiran sosial pelajar.',
                'role_restriction' => 'student',
                'color' => '#EC4899', // Pink
                'icon' => 'fas fa-users',
            ],
            [
                'code' => 'pencapaian',
                'name' => 'Pencapaian',
                'name_bm' => 'Pencapaian',
                'description' => 'Pencapaian umum dan aktiviti pelajar.',
                'role_restriction' => 'student',
                'color' => '#F59E0B', // Orange
                'icon' => 'fas fa-trophy',
            ],

            // === General / Legacy Categories (Mapped to 'all' or specific) ===
            [
                'code' => 'umum',
                'name' => 'Umum',
                'name_bm' => 'Umum',
                'description' => 'Lencana untuk semua pengguna.',
                'role_restriction' => 'all',
                'color' => '#10B981', // Green
                'icon' => 'fas fa-globe',
            ],
        ];

        foreach ($categories as $category) {
            // Remove role_restriction key if column doesn't exist
            if (!$hasRoleColumn && isset($category['role_restriction'])) {
                unset($category['role_restriction']);
            }

            DB::table('badge_category')->updateOrInsert(
                ['code' => $category['code']],
                $category
            );
        }
    }
}

