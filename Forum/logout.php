<?php
require_once '../config/database.php';

session_destroy();

header('Content-Type: application/json');
echo json_encode([
    'status' => 200,
    'message' => 'Logged out successfully'
]);

