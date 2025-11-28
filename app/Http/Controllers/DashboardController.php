<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role
     */
    public function index(Request $request): View|RedirectResponse
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        // Redirect based on role
        if ($user->role === 'teacher') {
            return view('dashboard.teacher', [
                'user' => $user
            ]);
        } else {
            // Eager load enrolled classrooms for student dashboard
            $user->load('enrolledClassrooms');

            return view('dashboard.student', [
                'user' => $user
            ]);
        }
    }
}
