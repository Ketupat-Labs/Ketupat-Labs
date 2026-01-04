<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Controllers\AIGeneratorController;

// Boot Laravel app
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = new AIGeneratorController();

$method = new ReflectionMethod($controller, 'generateQuizFromDocument');
$method->setAccessible(true);

$doc = "Photosynthesis is the process by which plants convert light energy into chemical energy. It occurs in chloroplasts and uses water and carbon dioxide to produce glucose and oxygen.";

try {
    $result = $method->invoke($controller, $doc, 3, 'easy', 'multiple_choice', 'Fotosintesis');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
