<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

echo "--- Importing Real Badges from SQL ---\n";

$sqlFile = __DIR__ . '/compuplay (1).sql';

echo "Fetching SQL from: $sqlFile\n";

if (!file_exists($sqlFile)) {
    echo "ERROR: Local SQL file not found.\n";
    exit(1);
}

$sqlContent = file_get_contents($sqlFile);

if ($sqlContent === false) {
    echo "ERROR: Failed to fetch SQL file.\n";
    exit(1);
}

echo "SQL fetched successfully (" . strlen($sqlContent) . " bytes).\n";

// Clear existing tables to avoid duplicates?
// echo "clearing existing badges...\n";
// DB::statement('SET FOREIGN_KEY_CHECKS=0;');
// DB::table('badges')->truncate();
// DB::table('badge_categories')->truncate();
// DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// 1. Import Categories
echo "Importing Categories...\n";
$pattern = '/INSERT\s+INTO\s+`?badge_categories`?\s*\(([^)]+)\)\s*VALUES\s*(.+);/is';
$idsMap = []; // old_id -> new_id (if IDs change, but we try to keep them)

if (preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $cols = array_map('trim', explode(',', str_replace('`', '', $match[1])));
        $valuesRaw = $match[2];

        // Simple parser for values like (1, 'Name', ...), (2, 'Name', ...)
        // This regex splits by `),(` handling the potential beginning `(` and end `)`
        $rows = preg_split('/\s*\)\s*,\s*\(\s*/', trim($valuesRaw, "(); \t\n\r\0\x0B"));

        foreach ($rows as $row) {
            $row = trim($row, "()");
            // Split by comma, respecting quotes is hard with simple explode, but let's try str_getcsv
            $vals = str_getcsv($row, ",", "'");

            $data = [];
            foreach ($cols as $i => $col) {
                $val = isset($vals[$i]) ? $vals[$i] : null;
                if ($val === 'NULL')
                    $val = null;
                // remove quotes if str_getcsv didn't
                $data[$col] = $val;
            }

            // Map/Fix columns
            if (!isset($data['code']) && isset($data['name'])) {
                $data['code'] = Str::slug($data['name']);
            }
            if (!isset($data['created_at']))
                $data['created_at'] = now();
            if (!isset($data['updated_at']))
                $data['updated_at'] = now();

            // Insert
            try {
                $id = DB::table('badge_categories')->insertGetId($data);
                echo " - Inserted Category: {$data['name']} (ID: $id)\n";
            } catch (\Exception $e) {
                // Try update if duplicate
                if (isset($data['code'])) {
                    $code = $data['code'];
                    unset($data['code']);
                    DB::table('badge_categories')->where('code', $code)->update($data);
                    echo " - Updated Category: {$data['name']}\n";
                } else {
                    echo " - Error inserting category: " . $e->getMessage() . "\n";
                }
            }
        }
    }
} else {
    echo "No badge_categories INSERTs found.\n";
}


// 2. Import Badges
echo "Importing Badges...\n";
// Cache categories for lookup
$categories = DB::table('badge_categories')->get();
$catSlugMap = [];
foreach ($categories as $cat) {
    if ($cat->code)
        $catSlugMap[$cat->code] = $cat->id;
    // fallback mapping if slug matches code
}

$pattern = '/INSERT\s+INTO\s+`?badges`?\s*\(([^)]+)\)\s*VALUES\s*(.+);/is';
if (preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $cols = array_map('trim', explode(',', str_replace('`', '', $match[1])));
        $valuesRaw = $match[2];

        // This naive split might fail if values contain `),(`, but let's assume standard mysqldump format
        // A better regex for the split is needed.
        // Actually, we can use the same regex approach as before but be careful.
        // Let's rely on parsed values
        $rows = preg_split('/\s*\)\s*,\s*\(\s*/', trim($valuesRaw, "(); \t\n\r\0\x0B"));

        foreach ($rows as $row) {
            $vals = str_getcsv($row, ",", "'");

            $data = [];
            foreach ($cols as $i => $col) {
                $val = isset($vals[$i]) ? $vals[$i] : null;
                if ($val === 'NULL')
                    $val = null;
                $data[$col] = $val;
            }

            // Fix Schema Mismatches

            // 1. Generate Code
            if (empty($data['code'])) {
                $data['code'] = 'badge_' . ($data['id'] ?? uniqid());
                if (isset($data['name'])) {
                    $data['code'] = Str::slug($data['name']);
                }
            }

            // 2. Map Category Slug/ID
            if (isset($data['category_slug'])) {
                $slug = $data['category_slug'];
                if (isset($catSlugMap[$slug])) {
                    $data['category_id'] = $catSlugMap[$slug];
                }
            }

            // 3. Defaults
            if (!isset($data['xp_reward']))
                $data['xp_reward'] = 50;
            if (!isset($data['created_at']))
                $data['created_at'] = now();
            if (!isset($data['updated_at']))
                $data['updated_at'] = now();

            // Insert
            try {
                DB::table('badges')->updateOrInsert(
                    ['code' => $data['code']],
                    $data
                );
                echo " - Processed Badge: {$data['name']}\n";
            } catch (\Exception $e) {
                echo " - Error processing badge: " . $e->getMessage() . "\n";
            }
        }
    }
} else {
    echo "No badges INSERTs found.\n";
}

echo "Done Importing.\n";
