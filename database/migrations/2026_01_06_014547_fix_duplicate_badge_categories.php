<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mappings = [
            'social' => 'sosial',
            'achievement' => 'pencapaian',
            'assessment' => 'penilaian-akademik',
            'teacher-excellence' => 'kecemerlangan-guru',
        ];

        foreach ($mappings as $oldCode => $newCode) {
            $oldCat = DB::table('badge_category')->where('code', $oldCode)->first();
            $newCat = DB::table('badge_category')->where('code', $newCode)->first();

            if ($oldCat && $newCat) {
                // Move badges to new category
                DB::table('badge')
                    ->where('category_id', $oldCat->id)
                    ->update([
                        'category_id' => $newCat->id,
                        'category_slug' => $newCode
                    ]);

                // Delete old category
                DB::table('badge_category')->where('id', $oldCat->id)->delete();
            }
        }
    }

    public function down(): void
    {
        // Irreversible data merge
    }
};
