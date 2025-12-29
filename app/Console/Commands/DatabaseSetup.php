<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:setup {--fresh : Drop existing database and recreate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup database automatically by importing CompuPlay.sql';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 CompuPlay Database Setup');
        $this->info('================================');
        
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');
        $dbHost = env('DB_HOST', '127.0.0.1');
        
        if (!$dbName) {
            $this->error('❌ DB_DATABASE not set in .env file');
            return 1;
        }
        
        // Check if CompuPlay.sql exists
        $sqlFile = base_path('CompuPlay.sql');
        if (!File::exists($sqlFile)) {
            $this->error('❌ CompuPlay.sql not found in project root');
            $this->info('💡 Run: php database/export_for_github.php to generate it');
            return 1;
        }
        
        try {
            // Create database if it doesn't exist
            $this->info("📦 Creating database '{$dbName}'...");
            
            // Connect without database name
            config(['database.connections.mysql.database' => null]);
            DB::purge('mysql');
            DB::reconnect('mysql');
            
            if ($this->option('fresh')) {
                DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
                $this->warn("⚠️  Dropped existing database '{$dbName}'");
            }
            
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("✅ Database '{$dbName}' created");
            
            // Reconnect to the database
            config(['database.connections.mysql.database' => $dbName]);
            DB::purge('mysql');
            DB::reconnect('mysql');
            
            // Import SQL file
            $this->info('📥 Importing CompuPlay.sql...');
            
            $command = sprintf(
                'mysql -h%s -u%s %s %s < %s 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                $dbPassword ? '-p' . escapeshellarg($dbPassword) : '',
                escapeshellarg($dbName),
                escapeshellarg($sqlFile)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                $this->error('❌ Failed to import SQL file');
                $this->error(implode("\n", $output));
                return 1;
            }
            
            $this->info('✅ Database imported successfully');
            
            // Verify tables
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);
            
            $this->info("✅ {$tableCount} tables created");
            $this->info('');
            $this->info('🎉 Database setup complete!');
            $this->info('');
            $this->info('Next steps:');
            $this->info('  1. Run: npm run build');
            $this->info('  2. Run: php artisan serve');
            $this->info('  3. Visit: http://127.0.0.1:8000');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
