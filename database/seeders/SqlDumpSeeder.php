<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SqlDumpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Target the complete SQL file
        $sqlFile = database_path('seeders/compuplay_complete.sql');
        
        if (!File::exists($sqlFile)) {
            $this->command->warn("Complete SQL dump file not found at: $sqlFile");
            return;
        }

        $this->command->info("Restoring full database from $sqlFile...");
        
        // Read the entire file
        // Since the file contains DROP TABLE and CREATE TABLE, we can execute it directly.
        // However, Laravel's DB::unprepared might struggle with large files or delimiters.
        // But for this size, it should be fine.
        
        try {
            DB::unprepared(File::get($sqlFile));
            $this->command->info("Database restored successfully from One SQL File!");
        } catch (\Exception $e) {
            $this->command->error("Failed to restore database: " . $e->getMessage());
        }
    }
}
