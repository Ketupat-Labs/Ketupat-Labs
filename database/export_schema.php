<?php
/**
 * Database Schema Export Script
 * Exports all table structures from the compuplay database
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$output = "-- CompuPlay Database Schema Export\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Database: compuplay\n\n";
$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

// Get all tables
$tables = DB::select('SHOW TABLES');
$tableKey = 'Tables_in_' . env('DB_DATABASE');

foreach ($tables as $table) {
    $tableName = $table->$tableKey;
    
    $output .= "-- Table: {$tableName}\n";
    
    // Get CREATE TABLE statement
    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
    $createTableObj = (array) $createTable[0];
    
    // The key might be different, let's find it
    $createKey = array_key_exists('Create Table', $createTableObj) ? 'Create Table' : 
                 (array_key_exists('Create View', $createTableObj) ? 'Create View' : null);
    
    if ($createKey) {
        $output .= $createTableObj[$createKey] . ";\n\n";
    }
}

$output .= "SET FOREIGN_KEY_CHECKS=1;\n";

// Save to file
$dir = __DIR__ . '/../database/schema';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

file_put_contents($dir . '/compuplay_complete_schema.sql', $output);

echo "✓ Schema exported successfully to database/schema/compuplay_complete_schema.sql\n";
echo "✓ Total tables exported: " . count($tables) . "\n";
