<?php

/**
 * DEPRECATED: This file is no longer used.
 * 
 * This project now uses Laravel Reverb for WebSocket functionality.
 * 
 * To start the WebSocket server, use:
 *   php artisan reverb:start
 * 
 * Reverb is configured in config/reverb.php and uses the broadcasting
 * system with events defined in app/Events/.
 * 
 * The old Ratchet-based MessageHandler has been replaced with Laravel
 * broadcasting events:
 * - MessageSent
 * - UserTyping
 * - UserStoppedTyping
 * - OnlineStatusChanged
 * 
 * These events are broadcast on private channels defined in routes/channels.php
 */

echo "This WebSocket server file is deprecated.\n";
echo "Please use Laravel Reverb instead:\n";
echo "  php artisan reverb:start\n";
echo "\n";
echo "Make sure to configure your .env file with Reverb credentials.\n";
echo "Run: php artisan reverb:install (if not already done)\n";

