<?php

// Helper function for curl
function makeRequest($url, $method = 'POST', $data = [], $token = null)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $httpCode, 'body' => $result];
}

$baseUrl = 'http://127.0.0.1:8001/api/auth';

// Test Registration with OTP
$email = 'testuser_otp@example.com';
$password = 'password123';
$registerData = [
    'name' => 'OTP Test User',
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
    'role' => 'pelajar',
    'otp' => '123456' // Add OTP
];

echo "Registering $email...\n";
$response = makeRequest($baseUrl . '/register', 'POST', $registerData);

echo "Register Status: " . $response['status'] . "\n";
echo "Register Body: " . $response['body'] . "\n";

$data = json_decode($response['body'], true);
$token = $data['token'] ?? null;

if (!$token && $response['status'] == 200) {
    // Try login if no token returned
    echo "Logging in...\n";
    $loginData = [
        'email' => $email,
        'password' => $password,
        'role' => 'student'
    ];
    $response = makeRequest($baseUrl . '/login', 'POST', $loginData);
    echo "Login Status: " . $response['status'] . "\n";
    $data = json_decode($response['body'], true);
    $token = $data['token'] ?? null;
}

if ($token) {
    echo "Testing /me...\n";
    $response = makeRequest($baseUrl . '/me', 'GET', [], $token);
    echo "Me Status: " . $response['status'] . "\n";
    echo "Me Body: " . $response['body'] . "\n";
} else {
    echo "No token obtained.\n";
}
