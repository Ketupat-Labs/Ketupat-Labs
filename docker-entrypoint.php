<?php
/**
 * Entrypoint for Docker container
 * Fixes Laravel serve PORT type issue and clears config cache at runtime
 */

// Convert PORT to integer (Railway sets it as string)
$port = $_ENV['PORT'] ?? 8080;
$_ENV['PORT'] = (int)$port;

// Clear config cache at runtime
echo "Clearing config cache...\n";
passthru('php artisan config:clear');

// Start Laravel dev server
echo "Starting Laravel server on 0.0.0.0:{$_ENV['PORT']}...\n";
passthru("php artisan serve --host=0.0.0.0 --port={$_ENV['PORT']}");
