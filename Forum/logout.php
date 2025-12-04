<?php
require_once '../config/database.php';

// Destroy session
session_destroy();

// Always redirect to login page immediately
header('Location: ../login.html');
exit;

