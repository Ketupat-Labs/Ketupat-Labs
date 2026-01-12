<?php
// Docker entrypoint: fixes string + int error

// Convert Railway PORT to integer
$port = $_ENV['PORT'] ?? 8080;
$_ENV['PORT'] = (int)$port;

// Clear Laravel config cache (runtime)
passthru('php artisan config:clear');

// Start Laravel dev server
passthru("php artisan serve --host=0.0.0.0 --port={$_ENV['PORT']}");
