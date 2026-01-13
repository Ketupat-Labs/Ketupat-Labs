<?php
// Test the actual homepage route
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    // 1. Resolve Kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    // 2. Force Debug Mode on the Application Instance directly
    $app['config']->set('app.debug', true);
    
    // 3. Capture Request
    $request = Illuminate\Http\Request::create('/', 'GET');
    
    // 4. Handle Request
    $response = $kernel->handle($request);
    
    echo "<h1>Response Status: " . $response->getStatusCode() . "</h1>";
    
    if ($response->getStatusCode() == 500) {
        echo "<div style='background:#fdd; padding:1em; border:1px solid red'>";
        echo "<h2>Captured 500 Error Content:</h2>";
        echo $response->getContent(); // This should contain the error stack trace
        echo "</div>";
    } else {
        echo "<h2 style='color:green'>Success! Status " . $response->getStatusCode() . "</h2>";
        // echo $response->getContent(); // Uncomment to see full page
    }
    
    $kernel->terminate($request, $response);
    
} catch (\Exception $e) {
    echo "<h1 style='color:red'>Uncaught Exception!</h1>";
    echo "<h3>" . get_class($e) . ": " . $e->getMessage() . "</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
