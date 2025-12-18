<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "DB: " . DB::connection()->getDatabaseName() . "\n";

try {
    echo "Updating enrollments table...\n";

    Schema::table('enrollments', function (Blueprint $table) {
        if (!Schema::hasColumn('enrollments', 'completed_items')) {
            $table->json('completed_items')->nullable()->after('progress');
            echo "Added completed_items\n";
        } else {
            echo "completed_items already exists\n";
        }
    });

    echo "OK: enrollments table updated.\n";

} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
