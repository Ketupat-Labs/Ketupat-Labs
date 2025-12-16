<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\Badge;
use App\Models\BadgeCategory;
use App\Models\User;

echo "--- Fixing Badge Schema and Seeding Data ---\n";

// 1. Fix Schema
echo "\n1. Fixing Schema...\n";

// Fix badges table
if (Schema::hasTable('badges')) {
    Schema::table('badges', function (Blueprint $table) {
        if (!Schema::hasColumn('badges', 'code')) {
            echo " - Adding 'code' column to badges table\n";
            $table->string('code')->nullable()->after('id');
        }
        if (!Schema::hasColumn('badges', 'category_id')) {
            echo " - Adding 'category_id' column to badges table\n";
            $table->unsignedBigInteger('category_id')->nullable()->after('color');
        }
        if (!Schema::hasColumn('badges', 'xp_reward')) {
            echo " - Adding 'xp_reward' column to badges table\n";
            $table->integer('xp_reward')->default(0)->after('category_id');
        }

        // Make requirement fields nullable to avoid insert errors
        if (Schema::hasColumn('badges', 'requirement_type')) {
            $table->string('requirement_type')->nullable()->change();
        }
        if (Schema::hasColumn('badges', 'requirement_value')) {
            $table->integer('requirement_value')->nullable()->change();
        }
    });

    // Populate code if empty
    DB::statement("UPDATE badges SET code = CONCAT('badge_', id) WHERE code IS NULL OR code = ''");

    // Make code unique if not already
    try {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('code')->nullable(false)->unique()->change();
        });
    } catch (\Exception $e) {
        echo " - Note: Could not force unique constraint on 'code': " . $e->getMessage() . "\n";
    }
} else {
    echo " - Creating 'badges' table\n";
    Schema::create('badges', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->text('description');
        $table->string('category_slug')->nullable();
        $table->string('icon')->nullable();
        $table->string('requirement_type')->nullable();
        $table->integer('requirement_value')->nullable();
        $table->string('color')->nullable();
        $table->unsignedBigInteger('category_id')->nullable();
        $table->integer('xp_reward')->default(0);
        $table->timestamps();
    });
}

// Fix user_badges table
if (Schema::hasTable('user_badges')) {
    Schema::table('user_badges', function (Blueprint $table) {
        if (!Schema::hasColumn('user_badges', 'badge_code')) {
            echo " - Adding 'badge_code' column to user_badges table\n";
            $table->string('badge_code')->nullable()->after('user_id');
            $table->index('badge_code');
        }
    });
} else {
    echo " - Creating 'user_badges' table\n";
    Schema::create('user_badges', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
        $table->string('badge_code');
        $table->string('badge_name')->nullable();
        $table->string('badge_type')->nullable();
        $table->timestamp('earned_at')->useCurrent();
        $table->timestamps();
        $table->index('badge_code');
    });
}

// Fix badge_categories table
if (!Schema::hasTable('badge_categories')) {
    echo " - Creating 'badge_categories' table\n";
    Schema::create('badge_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('code')->unique();
        $table->text('description')->nullable();
        $table->string('icon')->nullable();
        $table->string('color')->nullable();
        $table->timestamps();
    });
} else {
    Schema::table('badge_categories', function (Blueprint $table) {
        if (!Schema::hasColumn('badge_categories', 'code')) {
            echo " - Adding 'code' column to badge_categories\n";
            $table->string('code')->nullable()->after('name');
        }
    });
    DB::statement("UPDATE badge_categories SET code = LOWER(REPLACE(name, ' ', '_')) WHERE code IS NULL OR code = ''");
}

// 2. Seed Data
echo "\n2. Seeding Data...\n";

// Create Categories
$categories = [
    [
        'name' => 'Academic',
        'code' => 'academic',
        'description' => 'Achievements related to academic excellence',
        'icon' => 'fas fa-graduation-cap',
        'color' => '#3B82F6' // Blue
    ],
    [
        'name' => 'Social',
        'code' => 'social',
        'description' => 'Achievements related to social interactions',
        'icon' => 'fas fa-users',
        'color' => '#10B981' // Green
    ],
    [
        'name' => 'Activity',
        'code' => 'activity',
        'description' => 'Achievements related to participation',
        'icon' => 'fas fa-star',
        'color' => '#F59E0B' // Amber
    ]
];

$categoryIds = [];
foreach ($categories as $catData) {
    echo " - Processing Category: {$catData['name']}\n";
    $cat = BadgeCategory::updateOrCreate(
        ['code' => $catData['code']],
        $catData
    );
    $categoryIds[$catData['code']] = $cat->id;
}

// Create Badges
$badges = [
    [
        'code' => 'first_login',
        'name' => 'Early Bird',
        'description' => 'Logged in for the first time',
        'category_code' => 'activity',
        'icon' => 'fas fa-door-open',
        'xp_reward' => 50,
        'requirement_type' => 'login',
        'requirement_value' => 1
    ],
    [
        'code' => 'social_butterfly',
        'name' => 'Social Butterfly',
        'description' => 'Added 5 friends',
        'category_code' => 'social',
        'icon' => 'fas fa-handshake',
        'xp_reward' => 100,
        'requirement_type' => 'friends_count',
        'requirement_value' => 5
    ],
    [
        'code' => 'quiz_master',
        'name' => 'Quiz Master',
        'description' => 'Scored 100% on a quiz',
        'category_code' => 'academic',
        'icon' => 'fas fa-brain',
        'xp_reward' => 200,
        'requirement_type' => 'quiz_score',
        'requirement_value' => 100
    ]
];

foreach ($badges as $badgeData) {
    echo " - Processing Badge: {$badgeData['name']}\n";
    $catCode = $badgeData['category_code'];
    unset($badgeData['category_code']);

    $badgeData['category_id'] = $categoryIds[$catCode] ?? null;
    $badgeData['category_slug'] = $catCode; // Legacy support

    // We update based on code
    Badge::updateOrCreate(
        ['code' => $badgeData['code']],
        $badgeData
    );
}

// 3. Assign Badges to First User (for testing)
echo "\n3. Assigning Badges to User...\n";
$user = User::first();
if ($user) {
    echo " - Found User: {$user->email} (ID: {$user->id})\n";

    $badgeToAssign = 'first_login';

    // Check if user has this badge
    $hasBadge = DB::table('user_badges')
        ->where('user_id', $user->id)
        ->where('badge_code', $badgeToAssign)
        ->exists();

    if (!$hasBadge) {
        echo " - Assigning '{$badgeToAssign}' to user\n";
        DB::table('user_badges')->insert([
            'user_id' => $user->id,
            'badge_code' => $badgeToAssign,
            'badge_name' => 'Early Bird', // Legacy
            'created_at' => now(),
            'updated_at' => now()
        ]);
    } else {
        echo " - User already has '{$badgeToAssign}'\n";
    }
} else {
    echo " - No users found to assign badges to.\n";
}

echo "\nDone!\n";
