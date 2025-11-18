<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'nullable|in:cikgu,pelajar',
            'remember_me' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Email dan kata laluan diperlukan'
            ], 400);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $role_input = $request->input('role', 'pelajar');
        $remember_me = $request->input('remember_me', false);

        try {
            // Map UI role to database role
            $db_role = ($role_input === 'cikgu') ? 'teacher' : 'student';

            // Check if email exists
            $user = DB::table('users')
                ->where('email', $email)
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Emel tidak dijumpai. Sila daftar terlebih dahulu.'
                ], 401);
            }

            // Check password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Kata laluan tidak betul. Sila cuba lagi.'
                ], 401);
            }

            // Check if role matches
            if ($user->role !== $db_role) {
                $correct_role_ui = ($user->role === 'teacher') ? 'Cikgu' : 'Pelajar';
                return response()->json([
                    'status' => 401,
                    'message' => 'Akaun ini didaftarkan sebagai ' . $correct_role_ui . '. Sila pilih peranan ' . $correct_role_ui . ' untuk log masuk.'
                ], 401);
            }

            // Update last_seen and is_online
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'is_online' => 1,
                    'last_seen' => now()
                ]);

            // Map database role back to UI role
            $ui_role = ($user->role === 'teacher') ? 'cikgu' : 'pelajar';

            // Set session data - ensure session is started and data is saved
            $request->session()->put([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->full_name,
                'username' => $user->username,
                'user_role' => $ui_role,
                'user_role_db' => $user->role,
                'user_logged_in' => true,
                'avatar_url' => $user->avatar_url,
            ]);
            
            // Get session ID before saving
            $sessionId = $request->hasSession() ? $request->session()->getId() : 'no-session';
            
            // Force session to save immediately
            $request->session()->save();
            
            // Verify session was set and saved to database
            $sessionInDb = \DB::table('sessions')->where('id', $sessionId)->first();
            
            // Verify session was set
            \Log::info('Login successful - Session set', [
                'user_id' => $user->id,
                'email' => $user->email,
                'session_id' => $sessionId,
                'session_user_id' => $request->session()->get('user_id'),
                'has_session' => $request->hasSession(),
                'session_data_keys' => $request->hasSession() ? array_keys($request->session()->all()) : [],
                'session_in_db' => $sessionInDb ? 'yes' : 'no',
                'session_cookie_name' => config('session.cookie'),
            ]);

            // Prepare response data
            $responseData = [
                'status' => 200,
                'message' => 'Log masuk berjaya',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->full_name,
                    'username' => $user->username,
                    'role' => $ui_role,
                    'avatar_url' => $user->avatar_url,
                    'session_id' => $sessionId, // Include session ID for debugging
                ]
            ];

            // Return JSON response
            // The session cookie should be automatically set by AddQueuedCookiesToResponse middleware
            // The StartSession middleware queues the cookie, and AddQueuedCookiesToResponse adds it to the response
            $response = response()->json($responseData);
            
            // The session middleware will handle setting the cookie automatically
            // We just need to ensure the session is saved
            // Don't set the cookie manually - let the middleware handle it
            
            \Log::info('Login response prepared', [
                'session_id' => $sessionId,
                'session_cookie_name' => config('session.cookie'),
                'session_in_db' => $sessionInDb ? 'yes' : 'no',
            ]);
            
            // Set additional cookies if remember me is checked
            if ($remember_me) {
                $response->cookie('user_logged_in', 'true', 60 * 24 * 30, '/', null, false, false)
                    ->cookie('user_email', $user->email, 60 * 24 * 30, '/', null, false, false)
                    ->cookie('user_id', (string)$user->id, 60 * 24 * 30, '/', null, false, false);
            }

            return $response;
        } catch (\Exception $e) {
            \Log::error("Login error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Return more specific error message for debugging (remove in production)
            return response()->json([
                'status' => 500,
                'message' => 'Ralat pelayan. Sila cuba lagi kemudian.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Handle user registration
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:cikgu,pelajar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Semua medan diperlukan'
            ], 400);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $role_input = $request->input('role', 'pelajar');

        if (strlen($password) < 8) {
            return response()->json([
                'status' => 400,
                'message' => 'Kata laluan mesti sekurang-kurangnya 8 aksara'
            ], 400);
        }

        try {
            // Map UI role to database role
            $db_role = ($role_input === 'cikgu') ? 'teacher' : 'student';

            // Check if email already exists
            $existingUser = DB::table('users')->where('email', $email)->first();
            if ($existingUser) {
                return response()->json([
                    'status' => 409,
                    'message' => 'Emel sudah didaftarkan. Sila gunakan emel lain atau log masuk.'
                ], 409);
            }

            // Generate username from email
            $username_base = strtolower(explode('@', $email)[0]);
            $username = $username_base;
            $counter = 1;

            // Check if username exists and generate unique one
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $username_base . $counter;
                $counter++;
            }

            // Hash password
            $hashed_password = Hash::make($password);

            // Insert new user
            $userId = DB::table('users')->insertGetId([
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'role' => $db_role,
                'full_name' => $name,
                'is_online' => 1,
                'last_seen' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Set session for auto-login after registration
            session([
                'user_id' => $userId,
                'user_email' => $email,
                'user_name' => $name,
                'username' => $username,
                'user_role' => $role_input,
                'user_role_db' => $db_role,
                'user_logged_in' => true,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Pendaftaran berjaya!',
                'data' => [
                    'user_id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'username' => $username,
                    'role' => $role_input
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("Registration error: " . $e->getMessage());
            
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status' => 409,
                    'message' => 'Emel atau nama pengguna sudah wujud. Sila cuba dengan maklumat lain.'
                ], 409);
            }

            return response()->json([
                'status' => 500,
                'message' => 'Ralat pelayan. Sila cuba lagi kemudian.'
            ], 500);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        $userId = session('user_id');
        
        if ($userId) {
            // Update user status
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'is_online' => 0,
                    'last_seen' => now()
                ]);
        }

        // Clear session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 200,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 200); // Return 200 with status 401 in JSON to avoid redirect loops
        }

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ], 200); // Return 200 with status 404 in JSON
        }

        $ui_role = ($user->role === 'teacher') ? 'cikgu' : 'pelajar';

        return response()->json([
            'status' => 200,
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->full_name,
                'username' => $user->username,
                'role' => $ui_role,
                'avatar_url' => $user->avatar_url
            ]
        ]);
    }
}

