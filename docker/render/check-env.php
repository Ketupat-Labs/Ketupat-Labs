<?php
/**
 * Environment Variable Diagnostic Script
 * Run this to see what broadcasting variables are actually loaded
 */

echo "=== Broadcasting Environment Variables ===\n";
echo "BROADCAST_CONNECTION: " . (getenv('BROADCAST_CONNECTION') ?: 'NOT SET') . "\n";
echo "\n=== Pusher Variables ===\n";
echo "PUSHER_APP_ID: " . (getenv('PUSHER_APP_ID') ?: 'NOT SET') . "\n";
echo "PUSHER_APP_KEY: " . (getenv('PUSHER_APP_KEY') ?: 'NOT SET') . "\n";
echo "PUSHER_APP_SECRET: " . (getenv('PUSHER_APP_SECRET') ?: 'NOT SET') . "\n";
echo "PUSHER_APP_CLUSTER: " . (getenv('PUSHER_APP_CLUSTER') ?: 'NOT SET') . "\n";
echo "\n=== Reverb Variables ===\n";
echo "REVERB_APP_ID: " . (getenv('REVERB_APP_ID') ?: 'NOT SET') . "\n";
echo "REVERB_APP_KEY: " . (getenv('REVERB_APP_KEY') ?: 'NOT SET') . "\n";
echo "REVERB_APP_SECRET: " . (getenv('REVERB_APP_SECRET') ?: 'NOT SET') . "\n";
