<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Badges to remove (the ones I added that created duplicates)
        // Original codes like 'friendly', 'newcomer' will be kept
        $duplicateBadges = [
            'rakan_baik', 
            'pakar_kod', 
            'pendatang_baru', 
            'profil_lengkap', 
            'juara_kuiz', 
            'ulat_buku'
        ];

        // 1. Remove user_badge entries for these duplicates to prevent orphans
        DB::table('user_badge')->whereIn('badge_code', $duplicateBadges)->delete();

        // 2. Remove the actual duplicate badges
        DB::table('badge')->whereIn('code', $duplicateBadges)->delete();
        
        // 3. Ensure original 'friendly' badge has correct metadata (just in case)
         DB::table('badge')->updateOrInsert(
            ['code' => 'friendly'],
            [
                'name_bm' => 'Rakan Baik',
                'category_code' => 'social',
                'points_required' => 5,
                // Do not overwrite icon or color to respect user preference for original
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible cleanup
    }
};
