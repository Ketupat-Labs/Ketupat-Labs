<?php
/**
 * Complete Database Export for GitHub Distribution
 * Creates a comprehensive SQL file with schema and sample data
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$output = "-- =====================================================\n";
$output .= "-- CompuPlay Database - Complete Export\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- \n";
$output .= "-- This file contains the complete database schema\n";
$output .= "-- for the CompuPlay Learning Management System.\n";
$output .= "-- \n";
$output .= "-- To use: Import this file into your MySQL database\n";
$output .= "-- mysql -u root -p your_database_name < CompuPlay.sql\n";
$output .= "-- =====================================================\n\n";

$output .= "SET FOREIGN_KEY_CHECKS=0;\n";
$output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$output .= "SET time_zone = \"+00:00\";\n\n";

// Get all tables
$tables = DB::select('SHOW TABLES');
$tableKey = 'Tables_in_' . env('DB_DATABASE');

// Only exclude migrations table (Laravel tracks which migrations have run)
// All other tables are needed for the application to work properly
$excludeTables = ['migrations'];

foreach ($tables as $table) {
    $tableName = $table->$tableKey;
    
    if (in_array($tableName, $excludeTables)) {
        continue;
    }
    
    $output .= "\n-- =====================================================\n";
    $output .= "-- Table structure for table `{$tableName}`\n";
    $output .= "-- =====================================================\n\n";
    
    // Drop table if exists
    $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
    
    // Get CREATE TABLE statement
    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
    $createTableObj = (array) $createTable[0];
    
    $createKey = array_key_exists('Create Table', $createTableObj) ? 'Create Table' : 
                 (array_key_exists('Create View', $createTableObj) ? 'Create View' : null);
    
    if ($createKey) {
        $output .= $createTableObj[$createKey] . ";\n\n";
    }

    // Export Data (INSERT statements)
    // We skip extensive data tables if needed, but for now export everything
    // except specific heavy tables if users request
    
    $rows = DB::table($tableName)->get();
    
    if ($rows->count() > 0) {
        $output .= "-- Data for table `{$tableName}`\n";
        $output .= "LOCK TABLES `{$tableName}` WRITE;\n";
        $output .= "/*!40000 ALTER TABLE `{$tableName}` DISABLE KEYS */;\n";
        
        // Batch inserts for better performance/readability
        $chunks = $rows->chunk(50);
        
        foreach ($chunks as $chunk) {
            $inserts = [];
            foreach ($chunk as $row) {
                $values = [];
                foreach ((array)$row as $value) {
                    if (is_null($value)) {
                        $values[] = "NULL";
                    } else {
                        $values[] = "'" . addslashes($value) . "'";
                    }
                }
                $inserts[] = "(" . implode(", ", $values) . ")";
            }
            
            if (!empty($inserts)) {
                $output .= "INSERT INTO `{$tableName}` VALUES " . implode(",\n", $inserts) . ";\n";
            }
        }
        
        $output .= "/*!40000 ALTER TABLE `{$tableName}` ENABLE KEYS */;\n";
        $output .= "UNLOCK TABLES;\n\n";
    }
}

$output .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

// Save to root directory
file_put_contents(__DIR__ . '/../CompuPlay.sql', $output);

echo "✓ Complete database schema exported to CompuPlay.sql\n";
echo "✓ Ready for GitHub distribution\n";
echo "✓ Users can import with: mysql -u root -p database_name < CompuPlay.sql\n";
