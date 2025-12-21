<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgesSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // ===== Core / Keperluan =====
            [
                'code' => 'konsistensi',
                'name' => 'Konsistensi',
                'name_bm' => 'Konsistensi',
                'description' => 'Memahami dan menerapkan konsistensi dalam antaramu...',
                'category_code' => 'core',
                'icon' => 'fas fa-check',
                'type' => 'points',
                'value' => 50,
                'extra' => 10,
            ],
            [
                'code' => 'pemerhatian',
                'name' => 'Kebolehan Membuat Pemerhatian',
                'name_bm' => 'Kebolehan Membuat Pemerhatian',
                'description' => 'Merekabentuk elemen yang mudah diperhatikan',
                'category_code' => 'core',
                'icon' => 'fas fa-eye',
                'type' => 'points',
                'value' => 30,
                'extra' => 5,
            ],
            [
                'code' => 'konsistensi_xp',
                'name' => 'Konsistensi XP',
                'name_bm' => 'Konsistensi',
                'description' => 'Menerapkan prinsip konsistensi dalam reka bentuk antaramuka',
                'category_code' => 'core',
                'icon' => 'fas fa-align-left',
                'type' => 'xp',
                'value' => 50,
                'extra' => 0,
            ],
            [
                'code' => 'hierarki_visual',
                'name' => 'Hierarki Visual',
                'name_bm' => 'Hierarki Visual',
                'description' => 'Menyusun elemen berdasarkan keutamaan pengguna.',
                'category_code' => 'core',
                'icon' => 'fas fa-layer-group',
                'type' => 'xp',
                'value' => 80,
                'extra' => 0,
            ],
            [
                'code' => 'keterlihatan',
                'name' => 'Keterlihatan',
                'name_bm' => 'Keterlihatan',
                'description' => 'Memastikan elemen penting mudah dilihat dan diakses.',
                'category_code' => 'core',
                'icon' => 'fas fa-eye',
                'type' => 'xp',
                'value' => 120,
                'extra' => 0,
            ],

            // ===== Design / Reka =====
            [
                'code' => 'hci_asas',
                'name' => 'HCI Asas',
                'name_bm' => 'HCI Asas',
                'description' => 'Memahami asas interaksi manusia-komputer',
                'category_code' => 'design',
                'icon' => 'fas fa-laptop',
                'type' => 'points',
                'value' => 40,
                'extra' => 8,
            ],
            [
                'code' => 'pemahaman_pengguna',
                'name' => 'Pemahaman Pengguna',
                'name_bm' => 'Pemahaman Pengguna',
                'description' => 'Mengenal pasti keperluan dan tingkah laku pengguna...',
                'category_code' => 'design',
                'icon' => 'fas fa-users',
                'type' => 'xp',
                'value' => 60,
                'extra' => 0,
            ],
            [
                'code' => 'analisis_keperluan',
                'name' => 'Analisis Keperluan',
                'name_bm' => 'Analisis Keperluan',
                'description' => 'Menjana dan mengumpul keperluan pengguna.',
                'category_code' => 'design',
                'icon' => 'fas fa-list-check',
                'type' => 'xp',
                'value' => 90,
                'extra' => 0,
            ],
            [
                'code' => 'senario_pengguna',
                'name' => 'Senario Pengguna',
                'name_bm' => 'Senario Pengguna',
                'description' => 'Membina senario penggunaan untuk meramalkan interaksi.',
                'category_code' => 'design',
                'icon' => 'fas fa-clipboard-list',
                'type' => 'xp',
                'value' => 130,
                'extra' => 0,
            ],

            // ===== Prototype / Prototaip =====
            [
                'code' => 'lakaran_wireframe',
                'name' => 'Lakaran Wireframe',
                'name_bm' => 'Lakaran Wireframe',
                'description' => 'Membina lakaran awal antaramuka.',
                'category_code' => 'prototype',
                'icon' => 'fas fa-pencil-ruler',
                'type' => 'xp',
                'value' => 40,
                'extra' => 0,
            ],
            [
                'code' => 'prototip_rendah',
                'name' => 'Prototip Rendah Ketepatan',
                'name_bm' => 'Prototip Rendah Ketepatan',
                'description' => 'Mewakili idea asas sebelum binaan visual penuh.',
                'category_code' => 'prototype',
                'icon' => 'fas fa-drafting-compass',
                'type' => 'xp',
                'value' => 70,
                'extra' => 0,
            ],
            [
                'code' => 'prototip_tinggi',
                'name' => 'Prototip Tinggi Ketepatan',
                'name_bm' => 'Prototip Tinggi Ketepatan',
                'description' => 'Menghasilkan prototaip interaktif hampir produk sebenar.',
                'category_code' => 'prototype',
                'icon' => 'fas fa-laptop-code',
                'type' => 'xp',
                'value' => 120,
                'extra' => 0,
            ],

            // ===== Assessment / Penilaian =====
            [
                'code' => 'heuristik_asas',
                'name' => 'Heuristik Asas',
                'name_bm' => 'Heuristik Asas',
                'description' => 'Mengaplikasikan heuristik Nielsen dalam penilaian.',
                'category_code' => 'assessment',
                'icon' => 'fas fa-flask',
                'type' => 'xp',
                'value' => 50,
                'extra' => 0,
            ],
            [
                'code' => 'ujian_kebolehgunaan',
                'name' => 'Ujian Kebolehgunaan',
                'name_bm' => 'Ujian Kebolehgunaan',
                'description' => 'Menjalankan ujian untuk mengesan masalah pengguna.',
                'category_code' => 'assessment',
                'icon' => 'fas fa-user-check',
                'type' => 'xp',
                'value' => 100,
                'extra' => 0,
            ],
            [
                'code' => 'analisis_maklum_balas',
                'name' => 'Analisis Maklum Balas',
                'name_bm' => 'Analisis Maklum Balas',
                'description' => 'Menilai maklum balas pengguna untuk penambahbaikan.',
                'category_code' => 'assessment',
                'icon' => 'fas fa-comments',
                'type' => 'xp',
                'value' => 150,
                'extra' => 0,
            ],

            // ===== Project / Projek =====
            [
                'code' => 'navigasi_mudah',
                'name' => 'Navigasi Mudah',
                'name_bm' => 'Navigasi Mudah',
                'description' => 'Membina navigasi yang jelas dan tidak mengelirukan.',
                'category_code' => 'project',
                'icon' => 'fas fa-compass',
                'type' => 'xp',
                'value' => 40,
                'extra' => 0,
            ],
            [
                'code' => 'reka_letak_efisien',
                'name' => 'Reka Letak Efisien',
                'name_bm' => 'Reka Letak Efisien',
                'description' => 'Menyusun kandungan untuk meningkatkan aliran pengguna.',
                'category_code' => 'project',
                'icon' => 'fas fa-border-all',
                'type' => 'xp',
                'value' => 70,
                'extra' => 0,
            ],
            [
                'code' => 'kemudahan_akses',
                'name' => 'Kemudahan Akses',
                'name_bm' => 'Kemudahan Akses',
                'description' => 'Menambah ciri mesra OKU seperti kontras tinggi.',
                'category_code' => 'project',
                'icon' => 'fas fa-universal-access',
                'type' => 'xp',
                'value' => 110,
                'extra' => 0,
            ],

            // ===== Innovation / Inovasi =====
            [
                'code' => 'reka_bentuk_kreatif',
                'name' => 'Reka Bentuk Kreatif',
                'name_bm' => 'Reka Bentuk Kreatif',
                'description' => 'Menghasilkan idea inovatif dalam antaramuka.',
                'category_code' => 'innovation',
                'icon' => 'fas fa-lightbulb',
                'type' => 'xp',
                'value' => 50,
                'extra' => 0,
            ],
            [
                'code' => 'teknologi_baharu',
                'name' => 'Teknologi Baharu',
                'name_bm' => 'Teknologi Baharu',
                'description' => 'Menggunakan AR/VR atau teknologi moden dalam UI.',
                'category_code' => 'innovation',
                'icon' => 'fas fa-vr-cardboard',
                'type' => 'xp',
                'value' => 100,
                'extra' => 0,
            ],
            [
                'code' => 'eksperimen_ui',
                'name' => 'Eksperimen UI',
                'name_bm' => 'Eksperimen UI',
                'description' => 'Mencuba konsep UI baharu yang unik dan kreatif.',
                'category_code' => 'innovation',
                'icon' => 'fas fa-flask-vial',
                'type' => 'xp',
                'value' => 140,
                'extra' => 0,
            ],

            // ===== Solution / Solusi =====
            [
                'code' => 'penyelesaian_masalah',
                'name' => 'Penyelesaian Masalah',
                'name_bm' => 'Penyelesaian Masalah',
                'description' => 'Mengenal pasti dan menyelesaikan isu pengguna.',
                'category_code' => 'solution',
                'icon' => 'fas fa-tools',
                'type' => 'xp',
                'value' => 60,
                'extra' => 0,
            ],
            [
                'code' => 'iterasi_berterusan',
                'name' => 'Iterasi Berterusan',
                'name_bm' => 'Iterasi Berterusan',
                'description' => 'Menambah baik reka bentuk melalui proses iterasi.',
                'category_code' => 'solution',
                'icon' => 'fas fa-sync',
                'type' => 'xp',
                'value' => 130,
                'extra' => 0,
            ],

            // ===== Ethics / Etika =====
            [
                'code' => 'privasi_pengguna',
                'name' => 'Privasi Pengguna',
                'name_bm' => 'Privasi Pengguna',
                'description' => 'Menjaga keselamatan dan privasi maklumat pengguna.',
                'category_code' => 'ethics',
                'icon' => 'fas fa-shield-alt',
                'type' => 'xp',
                'value' => 50,
                'extra' => 0,
            ],
            [
                'code' => 'reka_bentuk_beretika',
                'name' => 'Reka Bentuk Beretika',
                'name_bm' => 'Reka Bentuk Beretika',
                'description' => 'Mengelakkan dark patterns dalam antaramuka.',
                'category_code' => 'ethics',
                'icon' => 'fas fa-balance-scale',
                'type' => 'xp',
                'value' => 80,
                'extra' => 0,
            ],

            // ===== Social / Interaction =====
            [
                'code' => 'friendly',
                'name' => 'Rakan Baik',
                'name_bm' => 'Rakan Baik',
                'description' => 'Mempunyai 5 rakan yang aktif.',
                'category_code' => 'social',
                'icon' => 'fas fa-smile',
                'color' => '#EC4899',
                'requirement_type' => 'friend_count',
                'points_required' => 5,
                'xp_reward' => 100,
            ],
            [
                'code' => 'pempengaruh',
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

            // ===== System / Requirement =====
            [
                'code' => 'newcomer',
                'name' => 'Pendatang Baru',
                'name_bm' => 'Pendatang Baru',
                'description' => 'Log masuk ke sistem buat kali pertama.',
                'category_code' => 'system',
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
                'category_code' => 'system',
                'icon' => 'fas fa-id-card',
                'color' => '#10B981',
                'requirement_type' => 'profile_completion',
                'points_required' => 100,
                'xp_reward' => 50,
            ],
            [
                'code' => 'pengguna_aktif',
                'name' => 'Pengguna Aktif',
                'name_bm' => 'Pengguna Aktif',
                'description' => 'Log masuk ke sistem selama 7 hari berturut-turut.',
                'category_code' => 'system',
                'icon' => 'fas fa-calendar-check',
                'color' => '#22C55E',
                'requirement_type' => 'login_streak',
                'points_required' => 7,
                'xp_reward' => 50,
            ],
            [
                'code' => 'pengguna_setia',
                'name' => 'Pengguna Setia',
                'name_bm' => 'Pengguna Setia',
                'description' => 'Menggunakan sistem secara aktif selama 30 hari.',
                'category_code' => 'system',
                'icon' => 'fas fa-fire',
                'color' => '#EF4444',
                'requirement_type' => 'login_streak',
                'points_required' => 30,
                'xp_reward' => 100,
            ],

            // ===== Achievement =====
            [
                'code' => 'quiz_master',
                'name' => 'Juara Kuiz',
                'name_bm' => 'Juara Kuiz',
                'description' => 'Mendapat markah penuh dalam 3 kuiz berturut-turut.',
                'category_code' => 'achievement',
                'icon' => 'fas fa-brain',
                'color' => '#F59E0B',
                'requirement_type' => 'quiz_streak',
                'points_required' => 3,
                'xp_reward' => 300,
            ],

            // ===== Learning / Assessment =====
            [
                'code' => 'ulat_buku',
                'name' => 'Ulat Buku',
                'name_bm' => 'Ulat Buku',
                'description' => 'Menghabiskan 10 pelajaran.',
                'category_code' => 'learning',
                'icon' => 'fas fa-book-reader',
                'color' => '#8B5CF6',
                'requirement_type' => 'lessons_completed',
                'points_required' => 10,
                'xp_reward' => 150,
            ],
        ];

        foreach ($badges as $badge) {
            // Ensure defaults
            if (!isset($badge['requirement_type'])) $badge['requirement_type'] = null;
            if (!isset($badge['points_required'])) $badge['points_required'] = 0;
            if (!isset($badge['xp_reward'])) $badge['xp_reward'] = 0;

            // Lookup category ID
            if (isset($badge['category_code'])) {
                $categoryId = DB::table('badge_category')->where('code', $badge['category_code'])->value('id');
                if ($categoryId) $badge['category_id'] = $categoryId;
            }

            DB::table('badge')->updateOrInsert(
                ['code' => $badge['code']],
                $badge
            );
        }
    }
}
