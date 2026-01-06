<?php

use App\Models\Badge;
use App\Models\BadgeCategory;
use Illuminate\Support\Facades\DB;

// 1. Ensure Categories Exist with Correct Role Restrictions
echo "Ensuring categories exist...\n";

$teacherCat = BadgeCategory::updateOrCreate(
    ['code' => 'teacher-excellence'], 
    [
        'name' => 'Kecemerlangan Guru', 
        'role_restriction' => 'teacher', 
        'description' => 'Lencana khas untuk guru', 
        'icon' => 'fa-chalkboard-teacher', 
        'color' => 'blue'
    ]
);

$studentCat = BadgeCategory::updateOrCreate(
    ['code' => 'student-achiever'], 
    [
        'name' => 'Pencapaian Pelajar', 
        'role_restriction' => 'student', 
        'description' => 'Lencana khas untuk pelajar', 
        'icon' => 'fa-user-graduate', 
        'color' => 'green'
    ]
);

$generalCat = BadgeCategory::updateOrCreate(
    ['code' => 'pencapaian'], 
    [
        'name' => 'Pencapaian', 
        'role_restriction' => 'all', 
        'description' => 'Lencana pencapaian umum', 
        'icon' => 'fa-trophy', 
        'color' => 'yellow'
    ]
);

// 2. Assign Badges to Categories
echo "Assigning badges...\n";

$badges = Badge::all();
foreach ($badges as $badge) {
    $code = $badge->code;
    $name = $badge->name;
    $desc = $badge->description;
    
    $targetCatCode = 'pencapaian'; // Default to general
    
    // Heuristic for Teacher
    if (stripos($name, 'guru') !== false || stripos($desc, 'guru') !== false || stripos($code, 'teacher') !== false || stripos($name, 'Pengajar') !== false) {
        $targetCatCode = 'teacher-excellence';
    }
    // Heuristic for Student
    elseif (stripos($name, 'pelajar') !== false || stripos($desc, 'pelajar') !== false || stripos($code, 'student') !== false || stripos($name, 'Newcomer') !== false || stripos($name, 'Pendatang') !== false) {
        $targetCatCode = 'student-achiever';
    }

    // Force update
    DB::table('badge')->where('id', $badge->id)->update([
        'category_code' => $targetCatCode,
        'category_id' => BadgeCategory::where('code', $targetCatCode)->value('id') 
    ]);
    
    echo "Badge '{$name}' ({$code}) -> {$targetCatCode}\n";
}

echo "\n--- Summary ---\n";
$counts = DB::table('badge')->select('category_code', DB::raw('count(*) as total'))->groupBy('category_code')->get();
foreach($counts as $c) {
    echo "Category: {$c->category_code} -> {$c->total} badges\n";
}
