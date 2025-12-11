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
            $classrooms = \App\Models\Classroom::where('teacher_id', $user->id)->withCount('students')->get();
            return view('dashboard.teacher', [
                'user' => $user,
                'classrooms' => $classrooms
            ]);
        } else {
            // Eager load enrolled classrooms for student dashboard
            $user->load('enrolledClassrooms');

            // Fetch recent feedback (last graded submission)
            $recentFeedback = \App\Models\Submission::where('user_id', $user->id)
                ->where('status', 'Graded')
                ->with('lesson')
                ->latest('updated_at')
                ->first();

            // Fetch recent class assignments
            $recentAssignments = \App\Models\LessonAssignment::whereIn('classroom_id', $user->enrolledClassrooms->pluck('id'))
                ->with('lesson', 'classroom')
                ->latest('assigned_at')
                ->take(5)
                ->get();

            return view('dashboard.student', [
                'user' => $user,
                'recentFeedback' => $recentFeedback,
                'recentAssignments' => $recentAssignments
            ]);
        }
    }
}
