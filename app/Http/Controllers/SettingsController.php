<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('settings.index', [
            'user' => $user,
        ]);
    }

    /**
     * Update user settings.
     */
    public function update(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'chatbot_enabled' => 'nullable|boolean',
        ]);

        // Update settings
        if (isset($validated['chatbot_enabled'])) {
            $user->chatbot_enabled = $validated['chatbot_enabled'];
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Get current user from session
     */
    protected function getCurrentUser()
    {
        $user = null;
        if (session('user_id')) {
            $user = \App\Models\User::find(session('user_id'));
        }
        
        if (!$user) {
            $user = Auth::user();
        }
        
        return $user;
    }
}
