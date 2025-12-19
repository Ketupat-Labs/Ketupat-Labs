<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user',
            'password' => 'required|string|min:8',
            'role' => 'required|in:pelajar,cikgu',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            // Map UI role to database role
            $db_role = $request->role === 'cikgu' ? 'teacher' : 'student';
            
            // Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Hash password
            $hashedPassword = Hash::make($request->password);
            
            // Delete any existing OTP for this email
            DB::table('email_verification_otp')
                ->where('email', $request->email)
                ->delete();
            
            // Store OTP in database (expires in 10 minutes)
            DB::table('email_verification_otp')->insert([
                'email' => $request->email,
                'otp' => $otp,
                'name' => $request->name,
                'password' => $hashedPassword,
                'role' => $db_role,
                'expires_at' => now()->addMinutes(10),
                'is_verified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Send OTP email using PHPMailer with Gmail SMTP
            $emailSent = EmailService::sendOtpEmail($request->email, $otp);
            if (!$emailSent) {
                Log::error('Failed to send OTP email to: ' . $request->email);
                // Continue even if email fails (for development)
            }

            return response()->json([
                'status' => 200,
                'message' => 'Kod pengesahan telah dihantar ke emel anda. Sila semak emel anda.',
                'data' => [
                    'email' => $request->email,
                    'requires_verification' => true,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Ralat berlaku. Sila cuba lagi kemudian.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            // Find OTP record
            $otpRecord = DB::table('email_verification_otp')
                ->where('email', $request->email)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', now())
                ->where('is_verified', false)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Kod pengesahan tidak sah atau telah tamat tempoh. Sila cuba lagi.',
                ], 400);
            }

            // Check if email is already registered
            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Emel ini sudah didaftarkan.',
                ], 400);
            }

            // Generate username from email
            $username_base = strtolower(explode('@', $request->email)[0]);
            $username = $username_base;
            $counter = 1;
            
            // Ensure username is unique
            while (User::where('username', $username)->exists()) {
                $username = $username_base . $counter;
                $counter++;
            }

            // Create user
            $user = User::create([
                'username' => $username,
                'email' => $otpRecord->email,
                'password' => $otpRecord->password, // Already hashed
                'full_name' => $otpRecord->name,
                'role' => $otpRecord->role,
                'is_online' => false,
            ]);

            // Mark OTP as verified
            DB::table('email_verification_otp')
                ->where('email', $request->email)
                ->where('otp', $request->otp)
                ->update(['is_verified' => true]);

            // Map database role back to UI role
            $dbRole = $user->getAttributes()['role'] ?? $user->role;
            $ui_role = $dbRole === 'teacher' ? 'cikgu' : 'pelajar';

            return response()->json([
                'status' => 200,
                'message' => 'Pendaftaran berjaya! Akaun anda telah dibuat.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->full_name,
                    'username' => $user->username,
                    'role' => $ui_role,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('OTP verification error: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Ralat berlaku. Sila cuba lagi kemudian.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            // Find existing OTP record
            $otpRecord = DB::table('email_verification_otp')
                ->where('email', $request->email)
                ->where('is_verified', false)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Tiada permintaan pendaftaran dijumpai untuk emel ini.',
                ], 400);
            }

            // Generate new OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Update OTP record
            DB::table('email_verification_otp')
                ->where('email', $request->email)
                ->where('is_verified', false)
                ->update([
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(10),
                    'updated_at' => now(),
                ]);
            
            // Send new OTP email using PHPMailer with Gmail SMTP
            $emailSent = EmailService::sendOtpEmail($request->email, $otp);
            if (!$emailSent) {
                Log::error('Failed to resend OTP email to: ' . $request->email);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Kod pengesahan baru telah dihantar ke emel anda.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Resend OTP error: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Ralat berlaku. Sila cuba lagi kemudian.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|in:pelajar,cikgu',
            'remember_me' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Email dan kata laluan diperlukan',
            ], 400);
        }

        try {
            // Map UI role to database role
            $db_role = $request->role === 'cikgu' ? 'teacher' : 'student';
            
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Emel tidak dijumpai. Sila daftar terlebih dahulu.',
                ], 401);
            }

            // Get raw password from database (bypass hidden attribute)
            $hashedPassword = $user->getAttributes()['password'] ?? $user->getOriginal('password');
            
            if (!Hash::check($request->password, $hashedPassword)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Kata laluan tidak betul. Sila cuba lagi.',
                ], 401);
            }

            // Check if role matches
            $dbRole = $user->getAttributes()['role'] ?? $user->role;
            if ($dbRole !== $db_role) {
                $correct_role_ui = $dbRole === 'teacher' ? 'Cikgu' : 'Pelajar';
                return response()->json([
                    'status' => 401,
                    'message' => 'Akaun ini didaftarkan sebagai ' . $correct_role_ui . '. Sila pilih peranan ' . $correct_role_ui . ' untuk log masuk.',
                ], 401);
            }

            // Update last_seen and is_online
            $user->update([
                'is_online' => true,
                'last_seen' => now(),
            ]);

            // Login user (session-based for SPA)
            Auth::login($user, $request->remember_me ?? false);
            
            // Set session user_id for Ketupat-Labs controllers compatibility
            session(['user_id' => $user->id]);

            // Map database role back to UI role
            $dbRole = $user->getAttributes()['role'] ?? $user->role;
            $ui_role = $dbRole === 'teacher' ? 'cikgu' : 'pelajar';

            return response()->json([
                'status' => 200,
                'message' => 'Log masuk berjaya',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->full_name,
                    'username' => $user->username,
                    'role' => $ui_role,
                    'avatar_url' => $user->avatar_url,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Login error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Ralat pelayan. Sila cuba lagi kemudian.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function me(Request $request)
    {
        // Get user from session
        if (!Auth::check()) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        
        // Ensure session user_id is set for Ketupat-Labs controllers
        if (!session('user_id')) {
            session(['user_id' => $user->id]);
        }

        $dbRole = $user->getAttributes()['role'] ?? $user->role;
        $ui_role = $dbRole === 'teacher' ? 'cikgu' : 'pelajar';

        return response()->json([
            'status' => 200,
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->full_name,
                'username' => $user->username,
                'role' => $ui_role,
                'avatar_url' => $user->avatar_url,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update([
                'is_online' => false,
                'last_seen' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 200,
            'message' => 'Log keluar berjaya',
        ], 200);
    }
}

