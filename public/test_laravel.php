<?php
// Quick test to see if Laravel can bootstrap
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    echo "Laravel bootstrap successful!<br>";
    echo "App Name: " . config('app.name') . "<br>";
    echo "App URL: " . config('app.url') . "<br>";
    echo "DB Connection: " . config('database.default') . "<br>";
    
} catch (\Exception $e) {
    echo "<h1 style='color:red'>Laravel Bootstrap Failed!</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
