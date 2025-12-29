<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Import Hash facade

$email = 'testuser_otp@example.com';
$otp = '123456';
$password = Hash::make('password123'); // Hash the password

// Clean up old
DB::table('email_verification_otp')->where('email', $email)->delete();

// Insert new
DB::table('email_verification_otp')->insert([
    'email' => $email,
    'otp' => $otp,
    'name' => 'OTP Test User',
    'password' => $password, // Store hashed password
    'role' => 'pelajar',
    'is_verified' => false,
    'expires_at' => now()->addMinutes(10),
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "OTP created for $email with code $otp\n";
