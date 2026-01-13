<?php
// Test the actual homepage route
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Simulate a GET request to /
    $request = Illuminate\Http\Request::create('/', 'GET');
    $response = $kernel->handle($request);
    
    echo "<h1 style='color:green'>Homepage Route Works!</h1>";
    echo "<p>Status Code: " . $response->getStatusCode() . "</p>";
    echo "<hr>";
    echo $response->getContent();
    
    $kernel->terminate($request, $response);
    
} catch (\Exception $e) {
    echo "<h1 style='color:red'>Homepage Route Failed!</h1>";
    echo "<h3>Error: " . get_class($e) . "</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<h4>File: " . $e->getFile() . " (Line " . $e->getLine() . ")</h4>";
    echo "<details><summary>Stack Trace</summary><pre>" . $e->getTraceAsString() . "</pre></details>";
}
