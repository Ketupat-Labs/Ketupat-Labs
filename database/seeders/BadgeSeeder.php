<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching badge data from Ketupat-Labs repository...');
        
        try {
            // Fetch the SQL file from GitHub using file_get_contents with stream context
            $sqlUrl = 'https://raw.githubusercontent.com/wannurraudhah/Ketupat-Labs/main/compuplay%20(1).sql';
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'Laravel BadgeSeeder',
                ]
            ]);
            
            $sqlContent = @file_get_contents($sqlUrl, false, $context);
            
            if ($sqlContent === false) {
                $this->command->error('Failed to fetch SQL file from GitHub. Please check your internet connection.');
                return;
            }
            
            $this->command->info('SQL file fetched successfully. Parsing INSERT statements...');
            
            // Extract INSERT statements for badge_categories
            $this->importBadgeCategories($sqlContent);
            
            // Extract INSERT statements for badges
            $this->importBadges($sqlContent);
            
            $this->command->info('Badge data imported successfully!');
            
        } catch (\Exception $e) {
            $this->command->error('Error importing badge data: ' . $e->getMessage());
            if ($this->command->getOutput()->isVerbose()) {
                $this->command->error('Stack trace: ' . $e->getTraceAsString());
            }
        }
    }
    
    /**
     * Extract and import badge_categories INSERT statements
     */
    private function importBadgeCategories(string $sqlContent): void
    {
        $this->command->info('Importing badge categories...');
        
        // Pattern to match INSERT INTO badge_categories statements
        $pattern = '/INSERT\s+INTO\s+`?badge_categories`?\s*\([^)]+\)\s*VALUES\s*([^;]+);/is';
        
        if (preg_match_all($pattern, $sqlContent, $matches)) {
            $insertCount = 0;
            
            foreach ($matches[0] as $insertStatement) {
                try {
                    // Clean up the statement
                    $statement = trim($insertStatement);
                    
                    // Skip if table doesn't exist or statement is malformed
                    if (empty($statement) || !Str::contains($statement, 'badge_categories')) {
                        continue;
                    }
                    
                    // Replace 'code' with 'slug' if needed, or handle both
                    // Actually, let's parse and insert manually to handle column mapping
                    if (preg_match('/INSERT\s+INTO\s+`?badge_categories`?\s*\(([^)]+)\)\s*VALUES\s*(.+);/is', $statement, $parts)) {
                        $columns = array_map('trim', explode(',', preg_replace('/[`\s]/', '', $parts[1])));
                        $valuesPart = trim($parts[2]);
                        
                        // Parse values
                        $values = $this->parseSqlValues($valuesPart);
                        
                        foreach ($values as $valueRow) {
                            try {
                                $data = [];
                                foreach ($columns as $index => $col) {
                                    $data[$col] = $valueRow[$index] ?? null;
                                }
                                
                                // Map 'code' to 'slug' if code column doesn't exist yet, or use code directly
                                if (isset($data['code']) && !Schema::hasColumn('badge_categories', 'code')) {
                                    $data['slug'] = $data['code'];
                                    unset($data['code']);
                                }
                                
                                // Insert using Eloquent to handle column mapping
                                DB::table('badge_categories')->insertOrIgnore($data);
                                $insertCount++;
                            } catch (\Exception $e) {
                                if (!Str::contains($e->getMessage(), 'Duplicate entry')) {
                                    // Skip silently for duplicates
                                }
                            }
                        }
                    } else {
                        // Fallback: try direct execution
                        DB::statement($statement);
                        $insertCount++;
                    }
                    
                } catch (\Exception $e) {
                    // Skip duplicate entries or other errors
                    if (!Str::contains($e->getMessage(), 'Duplicate entry')) {
                        $this->command->warn('Skipping badge category insert: ' . $e->getMessage());
                    }
                }
            }
            
            $this->command->info("Imported {$insertCount} badge categories.");
        } else {
            $this->command->warn('No badge_categories INSERT statements found in SQL file.');
        }
    }
    
    /**
     * Parse SQL VALUES clause into array of value arrays
     */
    private function parseSqlValues(string $valuesPart): array
    {
        $values = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $escapeNext = false;
        
        for ($i = 0; $i < strlen($valuesPart); $i++) {
            $char = $valuesPart[$i];
            
            if ($escapeNext) {
                $current .= $char;
                $escapeNext = false;
                continue;
            }
            
            if ($char === '\\') {
                $escapeNext = true;
                $current .= $char;
                continue;
            }
            
            if ($char === "'" || $char === '"') {
                $inString = !$inString;
                $current .= $char;
                continue;
            }
            
            if (!$inString) {
                if ($char === '(') {
                    $depth++;
                    if ($depth === 1) {
                        $current = '';
                        continue;
                    }
                } elseif ($char === ')') {
                    $depth--;
                    if ($depth === 0) {
                        // Parse this row
                        $rowValues = $this->parseValueRow($current);
                        if (!empty($rowValues)) {
                            $values[] = $rowValues;
                        }
                        $current = '';
                        continue;
                    }
                }
            }
            
            if ($depth > 0) {
                $current .= $char;
            }
        }
        
        return $values;
    }
    
    /**
     * Parse a single row of values
     */
    private function parseValueRow(string $row): array
    {
        $values = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $escapeNext = false;
        
        for ($i = 0; $i < strlen($row); $i++) {
            $char = $row[$i];
            
            if ($escapeNext) {
                $current .= $char;
                $escapeNext = false;
                continue;
            }
            
            if ($char === '\\') {
                $escapeNext = true;
                $current .= $char;
                continue;
            }
            
            if ($char === "'" || $char === '"') {
                $inString = !$inString;
                $current .= $char;
                continue;
            }
            
            if (!$inString && $char === ',' && $depth === 0) {
                $values[] = trim($current);
                $current = '';
                continue;
            }
            
            if ($char === '(') $depth++;
            if ($char === ')') $depth--;
            
            $current .= $char;
        }
        
        if (!empty(trim($current))) {
            $values[] = trim($current);
        }
        
        return array_map(function($v) {
            $v = trim($v);
            if (($v[0] === "'" && substr($v, -1) === "'") || ($v[0] === '"' && substr($v, -1) === '"')) {
                return trim($v, "'\"");
            }
            if (strtoupper($v) === 'NULL') {
                return null;
            }
            return $v;
        }, $values);
    }
    
    /**
     * Extract and import badges INSERT statements
     */
    private function importBadges(string $sqlContent): void
    {
        $this->command->info('Importing badges...');
        
        // First, ensure categories are loaded to map category_slug to category_id
        $categoryMap = [];
        $categories = DB::table('badge_categories')->get();
        foreach ($categories as $cat) {
            $code = $cat->code ?? $cat->slug ?? null;
            if ($code) {
                $categoryMap[$code] = $cat->id;
            }
        }
        
        // Pattern to match INSERT INTO badges statements (handles multi-row inserts)
        $pattern = '/INSERT\s+INTO\s+`?badges`?\s*\([^)]+\)\s*VALUES\s*([^;]+);/is';
        
        if (preg_match_all($pattern, $sqlContent, $matches)) {
            $insertCount = 0;
            
            foreach ($matches[0] as $insertStatement) {
                try {
                    // Clean up the statement
                    $statement = trim($insertStatement);
                    
                    // Skip if table doesn't exist or statement is malformed
                    if (empty($statement) || !Str::contains($statement, '`badges`') && !Str::contains($statement, 'badges ')) {
                        continue;
                    }
                    
                    // Parse the INSERT statement
                    if (preg_match('/INSERT\s+INTO\s+`?badges`?\s*\(([^)]+)\)\s*VALUES\s*(.+);/is', $statement, $parts)) {
                        $columns = array_map('trim', explode(',', preg_replace('/[`\s]/', '', $parts[1])));
                        $valuesPart = trim($parts[2]);
                        
                        // Parse values
                        $values = $this->parseSqlValues($valuesPart);
                        
                        foreach ($values as $valueRow) {
                            try {
                                $data = [];
                                $categorySlug = null;
                                
                                foreach ($columns as $index => $col) {
                                    $value = $valueRow[$index] ?? null;
                                    
                                    if ($col === 'category_slug') {
                                        $categorySlug = $value;
                                        // Map category_slug to category_id
                                        if ($categorySlug && isset($categoryMap[$categorySlug])) {
                                            $data['category_id'] = $categoryMap[$categorySlug];
                                        }
                                    } else {
                                        $data[$col] = $value;
                                    }
                                }
                                
                                // Ensure code is not empty - generate one if needed
                                if (empty($data['code']) || $data['code'] === '') {
                                    $data['code'] = 'badge_' . ($data['id'] ?? uniqid());
                                }
                                
                                // Insert using Eloquent to handle column mapping
                                DB::table('badges')->insertOrIgnore($data);
                                $insertCount++;
                            } catch (\Exception $e) {
                                if (!Str::contains($e->getMessage(), 'Duplicate entry')) {
                                    // Skip silently for duplicates
                                }
                            }
                        }
                    } else {
                        // Fallback: try direct execution
                        DB::statement($statement);
                        $rowCount = substr_count($statement, '),(') + 1;
                        $insertCount += $rowCount;
                    }
                    
                } catch (\Exception $e) {
                    // Skip duplicate entries or other errors
                    if (!Str::contains($e->getMessage(), 'Duplicate entry')) {
                        $this->command->warn('Skipping badge insert: ' . $e->getMessage());
                    }
                }
            }
            
            $this->command->info("Imported {$insertCount} badges.");
        } else {
            $this->command->warn('No badges INSERT statements found in SQL file.');
        }
    }
}
