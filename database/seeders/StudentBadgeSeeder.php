<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentBadgeSeeder extends Seeder
{
    public function run(): void
    {
        // Define Student Badges separated by Category
        $badges = [
            // === Penilaian Akademik (Student Only) ===
            [
                'code' => 'straight_a',
                'name' => 'Skor Sempurna',
                'name_bm' => 'Skor Sempurna',
                'description' => 'Mendapat markah penuh dalam penilaian.',
                'category_code' => 'penilaian-akademik',
                'icon' => 'fas fa-star',
                'color' => '#8B5CF6',
                'xp_reward' => 100,
            ],
            [
                'code' => 'quiz_whiz',
                'name' => 'Pakar Kuiz',
                'name_bm' => 'Pakar Kuiz',
                'description' => 'Menjawab 5 kuiz berturut-turut tanpa salah.',
                'category_code' => 'penilaian-akademik',
                'icon' => 'fas fa-brain',
                'color' => '#8B5CF6',
                'xp_reward' => 80,
            ],
            [
                'code' => 'assignment_hero',
                'name' => 'Wira Tugasan',
                'name_bm' => 'Wira Tugasan',
                'description' => 'Menghantar tugasan sebelum tarikh akhir.',
                'category_code' => 'penilaian-akademik',
                'icon' => 'fas fa-file-alt',
                'color' => '#8B5CF6',
                'xp_reward' => 50,
            ],

            // === Sosial (Student Only) ===
            [
                'code' => 'social_butterfly',
                'name' => 'Rama-rama Sosial',
                'name_bm' => 'Rama-rama Sosial',
                'description' => 'Mempunyai 10 rakan baharu.',
                'category_code' => 'sosial',
                'icon' => 'fas fa-users',
                'color' => '#EC4899',
                'xp_reward' => 50,
            ],
            [
                'code' => 'helper',
                'name' => 'Pembantu Setia',
                'name_bm' => 'Pembantu Setia',
                'description' => 'Membantu rakan dalam forum perbincangan.',
                'category_code' => 'sosial',
                'icon' => 'fas fa-hands-helping',
                'color' => '#EC4899',
                'xp_reward' => 40,
            ],
            [
                'code' => 'team_player',
                'name' => 'Pemain Pasukan',
                'name_bm' => 'Pemain Pasukan',
                'description' => 'Menyertai aktiviti berkumpulan.',
                'category_code' => 'sosial',
                'icon' => 'fas fa-people-carry',
                'color' => '#EC4899',
                'xp_reward' => 60,
            ],

            // === Pencapaian (Student Only) ===
            [
                'code' => 'early_bird',
                'name' => 'Burung Awal',
                'name_bm' => 'Burung Awal',
                'description' => 'Log masuk sebelum jam 7 pagi.',
                'category_code' => 'pencapaian',
                'icon' => 'fas fa-sun',
                'color' => '#F59E0B',
                'xp_reward' => 20,
            ],
            [
                'code' => 'streak_master',
                'name' => 'Raja Konsisten',
                'name_bm' => 'Raja Konsisten',
                'description' => 'Log masuk setiap hari selama sebulan.',
                'category_code' => 'pencapaian',
                'icon' => 'fas fa-fire',
                'color' => '#F59E0B',
                'xp_reward' => 150,
            ],
            [
                'code' => 'explorer',
                'name' => 'Penjelajah',
                'name_bm' => 'Penjelajah',
                'description' => 'Melawat semua halaman dalam sistem.',
                'category_code' => 'pencapaian',
                'icon' => 'fas fa-compass',
                'color' => '#F59E0B',
                'xp_reward' => 30,
            ],
        ];

        foreach ($badges as $badge) {
            // Find category ID by code
            $catId = DB::table('badge_category')->where('code', $badge['category_code'])->value('id');
            
            if ($catId) {
                // Prepare data for insertion
                $categoryCode = $badge['category_code'];
                unset($badge['category_code']); // Remove from array as it's not in badge table
                
                $badge['category_id'] = $catId;
                $badge['category_slug'] = $categoryCode; // Required column
                $badge['created_at'] = now();
                $badge['updated_at'] = now();
                
                // Add defaults for likely required columns (based on legacy schema)
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
