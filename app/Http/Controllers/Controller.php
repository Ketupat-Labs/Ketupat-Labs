<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Get the current authenticated user.
     * Uses session for consistency with Ketupat-Labs controllers,
     * falls back to Auth::user() for Material controllers.
     */
    protected function getCurrentUser()
    {
        if (session('user_id')) {
            return \App\Models\User::find(session('user_id'));
        }
        return Auth::user();
    }
}

