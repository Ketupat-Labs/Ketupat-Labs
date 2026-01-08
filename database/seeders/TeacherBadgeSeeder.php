<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherBadgeSeeder extends Seeder
{
    public function run(): void
    {
        // Define Teacher Badges
        $badges = [
            // === Kecemerlangan Guru (Teacher Only) ===
            [
                'code' => 'top_instructor',
                'name' => 'Pengajar Terbaik',
                'name_bm' => 'Pengajar Terbaik',
                'description' => 'Mendapat penilaian tertinggi daripada pelajar.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-chalkboard-teacher',
                'color' => '#3B82F6',
                'xp_reward' => 200,
            ],
            [
                'code' => 'content_creator',
                'name' => 'Pencipta Kandungan',
                'name_bm' => 'Pencipta Kandungan',
                'description' => 'Memuat naik 20 bahan pengajaran berkualiti.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-video',
                'color' => '#3B82F6',
                'xp_reward' => 150,
            ],
            [
                'code' => 'mentor',
                'name' => 'Mentor Dedikasi',
                'name_bm' => 'Mentor Dedikasi',
                'description' => 'Membimbing 5 pelajar bermasalah hingga berjaya.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-hand-holding-heart',
                'color' => '#3B82F6',
                'xp_reward' => 180,
            ],
            [
                'code' => 'innovative_teacher',
                'name' => 'Guru Inovatif',
                'name_bm' => 'Guru Inovatif',
                'description' => 'Mencipta aktiviti pembelajaran yang kreatif dan unik.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-lightbulb',
                'color' => '#F59E0B',
                'xp_reward' => 160,
            ],
            [
                'code' => 'review_expert',
                'name' => 'Pakar Semakan',
                'name_bm' => 'Pakar Semakan',
                'description' => 'Menyemak 50 tugasan pelajar dengan pantas dan teliti.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-clipboard-check',
                'color' => '#10B981',
                'xp_reward' => 140,
            ],
            [
                'code' => 'efficient_manager',
                'name' => 'Pengurus Efisien',
                'name_bm' => 'Pengurus Efisien',
                'description' => 'Menguruskan data dan rekod kelas dengan cemerlang.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-tasks',
                'color' => '#6366F1',
                'xp_reward' => 120,
            ],
            [
                'code' => 'digital_teacher',
                'name' => 'Guru Digital',
                'name_bm' => 'Guru Digital',
                'description' => 'Menggunakan teknologi digital dalam pengajaran harian.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-laptop-code',
                'color' => '#8B5CF6',
                'xp_reward' => 200,
            ],
            [
                'code' => 'school_ambassador',
                'name' => 'Duta Sekolah',
                'name_bm' => 'Duta Sekolah',
                'description' => 'Mewakili sekolah dalam program komuniti atau pertandingan.',
                'category_code' => 'kecemerlangan-guru',
                'icon' => 'fas fa-flag',
                'color' => '#EF4444',
                'xp_reward' => 250,
            ],
        ];

        foreach ($badges as $badge) {
            // Find category ID by code
            $catId = DB::table('badge_category')->where('code', $badge['category_code'])->value('id');
            
            if ($catId) {
                $categoryCode = $badge['category_code'];
                unset($badge['category_code']);
                $badge['category_id'] = $catId;
                $badge['category_slug'] = $categoryCode; // Required column
                $badge['created_at'] = now();
                $badge['updated_at'] = now();

                // Add defaults for likely required columns
                $badge['type'] = $badge['type'] ?? 'xp';
                $badge['value'] = $badge['value'] ?? 0;
                $badge['extra'] = $badge['extra'] ?? 0;
                $badge['points_required'] = $badge['points_required'] ?? 0;
                $badge['requirement_value'] = $badge['points_required']; // Map to legacy column to satisfy constraint
                $badge['requirement_type'] = $badge['requirement_type'] ?? null;


                DB::table('badge')->updateOrInsert(
                    ['code' => $badge['code']],
                    $badge
                );
            }
        }
    }
}
