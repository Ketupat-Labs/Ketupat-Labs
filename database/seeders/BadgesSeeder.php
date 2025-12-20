<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Badge;

class BadgesSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // Social Badges
            [
                'code' => 'friendly', // Was rakan_baik
                'name' => 'Rakan Baik',
                'name_bm' => 'Rakan Baik',
                'description' => 'Mempunyai 5 rakan yang aktif.',
                'category_code' => 'social',
                'icon' => 'fas fa-smile', // Restore original icon
                'color' => '#EC4899',
                'requirement_type' => 'friend_count',
                'points_required' => 5,
                'xp_reward' => 100, // Match screenshot +100 XP
            ],
            [
                'code' => 'pempengaruh', // Keep new one if no equivalent found
                'name' => 'Pempengaruh',
                'name_bm' => 'Pempengaruh',
                'description' => 'Mempunyai 20 rakan yang aktif.',
                'category_code' => 'social',
                'icon' => 'fas fa-bullhorn',
                'color' => '#EC4899',
                'requirement_type' => 'friend_count',
                'points_required' => 20,
                'xp_reward' => 200,
            ],



            // Requirement Badges
            [
                'code' => 'newcomer', // Was pendatang_baru
                'name' => 'Pendatang Baru',
                'name_bm' => 'Pendatang Baru',
                'description' => 'Log masuk ke sistem buat kali pertama.',
                'category_code' => 'requirement',
                'icon' => 'fas fa-door-open',
                'color' => '#10B981',
                'requirement_type' => 'first_login',
                'points_required' => 1,
                'xp_reward' => 25,
            ],
            [
                'code' => 'profil_lengkap',
                'name' => 'Profil Lengkap',
                'name_bm' => 'Profil Lengkap',
                'description' => 'Melengkapkan semua maklumat profil.',
                'category_code' => 'requirement',
                'icon' => 'fas fa-id-card',
                'color' => '#10B981',
                'requirement_type' => 'profile_completion',
                'points_required' => 100, // 100% complete
                'xp_reward' => 50,
            ],

            // Achievement Badges
            [
                'code' => 'quiz_master', // Was juara_kuiz
                'name' => 'Juara Kuiz',
                'name_bm' => 'Juara Kuiz',
                'description' => 'Mendapat markah penuh dalam 3 kuiz berturut-turut.',
                'category_code' => 'achievement',
                'icon' => 'fas fa-brain', // Restore original
                'color' => '#F59E0B',
                'requirement_type' => 'quiz_streak',
                'points_required' => 3,
                'xp_reward' => 300,
            ],

            // Assessment Badges
            [
                'code' => 'ulat_buku',
                'name' => 'Ulat Buku',
                'name_bm' => 'Ulat Buku',
                'description' => 'Menghabiskan 10 pelajaran.',
                'category_code' => 'assessment',
                'icon' => 'fas fa-book-reader',
                'color' => '#8B5CF6',
                'requirement_type' => 'lessons_completed',
                'points_required' => 10,
                'xp_reward' => 150,
            ]
        ];

        foreach ($badges as $badge) {
            // Lookup category ID from code
            if (isset($badge['category_code'])) {
                $categoryId = DB::table('badge_category')->where('code', $badge['category_code'])->value('id');
                if ($categoryId) {
                    $badge['category_id'] = $categoryId;
                }
            }
            
            DB::table('badge')->updateOrInsert(
                ['code' => $badge['code']], 
                $badge
            );
        }
    }
}
