<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\MessageHandler;

require __DIR__ . '/../vendor/autoload.php';

$port = 8080;
$host = '0.0.0.0'; // Listen on all interfaces

$handler = new MessageHandler();
$wsServer = new WsServer($handler);
$httpServer = new HttpServer($wsServer);
$server = IoServer::factory($httpServer, $port, $host);

echo "WebSocket server running on ws://{$host}:{$port}\n";
echo "Press Ctrl+C to stop the server\n";

$server->run();

